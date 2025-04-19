<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Wrappers;

use PHPUnit\Framework\TestCase;
use App\Models\Wrappers\NotificationWithPatrolMember;
use App\Models\Notification;
use App\Models\Roles\GangMember;

final class NotificationWithPatrolMemberTest extends TestCase
{
    public function testJsonSerializationReturnsCorrectStructure(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $patrolMemberMock = $this->createMock(GangMember::class);

        $notificationMock->method('jsonSerialize')->willReturn([
            'id_notification' => 100,
            'text' => 'Testovací zpráva',
        ]);

        $patrolMemberMock->method('jsonSerialize')->willReturn([
            'id_user' => 10,
            'nickname' => 'Skautík',
        ]);

        $wrapper = new NotificationWithPatrolMember($notificationMock, $patrolMemberMock);

        $expectedJson = json_encode([
            'notification' => ['id_notification' => 100, 'text' => 'Testovací zpráva'],
            'creator' => ['id_user' => 10, 'nickname' => 'Skautík'],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($wrapper));
    }

    public function testGettersReturnCorrectInstances(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $patrolMemberMock = $this->createMock(GangMember::class);

        $wrapper = new NotificationWithPatrolMember($notificationMock, $patrolMemberMock);

        $this->assertSame($notificationMock, $wrapper->getNotification());
        $this->assertSame($patrolMemberMock, $wrapper->getPatrolMember());
    }
}