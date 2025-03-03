<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\Task;
use App\Repository\TaskRepository;
use InvalidArgumentException;

class TaskController extends CRUDController{
    public function __construct($pdo) {
        parent::__construct($pdo, Task::class, TaskRepository::class );
    }


    public function getAllGeneralTasks($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAllGeneralTasks();
            return $response->withJson($tasks, 200);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
    }

    public function getAllGeneralTasksByLevel($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAllGeneralTasksByPathLevel($args['pathLevel']);
            return $response->withJson($tasks, 200);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
    }

    public function getTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 404);
        }

        return $this->getById($request, $response, $args);
    }

    public function createTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 404);
        }

        return $this->create($request, $response, $args);
    }


    public function updateTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 400);
        }

        return $this->update($request, $response, $args);
    }

    public function deleteTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 404);
        }

        return $this->delete($request, $response, $args);
    }



    //********** PRIVATE ***********************************************************************
    private function troopIdIncluded($request): bool
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // control, if troopId is set
        if(!isset($data['id_troop']) || !filter_var($data['id_troop'], FILTER_VALIDATE_INT)){
            return false;
        }
        return true;
    }
}