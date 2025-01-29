<?php

use model\Task;
use model\Troop;

require_once '../src/db.php';
require_once '../src/model/Task.php';
require_once '../src/model/Troop.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uri = rtrim($uri, '/');

// Parse URI a rozhodni, kterou entitu zpracovat
$uriParts = explode('/', $uri);
$entity = $uriParts[2] ?? '';
$id = $uriParts[3] ?? null;

switch ($method) {
    case 'GET':
        // Načíst všechny nebo konkrétní záznamy
        if ($entity == 'task') {
            if ($id) {
                $task = Task::find($id, $pdo);
                echo json_encode($task->toArray());
            } else {
                $tasks = Task::all($pdo);
                echo json_encode($tasks);
            }
        } elseif ($entity == 'troop') {
            if ($id) {
                $troop = Troop::find($id, $pdo);
                echo json_encode($troop->toArray());
            } else {
                $troops = Troop::all($pdo);
                echo json_encode($troops);
            }
        }
        break;

    case 'POST': // Vytvořit nový záznam
        $data = json_decode(file_get_contents("php://input"), true);

        if ($entity == 'task') {
            $task = new Task($data['number'], $data['name'], $data['description'], $data['category']);
            $task->save($pdo);
            echo json_encode(['status' => 'created', 'id' => $task->id_task]);
        } elseif ($entity == 'troop') {
            $troop = new Troop($data['name']);
            $troop->save($pdo);
            echo json_encode(['status' => 'created', 'id' => $troop->id_troop]);
        }
        break;

    case 'PUT': // Aktualizace existujícího záznamu
        $data = json_decode(file_get_contents("php://input"), true);

        if ($entity == 'task') {
            $task = Task::find($id, $pdo);
            $task->name = $data['name'];
            $task->description = $data['description'];
            $task->category = $data['category'];
            $task->save($pdo);
            echo json_encode(['status' => 'updated']);
        } elseif ($entity == 'troop') {
            $troop = Troop::find($id, $pdo);
            $troop->name = $data['name'];
            $troop->save($pdo);
            echo json_encode(['status' => 'updated']);
        }
        break;

    case 'DELETE': // Smazání záznamu
        $data = json_decode(file_get_contents("php://input"), true);

        if ($entity == 'task') {
            $task = Task::find($id, $pdo);
            $task->delete($pdo);
            echo json_encode(['status' => 'deleted']);
        } elseif ($entity == 'troop') {
            $troop = Troop::find($id, $pdo);
            $troop->delete($pdo);
            echo json_encode(['status' => 'deleted']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
