<?php
//require_once __DIR__ . '/../autoloader.php';
require_once __DIR__ . '/../../vendor/autoload.php';


use App\Config\Database;
use App\Controllers\AuthController;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Controllers\TaskController;
use App\Controllers\TroopController;

return function (App $app) {
    $pdo = Database::connect();

    //************************* TASK ****************************************


    $taskController = new TaskController($pdo);

    // Získat všechny úkoly
    $app->get('/tasks', [$taskController, 'getAllTasks']);

    // Získat úkol podle ID
    $app->get('/tasks/{id}', [$taskController, 'getTask']);

    // Vytvořit nový úkol
    $app->post('/tasks', [$taskController, 'createTask']);

    // Další routy pro update, delete



    //************************* TROOP ****************************************

    $troopController = new TroopController($pdo);
    $app->get('/troops', [$troopController, 'getAllTroops']);

    $app->get('/troops/{id}', [$troopController, 'getTroop']);

    $app->put('/troops/{id}', [$troopController, 'updateTroop']);

    $app->delete('/troops/{id}', [$troopController, 'deleteTroop']);

    $app->post('/troops', [$troopController, 'createTroop']);

    $app->get('/troops/{id}/gangs', [$troopController, 'getTroopGangs']);

    $app->post('/troops/{id}/gangs', [$troopController, 'createGang']);


    //************************** AUTH ***************************************

    $authController = new AuthController($pdo);

    $app->post('/auth/register', [$authController, 'register']);
    $app->post('/auth/login', [$authController, 'login']);






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

