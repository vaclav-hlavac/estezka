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

    public function findAllGeneralTasks(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table} WHERE id_troop IS NULL");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findAllGeneralTasksByPathLevel(int $pathLevel): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_troop IS NULL AND path_level = :level");
        $stmt->execute(['level' => $pathLevel]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }
}