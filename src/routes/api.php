<?php
require_once __DIR__ . '/../../vendor/autoload.php';


use App\Config\Database;
use App\Controllers\AuthController;
use App\Controllers\GangController;
use App\Controllers\GangLeaderController;
use App\Controllers\NotificationController;
use App\Controllers\TaskProgressController;
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
        $auth->post('/refresh', [$authController, 'refresh']);
    });

    $publicGangController = new GangController($pdo);

    $app->group('/patrol', function ($auth) use ($publicGangController) {
        $auth->post('/check-invite', [$publicGangController, 'checkInviteCode']);
    });


    //************************* TASK ****************************************
    $taskController = new TaskController($pdo);

    $app->group('/tasks', function ($tasks) use ($taskController) {
        $tasks->get('', [$taskController, 'getAllGeneralTasks']);

        $tasks->get('/{id}', [$taskController, 'getById']);
    });



    //************************************************************************
    //************************* NON-PUBLIC ROUTES ****************************
    //************************************************************************

    //************************* USER ****************************************
    $userController = new UserController($pdo);

    $app->group('/users', function ($users) use ($userController) {
        $users->get('', [$userController, 'getAll']);

        $users->get('/{id}', [$userController, 'getById']);
        $users->patch('/me', [$userController, 'updateSelf']);

    })->add(new AuthMiddleware());

    //************************* TROOP ****************************************
    $troopController = new TroopController($pdo);

    $app->group('/troops', function ($troops) use ($troopController) {
        $troops->get('', [$troopController, 'getAll']);
        $troops->post('', [$troopController, 'create']);


        $troops->get('/{id}', [$troopController, 'getById']);
        $troops->put('/{id}', [$troopController, 'update']);
        $troops->delete('/{id}', [$troopController, 'delete']);

        $troops->get('/{id}/members', [$troopController, 'getTroopMembers']);

        $troops->get('/{id}/patrols', [$troopController, 'getTroopGangs']);
        $troops->post('/{id}/patrols', [$troopController, 'createGang']);
    })->add(new AuthMiddleware()); //adds authorization middleware


    //************************* TASK-PROGRESSES ****************************************
    $taskProgressController = new TaskProgressController($pdo);

    $app->group('/troops/{id_troop}', function ($task_progresses) use ($taskProgressController) {
        $task_progresses->get('/task-progresses', [$taskProgressController, 'getTaskProgressesByTroop']);

        $task_progresses->get('/members/{id_user}/task-progresses', [$taskProgressController, 'getUserTaskProgresses']);
        $task_progresses->patch('/members/{id_user}/task-progresses/{id_task_progress}', [$taskProgressController, 'updateUserTaskProgress']);

    })->add(new AuthMiddleware());

    $app->group('/task-progresses', function ($task_progresses) use ($taskProgressController) {
        $task_progresses->get('/{id_task_progress}', [$taskProgressController, 'getUserTaskProgressById']);
        $task_progresses->patch('/{id_task_progress}', [$taskProgressController, 'updateUserTaskProgress']);

    })->add(new AuthMiddleware());

    //************************* NOTIFICATIONS ****************************************
    $notificationController = new NotificationController($pdo);

    $app->group('/users/{id_user}/notifications', function ($notifications) use ($notificationController) {
        $notifications->get('', [$notificationController, 'getAllForUser']);
    });

    $app->group('/notifications', function ($notifications) use ($notificationController) {
        $notifications->post('', [$notificationController, 'create']);
        $notifications->patch('/{id_notification}', [$notificationController, 'update']);
    })->add(new AuthMiddleware());


    //************************* TROOP - GANGS ****************************************
    $gangController = new GangController($pdo);
    //$accesService = new AccessService(); todo

    $app->group('/troops', function ($troops) use ($gangController) {
        $troops->post('/{id_troop}/patrols', [$gangController, 'addPatrolToTroop']);

        $troops->get('/{id_troop}/patrols/{id_patrol}/members', [$gangController, 'getGangMembers']);
    })->add(new AuthMiddleware()); //adds authorization middleware

    //************************* TASK ****************************************
    $app->group('/tasks', function ($tasks) use ($taskController) {
        $tasks->post('/troop/{id}', [$taskController, 'createTroopTask']);
        $tasks->patch('/troop/{id}', [$taskController, 'updateTroopTask']);
        $tasks->delete('/troop/{id}', [$taskController, 'deleteTroopTask']);
        $tasks->get('/troop/{id}', [$taskController, 'getTroopTasks']);
    })->add(new AuthMiddleware()); //adds authorization middleware


    //************************* PATROL ****************************************
    $gangController = new GangController($pdo);

    $app->group('/patrols', function ($gangs) use ($gangController) {
        $gangs->patch('/{id}', [$gangController, 'update']);
        $gangs->delete('/{id}', [$gangController, 'delete']);
    })->add(new AuthMiddleware())/*->add(new GangAuthorizationMiddleware())*/;

    //************************* PATROL LEADERS ****************************************
    $gangLeaderController = new GangLeaderController($pdo);

    $app->group('/patrols', function ($gangs) use ($gangLeaderController) {
        $gangs->post('/{id_patrol}/leaders', [$gangLeaderController, 'addPatrolLeader']); //todo
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

