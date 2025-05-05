<?php

namespace App\Models;

use DateTime;
use DateTimeInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

class TaskProgress extends BaseModel
{
    public int $id_user;
    public int $id_task;
    public string $status;
    public ?DateTime $planned_to;
    public ?DateTime $signed_at;
    public ?string $witness;
    public ?int $id_confirmed_by;
    public ?String $filled_text;
    public ?String $confirmed_by_nickname;
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

        $this->planned_to = $this->convertToDateTimeIfNeeded($data['planned_to'] ?? null);
        $this->signed_at = $this->convertToDateTimeIfNeeded($data['signed_at'] ?? null);
        $this->witness = $data['witness'] ?? null;
        $this->filled_text = $data['filled_text'] ?? null;
        $this->id_confirmed_by = $data['id_confirmed_by'] ?? null;
        $this->confirmed_by_nickname = $data['confirmed_by_nickname'] ?? null;
        $this->confirmed_at = $this->convertToDateTimeIfNeeded($data['confirmed_at'] ?? null);
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'id_user' => $this->id_user,
            'id_task' => $this->id_task,
            'status' => $this->status,
        ];

        if ($this->id_task_progress !== null) { $data['id_task_progress'] = $this->id_task_progress; }
        if ($this->planned_to !== null) { $data['planned_to'] = $this->planned_to->format(DateTimeInterface::ATOM); }
        if ($this->signed_at !== null) { $data['signed_at'] = $this->signed_at->format(DateTimeInterface::ATOM); }
        if ($this->witness !== null) { $data['witness'] = $this->witness; }
        if ($this->id_confirmed_by !== null) { $data['id_confirmed_by'] = $this->id_confirmed_by; }
        if ($this->confirmed_at !== null) { $data['confirmed_at'] = $this->confirmed_at->format(DateTimeInterface::ATOM); }
        if ($this->confirmed_by_nickname !== null) { $data['confirmed_by_nickname'] = $this->confirmed_by_nickname; }
        if ($this->filled_text !== null) { $data['filled_text'] = $this->filled_text; }

        return $data;
    }

    public function getId()
    {
        return $this->id_task_progress;
    }

    public function toDatabase()
    {
        $data = [
            'id_user' => $this->id_user,
            'id_task' => $this->id_task,
            'status' => $this->status,
        ];

        if ($this->id_task_progress !== null) { $data['id_task_progress'] = $this->id_task_progress; }
        if ($this->planned_to !== null) { $data['planned_to'] = $this->formatForDatabase($this->planned_to); }
        if ($this->signed_at !== null) { $data['signed_at'] = $this->formatForDatabase($this->signed_at); }
        if ($this->witness !== null) { $data['witness'] = $this->witness; }
        if ($this->id_confirmed_by !== null) { $data['id_confirmed_by'] = $this->id_confirmed_by; }
        if ($this->confirmed_at !== null) { $data['confirmed_at'] = $this->formatForDatabase($this->confirmed_at); }
        if ($this->filled_text !== null) { $data['filled_text'] = $this->filled_text; }

        return $data;
    }
}