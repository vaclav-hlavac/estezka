<?php

namespace App\Models;

use DateTime;

require_once __DIR__ . '/../../vendor/autoload.php';

class TaskProgress extends BaseModel
{
    public int $id_user;
    public int $id_task;
    public string $status;
    public ?DateTime $planned_to;
    public ?DateTime $completed_at;
    public ?int $confirmed_by;
    public ?DateTime $confirmed_at;
    public ?int $id_task_progress;

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
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'id_user' => $this->id_user,
            'id_task' => $this->id_task,
            'status' => $this->status,
        ];

        if ($this->id_task_progress != null) { $data['id_task_progress'] = $this->id_task_progress;}
        if ($this->planned_to != null) { $data['planned_to'] = $this->planned_to;}
        if ($this->completed_at != null) { $data['completed_at'] = $this->completed_at;}
        if ($this->confirmed_by != null) { $data['confirmed_by'] = $this->confirmed_by;}
        if ($this->confirmed_at != null) { $data['confirmed_at'] = $this->confirmed_at;}
        if ($this->confirmed_at != null) { $data['confirmed_at'] = $this->confirmed_at;}

        return $data;
    }

    public function getId()
    {
        return $this->id_task_progress;
    }
}