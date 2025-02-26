<?php

namespace App\Repository;


use App\Models\TaskProgress;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<TaskProgress>
 */
class TaskProgressRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task_progress', 'id_task_progress', TaskProgress::class);
    }
}