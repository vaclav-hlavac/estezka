<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\Task;
use App\Repository\TaskRepository;
use App\Utils\JsonResponseHelper;
use InvalidArgumentException;

class TaskController extends CRUDController{
    public function __construct($pdo) {
        parent::__construct($pdo, Task::class, TaskRepository::class );
    }


    public function getAllGeneralTasks($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAllGeneralTasks();
            return JsonResponseHelper::jsonResponse($tasks, 200, $response);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    public function getAllGeneralTasksByLevel($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAllGeneralTasksByPathLevel($args['pathLevel']);
            return JsonResponseHelper::jsonResponse($tasks, 200, $response);

        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    public function getTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return JsonResponseHelper::jsonResponse('Id troop not found', 404, $response);
        }

        return $this->getById($request, $response, $args);
    }

    public function createTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return JsonResponseHelper::jsonResponse('Id troop not found', 404, $response);
        }

        return $this->create($request, $response, $args);
    }


    public function updateTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return JsonResponseHelper::jsonResponse('Id troop not found', 404, $response);
        }

        return $this->update($request, $response, $args);
    }

    public function deleteTroopTask($request, $response, $args) {
        if($this->troopIdIncluded($request)){
            return JsonResponseHelper::jsonResponse('Id troop not found', 404, $response);
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