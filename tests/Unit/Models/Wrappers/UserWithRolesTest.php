<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Wrappers;

use PHPUnit\Framework\TestCase;
use App\Models\Wrappers\UserWithRoles;
use App\Models\User;
use App\Models\Roles\GangMember;
use App\Models\Roles\GangLeader;
use App\Models\Roles\TroopLeader;

final class UserWithRolesTest extends TestCase
{
    public function testJsonSerializationWithAllRoles(): void
    {
        $userMock = $this->createMock(User::class);
        $gangMemberMock = $this->createMock(GangMember::class);
        $gangLeaderMock1 = $this->createMock(GangLeader::class);
        $gangLeaderMock2 = $this->createMock(GangLeader::class);
        $troopLeaderMock = $this->createMock(TroopLeader::class);

        $userMock->method('jsonSerialize')->willReturn(['id_user' => 1, 'nickname' => 'Alfa']);
        $gangMemberMock->method('jsonSerialize')->willReturn(['id_patrol' => 3]);
        $gangLeaderMock1->method('jsonSerialize')->willReturn(['id_patrol_leader' => 10]);
        $gangLeaderMock2->method('jsonSerialize')->willReturn(['id_patrol_leader' => 11]);
        $troopLeaderMock->method('jsonSerialize')->willReturn(['id_troop_leader' => 20]);

        $wrapper = new UserWithRoles(
            $userMock,
            $gangMemberMock,
            [$gangLeaderMock1, $gangLeaderMock2],
            [$troopLeaderMock]
        );

        $expectedJson = json_encode([
            'user' => ['id_user' => 1, 'nickname' => 'Alfa'],
            'patrol_member' => ['id_patrol' => 3],
            'patrol_leaders' => [
                ['id_patrol_leader' => 10],
                ['id_patrol_leader' => 11],
            ],
            'troop_leaders' => [
                ['id_troop_leader' => 20],
            ],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($wrapper));
    }

    public function testJsonSerializationWithOnlyUser(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('jsonSerialize')->willReturn(['id_user' => 2, 'nickname' => 'Beta']);

        $wrapper = new UserWithRoles($userMock);

        $expectedJson = json_encode([
            'user' => ['id_user' => 2, 'nickname' => 'Beta'],
            'patrol_member' => null,
            'patrol_leaders' => [],
            'troop_leaders' => [],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($wrapper));
    }

    public function testWrapperStoresReferencesCorrectly(): void
    {
        $userMock = $this->createMock(User::class);
        $gangMemberMock = $this->createMock(GangMember::class);
        $gangLeaders = [$this->createMock(GangLeader::class)];
        $troopLeaders = [$this->createMock(TroopLeader::class)];

        $wrapper = new UserWithRoles($userMock, $gangMemberMock, $gangLeaders, $troopLeaders);

        $this->assertSame($userMock, $wrapper->user);
        $this->assertSame($gangMemberMock, $wrapper->gang_member);
        $this->assertSame($gangLeaders, $wrapper->gang_leaders);
        $this->assertSame($troopLeaders, $wrapper->troop_leaders);
    }
}