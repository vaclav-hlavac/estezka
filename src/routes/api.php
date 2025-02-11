<?php
require_once __DIR__ . '/../autoloader.php';


use config\Database;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use controller\TaskController;
use controller\TroopController;

return function (App $app) {
    $pdo = Database::connect();

    $taskController = new TaskController($pdo);

    // Získat všechny úkoly
    $app->get('/tasks', [$taskController, 'getAllTasks']);

    // Získat úkol podle ID
    $app->get('/tasks/{id}', [$taskController, 'getTask']);

    // Vytvořit nový úkol
    $app->post('/tasks', [$taskController, 'createTask']);

    // Další routy pro update, delete



    //*****************************************************************

    $troopController = new TroopController($pdo);
    // Získat všechny oddíly
    $app->get('/troops', [$troopController, 'getAllTroops']);

    $app->get('/troops/{id}', [$troopController, 'getTroop']);


    $app->put('/troops/{id}', [$troopController, 'updateTroop']);

    $app->delete('/troops/{id}', [$troopController, 'deleteTroop']);


    $app->post('/troops', [$troopController, 'createTroop']);



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

