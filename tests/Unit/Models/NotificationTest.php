<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Notification;
use InvalidArgumentException;

final class NotificationTest extends TestCase
{
    private array $baseData = [
        'id_user_creator' => 1,
        'id_user_receiver' => 2,
        'text' => 'Máš nový úkol!',
        'was_received' => true,
        'type' => 'info',
        'id_task_progress' => 7,
        'created_at' => '2025-04-17T12:00:00+00:00',
        'updated_at' => '2025-04-17T13:00:00+00:00',
    ];

    public function testInitializationWithValidData(): void
    {
        $notif = new Notification($this->baseData);

        $this->assertSame(1, $notif->id_user_creator);
        $this->assertSame(2, $notif->id_user_receiver);
        $this->assertSame('Máš nový úkol!', $notif->text);
        $this->assertTrue($notif->was_received);
        $this->assertSame('info', $notif->type);
        $this->assertSame(7, $notif->id_task_progress);
        $this->assertNotNull($notif->created_at);
        $this->assertNotNull($notif->updated_at);
    }

    public function testInitializationWithDefaults(): void
    {
        $data = [
            'id_user_creator' => 1,
            'id_user_receiver' => 2,
            'text' => 'Notifikace bez typu a času',
        ];

        $notif = new Notification($data);

        $this->assertFalse($notif->was_received);
        $this->assertSame('generic', $notif->type);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Notification([]);
    }

    public function testJsonSerializeIncludesExpectedFields(): void
    {
        $notif = new Notification($this->baseData);
        $json = $notif->jsonSerialize();

        $this->assertSame('Máš nový úkol!', $json['text']);
        $this->assertSame(1, $json['id_user_creator']);
        $this->assertSame(2, $json['id_user_receiver']);
        $this->assertSame(true, $json['was_received']);
        $this->assertSame(7, $json['id_task_progress']);
        $this->assertSame('info', $json['type']);
        $this->assertSame('2025-04-17T12:00:00+00:00', $json['created_at']);
        $this->assertSame('2025-04-17T13:00:00+00:00', $json['updated_at']);
        $this->assertArrayHasKey('id_notification', $json);
    }

    public function testToDatabaseFormatsCorrectly(): void
    {
        $notif = new Notification($this->baseData);
        $db = $notif->toDatabase();

        $this->assertSame(1, $db['id_user_creator']);
        $this->assertSame(2, $db['id_user_receiver']);
        $this->assertSame('Máš nový úkol!', $db['text']);
        $this->assertSame(1, $db['was_received']); // true -> 1
        $this->assertSame(7, $db['id_task_progress']);
        $this->assertSame('info', $db['type']);
        $this->assertArrayNotHasKey('created_at', $db);
        $this->assertArrayNotHasKey('updated_at', $db);
        $this->assertArrayNotHasKey('id_notification', $db);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $notif = new Notification(array_merge($this->baseData, [
            'id_notification' => 99
        ]));

        $this->assertSame(99, $notif->getId());
    }
}