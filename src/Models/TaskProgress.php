<?php

namespace App\Models;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

class TaskProgress implements JsonSerializable
{
    public $userId;
    public $taskId;
    public $status;
    public $plannedTo;
    public $completedAt;
    public $confirmedBy;
    public $confirmedAt;
    public $filledText;
    public $taskProgressId;

    public function __construct(array $data)
    {
        $this->requiredArgumentsControl();

        $this->taskProgressId = $data['id_task_progress'];
        $this->userId = $data['id_user'];
        $this->taskId = $data['id_task'];
        $this->status = $data['status'] ?? "not_started";
        $this->plannedTo = $data['planned_to'] ?? null;
        $this->completedAt = $data['completed_at'] ?? null;
        $this->confirmedBy = $data['confirmed_by'] ?? null;
        $this->confirmedAt = $data['confirmed_at'] ?? null;
        $this->filledText = $data['filled_text'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_task_progress' => $this->taskProgressId,
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

    private function requiredArgumentsControl()
    {
        if (empty($data['id_task_progress'])) {
            throw new InvalidArgumentException("Missing required field: id_task_progress");
        }
        if (empty($data['id_user'])) {
            throw new InvalidArgumentException("Missing required field: id_user");
        }
        if (empty($data['id_task'])) {
            throw new InvalidArgumentException("Missing required field: id_task");
        }
    }
}