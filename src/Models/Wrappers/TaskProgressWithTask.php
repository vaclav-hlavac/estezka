<?php

namespace App\Models\Wrappers;

use App\Models\Task;
use App\Models\TaskProgress;
use JsonSerializable;

/**
 * Wrapper that combines a TaskProgress instance with its corresponding Task.
 *
 * Useful for endpoints that need to show both the user's progress and task metadata in one response.
 */
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