<?php

namespace App\Repository;


use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Task;

/**
 * @extends GenericRepository<Task>
 */
class TaskRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task', 'id_task', Task::class);
    }
}