<?php

namespace controller;
require_once __DIR__ . '/../autoloader.php';

use model\Task;

class TaskController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTasks($request, $response, $args) {
        $tasks = Task::all($this->pdo);
        $response->getBody()->write(json_encode($tasks));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getTask($request, $response, $args) {
        $task = Task::find($args['id'], $this->pdo);
        if ($task) {
            $response->getBody()->write(json_encode($task));
        } else {
            $response->getBody()->write(json_encode(['message' => 'Task not found']));
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createTask($request, $response, $args) {
        $data = $request->getParsedBody();
        $task = new Task(
            $data['number'],
            $data['name'],
            $data['description'],
            $data['category'],
            $data['tag'] ?? null
        );
        $task->save($this->pdo);
        $response->getBody()->write(json_encode($task));
        return $response->withHeader('Content-Type', 'application/json');
    }


    // Další metody pro update, delete
}