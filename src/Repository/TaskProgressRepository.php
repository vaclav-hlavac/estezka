<?php

namespace App\Repository;

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

    public function savePlannedTo(int $id, DateTime $plannedTo): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET planned_to = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$plannedTo->format('Y-m-d'), $id]);
    }

    public function saveCompletedAt(int $id, DateTime $completedAt): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET completed_at = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$completedAt->format('Y-m-d H:i:s'), $id]);
    }

    public function saveConfirmedBy(int $id, int $confirmedBy): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_by = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$confirmedBy, $id]);
    }

    public function saveConfirmedAt(int $id, int $confirmedAt): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_at = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$confirmedAt, $id]);
    }

    public function saveFilledText(int $id, string $filledText): bool {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET filled_text = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$filledText, $id]);
    }
}