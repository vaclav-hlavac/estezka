<?php

namespace App\Models;
use InvalidArgumentException;

require_once __DIR__ . '/../../vendor/autoload.php';

class TaskProgress extends BaseModel
{
    static protected $tableName = "task_progress";

    public $userId;
    public $taskId;
    public $status;
    public $plannedTo;
    public $completedAt;
    public $confirmedBy;
    public $confirmedAt;
    public $filledText;

    public function __construct($pdo, array $data)//todo dodelat tridu TaskProgress
    {
        if (isset($data['id_task_progress'])) {
            $data['id'] = $data['id_task_progress'];
        }

        parent::__construct($pdo, $data['id'] ?? null);
        $this->userId = $data['id_user'] ?? null;
        $this->taskId = $data['id_task'] ?? null;
        $this->status = $data['status'] ?? "not_started";
        $this->plannedTo = $data['planned_to'] ?? null;
        $this->completedAt = $data['completed_at'] ?? null;
        $this->confirmedBy = $data['confirmed_by'] ?? null;
        $this->confirmedAt = $data['confirmed_at'] ?? null;
        $this->filledText = $data['filled_text'] ?? null;
    }

    /**
     * Create or update a row in DB, but only **compulsory** arguments.
     * Takes compulsory arguments from the class and saves them into DB.
     * @return void
     */
    public function save()
    {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Actualization of existing taskProgress
            $stmt = $this->pdo->prepare("UPDATE $tableName SET id_user = ?, id_task = ?, status = ? WHERE id_task_progress = ?");
            $stmt->execute([$this->userId, $this->taskId, $this->status,  $this->id]);
        } else {
            // Insertion of new taskProgress
            $stmt = $this->pdo->prepare("INSERT INTO $tableName (id_user, id_task, status) VALUES (?, ?, ?)");
            $stmt->execute([$this->userId, $this->taskId, $this->status]);
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_task_progress' => $this->id,
            'id_user' => $this->userId,
            'id_task' => $this->taskId,
            'status' => $this->status,
            'planned_to' => $this->plannedTo,
            'completed_at' => $this->completedAt,
            'confirmed_by' => $this->confirmedBy,
            'confirmed_at' => $this->confirmedAt,
            'filled_text' => $this->filledText
        ];
    }

    public function savePlannedTo(): void {
        if(!isset($this->plannedTo)){
            throw new InvalidArgumentException("PlannedTo argument is not set");
        }

        $tableName = static::$tableName;
        $stmt = $this->pdo->prepare("UPDATE $tableName SET planned_to = ? WHERE id_task_progress = ?");
        $stmt->execute([$this->plannedTo, $this->id]);
    }

    public function saveCompletedAt(): void {
        if(!isset($this->completedAt)){
            throw new InvalidArgumentException("CompletedAt argument is not set");
        }

        $tableName = static::$tableName;
        $stmt = $this->pdo->prepare("UPDATE $tableName SET completed_at = ? WHERE id_task_progress = ?");
        $stmt->execute([$this->completedAt, $this->id]);
    }

    public function saveConfirmedBy(): void {
        if(!isset($this->confirmedBy)){
            throw new InvalidArgumentException("ConfirmedBy argument is not set");
        }

        $tableName = static::$tableName;
        $stmt = $this->pdo->prepare("UPDATE $tableName SET confirmed_by = ? WHERE id_task_progress = ?");
        $stmt->execute([$this->confirmedBy, $this->id]);
    }


    public function saveConfirmedAt(): void {
        if(!isset($this->confirmedAt)){
            throw new InvalidArgumentException("ConfirmedAt argument is not set");
        }

        $tableName = static::$tableName;
        $stmt = $this->pdo->prepare("UPDATE $tableName SET confirmed_at = ? WHERE id_task_progress = ?");
        $stmt->execute([$this->confirmedAt, $this->id]);
    }

    public function saveFilledText(): void {
        if(!isset($this->filledText)){
            throw new InvalidArgumentException("FilledText argument is not set");
        }

        $tableName = static::$tableName;
        $stmt = $this->pdo->prepare("UPDATE $tableName SET filled_text = ? WHERE id_task_progress = ?");
        $stmt->execute([$this->filledText, $this->id]);
    }
}