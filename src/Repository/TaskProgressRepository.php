<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\TaskProgress;
use DateTime;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<TaskProgress>
 */
class TaskProgressRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task_progress', 'id_task_progress', TaskProgress::class);
    }

    /**
     * Saves DateTime atribut to planned_to column of Task by task's ID
     * @param int $id
     * @param DateTime $plannedTo
     * @return void
     * @throws DatabaseException
     */
    public function savePlannedTo(int $id, DateTime $plannedTo): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET planned_to = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$plannedTo->format('Y-m-d'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param DateTime $completedAt
     * @return void
     * @throws DatabaseException
     */
    public function saveCompletedAt(int $id, DateTime $completedAt): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET completed_at = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$completedAt->format('Y-m-d H:i:s'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param int $confirmedBy
     * @return void
     * @throws DatabaseException
     */
    public function saveConfirmedBy(int $id, int $confirmedBy): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_by = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$confirmedBy, $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param int $confirmedAt
     * @return void
     * @throws DatabaseException
     */
    public function saveConfirmedAt(int $id, int $confirmedAt): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_at = ? WHERE {$this->primaryKey} = ?");

        try{
            $stmt->execute([$confirmedAt->format('Y-m-d H:i:s'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param string $filledText
     * @return void
     * @throws DatabaseException
     */
    public function saveFilledText(int $id, string $filledText): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET filled_text = ? WHERE {$this->primaryKey} = ?");

        try{
            $stmt->execute([$filledText, $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }
}