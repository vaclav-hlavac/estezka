<?php
require_once __DIR__ . '/../autoloader.php';


use config\Database;
use Slim\App;
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

    $app->post('/troops', [$troopController, 'createTroop']);

};

