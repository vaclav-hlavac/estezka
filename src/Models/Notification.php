<?php

namespace App\Models;

class Notification extends BaseModel
{
    public ?int $id_notification;
    public int $id_user_creator;
    public ?string $creator_name;
    public int $id_user_receiver;
    public string $text;
    public bool $was_received;
    public string $type;

    public ?int $id_task_progress;
    public ?string $created_at;
    public ?string $updated_at;


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
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        $this->creator_name = $data['creator_name'] ?? null;
    }

    public function toDatabase(): array
    {
        return [
            'id_user_creator' => $this->id_user_creator,
            'id_user_receiver' => $this->id_user_receiver,
            'text' => $this->text,
            'was_received' => $this->was_received,
            'id_task_progress' => $this->id_task_progress,
            'type' => $this->type,
        ];
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'type' => $this->type,
            'creator?name' => $this->creator_name,
        ];
    }

    public function getId()
    {
        return $this->id_notification;
    }
}