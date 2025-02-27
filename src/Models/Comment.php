<?php

namespace App\Models;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

class Comment implements JsonSerializable
{
    public $userBy;
    public $userTo;
    public $postedAt;
    public $text;
    public $commentId;
    public $taskProgressId;
    public function __construct(array $data)
    {
        $this->requiredArgumentsControl();

        $this->commentId = $data['id_comment'];
        $this->taskProgressId = $data['id_task_progress'] ?? null;
        $this->userBy = $data['user_by'];
        $this->userTo = $data['user_to'];
        $this->postedAt = $data['posted_at'];
        $this->text = $data['text'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_comment' => $this->commentId,
            'id_task_progress' => $this->taskProgressId,
            'user_by' => $this->userBy,
            'user_to' => $this->userTo,
            'posted_at' => $this->postedAt,
            'text' => $this->text
        ];
    }

    private function requiredArgumentsControl()
    {
        if (empty($data['id_comment'])) {
            throw new InvalidArgumentException("Missing required field: id_comment");
        }
        if (empty($data['user_by'])) {
            throw new InvalidArgumentException("Missing required field: user_by");
        }
        if (empty($data['user_to'])) {
            throw new InvalidArgumentException("Missing required field: user_to");
        }
        if (empty($data['posted_at'])) {
            throw new InvalidArgumentException("Missing required field: posted_at");
        }
        if (empty($data['text'])) {
            throw new InvalidArgumentException("Missing required field: text");
        }
    }
}