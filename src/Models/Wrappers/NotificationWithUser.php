<?php

namespace App\Models\Wrappers;

use App\Models\Notification;
use App\Models\Roles\GangMember;
use App\Models\User;
use JsonSerializable;

class NotificationWithUser implements JsonSerializable
{
    private Notification $notification;
    private User $user;

    public function __construct(Notification $notification, User $user)
    {
        $this->notification = $notification;
        $this->user = $user;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function jsonSerialize(): array
    {
        return [
            'notification' => $this->notification,
            'creator' => $this->user,
        ];
    }
}