<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\Task;
use App\Repository\TaskRepository;
use InvalidArgumentException;

class TaskController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTasks($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAll();
            return $response->withJson($tasks, 200);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
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

    public function getTask($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $task = $taskRepository->findById($args['id']);
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }


        if ($task) {
            return $response->withJson($task, 200);
        } else {
            return $response->withJson(['message' => 'Task not found'], 404);
        }
    }

    public function createTask($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // required arguments check
        try {
            $task = new Task($data);
        }catch (InvalidArgumentException $e){
            return $response->withJson($e->getMessage(), $e->getCode());
        }

        // save + response
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $savedTask = $taskRepository->insert($task->jsonSerialize());
            return $response->withJson($savedTask, 201);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
    }

    public function createTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 404);
        }

        return $this->createTask($request, $response, $args);
    }

    public function updateTask($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        $taskRepository = new TaskRepository($this->pdo);
        try {
            // exist check
            $task = $taskRepository->findById($args['id']);
            if ($task == null) {
                return $response->withJson(['message' => 'Task not found'], 404);
            }

            // set new attributes
            $task->setAttributes($data);

            // update
            $updatedTask = $taskRepository->update($task->getId(), $task->jsonSerialize());

        }
        catch (DatabaseException|InvalidArgumentException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }

        // response
        if ($updatedTask) {
            return $response->withJson($updatedTask, 200);
        } else {
            return $response->withJson(['message' => 'Task not found'], 404);
        }
    }

    public function updateTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 400);
        }

        return $this->updateTask($request, $response, $args);
    }

    public function deleteTask($request, $response, $args) {
        // exist check
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $task = $taskRepository->findById($args['id']);
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        if ($task == null) {
            return $response->withJson(['message' => 'Task not found'], 404);
        }

        // delete + response
        try {
            $taskRepository->delete($task->getId());
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withStatus(204);
    }

    public function deleteTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return $response->withJson(['message' => 'Id troop not found'], 404);
        }

        return $this->deleteTask($request, $response, $args);
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