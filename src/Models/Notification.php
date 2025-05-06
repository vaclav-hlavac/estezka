<?php

namespace App\Models;

use DateTime;
use DateTimeInterface;

/**
 * Notification model representing a user-to-user message or system alert.
 *
 * Supports linking to a specific task progress and tracks delivery status and timestamps.
 */
class Notification extends BaseModel
{
    public ?int $id_notification;
    public int $id_user_creator;
    public int $id_user_receiver;
    public string $text;
    public bool $was_received;
    public string $type;

    public ?int $id_task_progress;
    public ?DateTime $created_at;
    public ?DateTime $updated_at;

    public function __construct(array $data)
    {
        $notEmpty = ['id_user_creator', 'id_user_receiver', 'text'];
        $this->requiredArgumentsControl($data, [], $notEmpty);

        $this->id_notification = $data['id_notification'] ?? null;
        $this->id_user_creator = $data['id_user_creator'];
        $this->id_user_receiver = $data['id_user_receiver'];
        $this->text = $data['text'];
        $this->type = $data['type'] ?? 'generic';
        $this->was_received = $data['was_received'] ?? false;
        $this->id_task_progress = $data['id_task_progress'] ?? null;
        $this->created_at = $this->convertToDateTimeIfNeeded($data['created_at'] ?? null);
        $this->updated_at = $this->convertToDateTimeIfNeeded($data['updated_at'] ?? null);
    }

    public function toDatabase(): array
    {
        $data = [
            'id_user_creator' => $this->id_user_creator,
            'id_user_receiver' => $this->id_user_receiver,
            'text' => $this->text,
            'was_received' => $this->formatForDatabase($this->was_received),
            'id_task_progress' => $this->id_task_progress,
            'type' => $this->type,
        ];
        return $data;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_notification' => $this->id_notification,
            'id_user_creator' => $this->id_user_creator,
            'id_user_receiver' => $this->id_user_receiver,
            'text' => $this->text,
            'was_received' => $this->was_received,
            'id_task_progress' => $this->id_task_progress,
            'created_at' => $this->created_at?->format(DateTimeInterface::ATOM),
            'updated_at' => $this->updated_at?->format(DateTimeInterface::ATOM),
            'type' => $this->type,
        ];
    }

    public function getId()
    {
        return $this->id_notification;
    }

}