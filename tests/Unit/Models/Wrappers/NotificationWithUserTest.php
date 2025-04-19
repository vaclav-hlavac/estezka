<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Wrappers;

use PHPUnit\Framework\TestCase;
use App\Models\Wrappers\NotificationWithUser;
use App\Models\Notification;
use App\Models\User;

final class NotificationWithUserTest extends TestCase
{
    public function testJsonSerializationReturnsExpectedStructure(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $userMock = $this->createMock(User::class);

        $notificationMock->method('jsonSerialize')->willReturn([
            'id_notification' => 77,
            'text' => 'Notifikace od uživatele',
        ]);

        $userMock->method('jsonSerialize')->willReturn([
            'id_user' => 12,
            'nickname' => 'Tester',
        ]);

        $wrapper = new NotificationWithUser($notificationMock, $userMock);

        $expectedJson = json_encode([
            'notification' => ['id_notification' => 77, 'text' => 'Notifikace od uživatele'],
            'creator' => ['id_user' => 12, 'nickname' => 'Tester'],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($wrapper));
    }

    public function testGetterMethodsReturnCorrectInstances(): void
    {
        $notificationMock = $this->createMock(Notification::class);
        $userMock = $this->createMock(User::class);

        $wrapper = new NotificationWithUser($notificationMock, $userMock);

        $this->assertSame($notificationMock, $wrapper->getNotification());
        $this->assertSame($userMock, $wrapper->getUser());
    }
}