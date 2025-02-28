<?php

namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';

class TaskProgress extends BaseModel
{
    public $id_user;
    public $id_task;
    public $status;
    public $planned_to;
    public $completed_at;
    public $confirmed_by;
    public $confirmed_at;
    public $filled_text;
    public $id_task_progress;

    public function __construct(array $data)
    {
        $notNullArguments = ['id_user', 'id_task'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_task_progress = $data['id_task_progress'] ?? null;
        $this->id_user = $data['id_user'];
        $this->id_task = $data['id_task'];
        $this->status = $data['status'] ?? "not_started";
        $this->planned_to = $data['planned_to'] ?? null;
        $this->completed_at = $data['completed_at'] ?? null;
        $this->confirmed_by = $data['confirmed_by'] ?? null;
        $this->confirmed_at = $data['confirmed_at'] ?? null;
        $this->filled_text = $data['filled_text'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_task_progress' => $this->id_task_progress,
            'id_user' => $this->id_user,
            'id_task' => $this->id_task,
            'status' => $this->status,
            'planned_to' => $this->planned_to,
            'completed_at' => $this->completed_at,
            'confirmed_by' => $this->confirmed_by,
            'confirmed_at' => $this->confirmed_at,
            'filled_text' => $this->filled_text
        ];
    }

    public function getId()
    {
        return $this->id_task_progress;
    }
}