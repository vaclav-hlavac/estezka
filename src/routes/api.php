<?php
require_once __DIR__ . '/../../vendor/autoload.php';


use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\GangController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GangAuthorizationMiddleware;
use App\Models\Troop;
use App\Services\AccessService;
use App\Services\AuthService;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Controllers\TaskController;
use App\Controllers\TroopController;

return function (App $app) {
    $pdo = Database::connect();
    $authService = new AuthService();

    //************************************************************************
    //************************* PUBLIC ROUTES ********************************
    //************************************************************************

    //************************** AUTH ***************************************
    $authController = new AuthController($pdo, $authService);

    $app->group('/auth', function ($auth) use ($authController) {
        $auth->post('/register', [$authController, 'register']);
        $auth->post('/login', [$authController, 'login']);
    });


    //************************* TASK ****************************************
    $taskController = new TaskController($pdo);

    $app->group('/tasks', function ($tasks) use ($taskController) {
        $tasks->get('', [$taskController, 'getAllGeneralTasks']);

        $tasks->get('/{id}', [$taskController, 'getById']);
    });

    //************************* USER ****************************************
    //todo only for testing
    $userController = new UserController($pdo);

    $app->group('/users', function ($tasks) use ($userController) {
        $tasks->get('', [$userController, 'getAll']);

        $tasks->get('/{id}', [$userController, 'getById']);
    });

    //************************************************************************
    //************************* NON-PUBLIC ROUTES ****************************
    //************************************************************************


    //************************* TROOP ****************************************
    $troopController = new TroopController($pdo);

    $app->group('/troops', function ($troops) use ($troopController) {
        $troops->get('', [$troopController, 'getAll']);
        $troops->post('', [$troopController, 'create']);

        $troops->get('/{id}', [$troopController, 'getById']);
        $troops->put('/{id}', [$troopController, 'update']);
        $troops->delete('/{id}', [$troopController, 'delete']);

        $troops->get('/{id}/gangs', [$troopController, 'getTroopGangs']);
        $troops->post('/{id}/gangs', [$troopController, 'createGang']);
    })->add(new AuthMiddleware()); //adds authorization middleware


    //************************* TROOP - GANGS ****************************************
    $gangController = new GangController($pdo);
    //$accesService = new AccessService(); todo

    $app->group('/troops', function ($troops) use ($gangController) {
        $troops->get('/{id_troop}/gangs/{id_gang}/members', [$gangController, 'getGangMembers']);
        $troops->post('/{id_troop}/gangs/{id_gang}/members', [$gangController, 'addGangMember']);

    })->add(new AuthMiddleware()); //adds authorization middleware

    //************************* TASK ****************************************
    $app->group('/tasks', function ($tasks) use ($taskController) {
        $tasks->post('/troop/{id}', [$taskController, 'createTroopTask']);
        $tasks->patch('/troop/{id}', [$taskController, 'updateTroopTask']);
        $tasks->delete('/troop/{id}', [$taskController, 'deleteTroopTask']);
        $tasks->get('/troop/{id}', [$taskController, 'getTroopTasks']);
    })->add(new AuthMiddleware()); //adds authorization middleware


    //************************* GANG ****************************************
    $gangController = new GangController($pdo);

    $app->group('/gangs', function ($gangs) use ($gangController) {
        $gangs->patch('/{id}', [$gangController, 'update']);
        $gangs->delete('/{id}', [$gangController, 'delete']);
        $gangs->delete('/{id_gang}/members/{id_user}', [$gangController, 'getGangMembers']);

    })->add(new AuthMiddleware())/*->add(new GangAuthorizationMiddleware())*/;




    //************************* NON-EXISTING **********************************

    // Middleware for nonexisting pages - return 404
    $app->add(function (Request $request, RequestHandler $handler): Response {
        try {
            return $handler->handle($request);
        } catch (HttpNotFoundException $e) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode([
                'error' => 'Not Found',
                'message' => 'The requested resource was not found'
            ]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    });
};

