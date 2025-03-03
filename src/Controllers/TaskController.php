<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';
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
        $tasks = $taskRepository->findAll();

        $response->getBody()->write(json_encode($tasks));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function getAllGeneralTasks($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        $tasks = $taskRepository->findAllGeneralTasks();

        $response->getBody()->write(json_encode($tasks));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function getAllGeneralTasksByLevel($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        $tasks = $taskRepository->findAllGeneralTasksByPathLevel($args['pathLevel']);

        if (!empty($tasks)) {
            $response->getBody()->write(json_encode($tasks));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['message' => 'Tasks of this level not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function getTask($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($args['id']);

        if ($task) {
            $response->getBody()->write(json_encode($task));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['message' => 'Task not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function createTask($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // required arguments check
        try {
            $task = new Task($data);
        }catch (InvalidArgumentException $e){
            $response->getBody()->write(json_encode(['message' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // save
        $taskRepository = new TaskRepository($this->pdo);
        $savedTask = $taskRepository->insert($task->jsonSerialize());

        // response
        $response->getBody()->write(json_encode($savedTask));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            $response->getBody()->write(json_encode(['message' => 'Id troop not found']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        return $this->createTask($request, $response, $args);
    }

    public function updateTask($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // exist check
        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($args['id']);
        if ($task == null) {
            $response->getBody()->write(json_encode(['message' => 'Task not found']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // set new attributes
        try {
            $task->setAttributes($data);
        }catch (InvalidArgumentException $e){
            $response->getBody()->write(json_encode(['message' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // update
        $updatedTask = $taskRepository->update($task->getId(), $task->jsonSerialize());

        // response
        if ($updatedTask) {
            $response->getBody()->write(json_encode($task));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['message' => 'Task not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function updateTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            $response->getBody()->write(json_encode(['message' => 'Id troop not found']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        return $this->updateTask($request, $response, $args);
    }

    public function deleteTask($request, $response, $args) {
        // exist check
        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($args['id']);
        if ($task == null) {
            $response->getBody()->write(json_encode(['message' => 'Task not found']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // delete + response
        if ($taskRepository->delete($task->getId())) {
            return $response->withStatus(204);
        } else {
            $response->getBody()->write(json_encode(['message' => 'Database error.']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function deleteTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            $response->getBody()->write(json_encode(['message' => 'Id troop not found']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
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