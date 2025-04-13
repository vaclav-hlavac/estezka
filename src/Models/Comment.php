<?php

namespace App\Models;

use DateTime;

require_once __DIR__ . '/../../vendor/autoload.php';

class Comment extends BaseModel
{
    public int $user_by;
    public int $user_to;
    public DateTime $posted_at;
    public string $text;
    public ?int $id_comment;
    public ?int $id_task_progress;

    public function __construct(array $data)
    {
        $notNullArguments = ['user_by', 'user_to', 'posted_at'];
        $notEmptyArguments = ['text'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->id_comment = $data['id_comment'] ?? null;
        $this->id_task_progress = $data['id_task_progress'] ?? null;
        $this->user_by = $data['user_by'];
        $this->user_to = $data['user_to'];
        $this->posted_at = $this->convertToDateTimeIfNeeded($data['posted_at']);
        $this->text = $data['text'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_comment' => $this->id_comment,
            'id_task_progress' => $this->id_task_progress,
            'user_by' => $this->user_by,
            'user_to' => $this->user_to,
            'posted_at' => $this->posted_at?->format(\DateTimeInterface::ATOM),
            'text' => $this->text,
        ];
    }

    public function toDatabase(): array
    {
        return [
            'id_comment' => $this->id_comment,
            'id_task_progress' => $this->id_task_progress,
            'user_by' => $this->user_by,
            'user_to' => $this->user_to,
            'posted_at' => $this->formatForDatabase($this->posted_at),
            'text' => $this->text,
        ];
    }

    public function getId()
    {
        return $this->id_comment;
    }
}