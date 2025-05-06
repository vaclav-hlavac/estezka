<?php

namespace App\Models\Wrappers;

use App\Models\User;
use App\Models\Roles\GangMember;
use App\Models\Roles\GangLeader;
use App\Models\Roles\TroopLeader;

/**
 * Wrapper object that combines a User entity with all of their assigned roles.
 *
 * Includes optional gang membership and lists of leadership roles.
 * Designed for convenient JSON serialization of user + roles in one payload.
 */
class UserWithRoles implements \JsonSerializable
{
    public User $user;
    public ?GangMember $gang_member;
    /** @var GangLeader[] */
    public array $gang_leaders;
    /** @var TroopLeader[] */
    public array $troop_leaders;

    public function __construct(User $user, ?GangMember $gangMember = null, array $gangLeaders = [], array $troopLeaders = [])
    {
        $this->user = $user;
        $this->gang_member = $gangMember;
        $this->gang_leaders = $gangLeaders;
        $this->troop_leaders = $troopLeaders;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'user' => $this->user,
            'patrol_member' => $this->gang_member,
            'patrol_leaders' => $this->gang_leaders,
            'troop_leaders' => $this->troop_leaders,
        ];
    }
}