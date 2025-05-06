<?php

namespace App\Repository;


use App\Exceptions\DatabaseException;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Models\Task;
use PDOException;

/**
 * Repository class for accessing tasks from the `task` table.
 *
 * Focuses on retrieving general tasks (i.e., not assigned to a specific troop) and filtering by path level.
 *
 * @extends GenericRepository<Task>
 */
class TaskRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task', 'id_task', Task::class);
    }

    /**
     * Retrieve all general tasks that are not assigned to any troop.
     *
     * A general task is identified by having `id_troop IS NULL`.
     *
     * @return Task[] Array of Task objects that are general (global).
     * @throws DatabaseException If a database error occurs during the query.
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
     * Retrieve all general tasks filtered by a specific path level.
     *
     * General tasks are those not assigned to any troop (`id_troop IS NULL`)
     * and that match the given `path_level`.
     *
     * @param int $pathLevel The path level to filter general tasks by.
     * @return Task[] Array of general Task objects matching the given path level.
     * @throws DatabaseException If a database error occurs during the query.
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