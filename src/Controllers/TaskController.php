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
        parent::__construct($pdo, Task::class, new TaskRepository($pdo) );
    }


    /**
     * Retrieves all general tasks (not assigned to any troop).
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Get(
     *     path="/tasks",
     *     summary="Get all general tasks",
     *     tags={"Tasks"},
     *     @OA\Response(response=200, description="List of general tasks"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function getAllGeneralTasks($request, $response, $args) {
        $taskRepository = new TaskRepository($this->pdo);
        try {
            $tasks = $taskRepository->findAllGeneralTasks();
            return JsonResponseHelper::jsonResponse($tasks, 200, $response);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    /**
     * Retrieves all general tasks filtered by path level.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args Contains 'pathLevel'
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Get(
     *     path="/tasks/level/{pathLevel}",
     *     summary="Get general tasks by path level",
     *     tags={"Tasks"},
     *     @OA\Parameter(
     *         name="pathLevel",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Filtered list of general tasks"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
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