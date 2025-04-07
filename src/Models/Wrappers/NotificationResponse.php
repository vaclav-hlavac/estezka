<?php

namespace App\Models\Wrappers;

use App\Models\Notification;
use App\Models\TaskProgress;
use JsonSerializable;

class NotificationResponse implements JsonSerializable {
    public Notification $notification;
    public ?TaskProgress $taskProgress;

    public function __construct(Notification $notification, ?TaskProgress $taskProgress = null) {
        $this->notification = $notification;
        $this->taskProgress = $taskProgress;
    }

    public function jsonSerialize(): mixed {
        $data = [
            'notification' => $this->notification,
        ];

        if ($this->taskProgress !== null) {
            $data['task_progress'] = $this->taskProgress;
        }

        return $data;
    }
}