<?php

namespace App\Models\Wrappers;

use App\Models\Task;
use App\Models\TaskProgress;
use JsonSerializable;

class TaskProgressWithTask implements JsonSerializable
{
    public TaskProgress $progress;
    public Task $task;

    public function __construct(TaskProgress $progress, Task $task)
    {
        $this->progress = $progress;
        $this->task = $task;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'progress' => $this->progress,
            'task' => $this->task
        ];
    }
}