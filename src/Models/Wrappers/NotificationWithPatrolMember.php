<?php

namespace App\Models\Wrappers;

use App\Models\Notification;
use App\Models\Roles\GangMember;
use JsonSerializable;

/**
 * Wrapper combining a Notification with creator details enriched as a GangMember (patrol member).
 *
 * Useful for displaying notifications alongside patrol context (nickname, avatar, patrol info).
 */
class NotificationWithPatrolMember implements JsonSerializable
{
    private Notification $notification;
    private GangMember $patrolMember;

    public function __construct(Notification $notification, GangMember $patrolMember)
    {
        $this->notification = $notification;
        $this->patrolMember = $patrolMember;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function getPatrolMember(): GangMember
    {
        return $this->patrolMember;
    }

    public function jsonSerialize(): array
    {
        return [
            'notification' => $this->notification,
            'creator' => $this->patrolMember,
        ];
    }
}