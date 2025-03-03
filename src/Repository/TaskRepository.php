<?php

namespace App\Repository;


use App\Exceptions\DatabaseException;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Task;
use PDOException;

/**
 * @extends GenericRepository<Task>
 */
class TaskRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task', 'id_task', Task::class);
    }

    /**
     * @return array
     * @throws DatabaseException
     */
    public function findAllGeneralTasks(): array
    {
        try{
            $stmt = $this->pdo->query("SELECT * FROM {$this->table} WHERE id_troop IS NULL");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * @param int $pathLevel
     * @return array
     * @throws DatabaseException
     */
    public function findAllGeneralTasksByPathLevel(int $pathLevel): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_troop IS NULL AND path_level = :level");

        try{
            $stmt->execute(['level' => $pathLevel]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }
}