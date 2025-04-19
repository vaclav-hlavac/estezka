<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Roles;

use PHPUnit\Framework\TestCase;
use App\Models\Roles\GangMember;
use InvalidArgumentException;

final class GangMemberTest extends TestCase
{
    private array $fullData = [
        'id_user' => 5,
        'id_patrol' => 8,
        'nickname' => 'Skautík',
        'avatar_url' => 'https://example.com/avatar.png',
        'patrol_name' => 'Rysi',
        'patrol_color' => 'green',
        'id_troop' => 2,
        'troop_name' => '1. oddíl',
        'completed_tasks' => 15,
        'total_tasks' => 30,
        'active_path_level' => 3,
    ];

    public function testInitializationWithFullData(): void
    {
        $member = new GangMember($this->fullData);

        $this->assertSame(5, $member->id_user);
        $this->assertSame(8, $member->id_patrol);
        $this->assertSame('Skautík', $member->nickname);
        $this->assertSame('https://example.com/avatar.png', $member->avatar_url);
        $this->assertSame('Rysi', $member->gang_name);
        $this->assertSame('green', $member->gang_color);
        $this->assertSame(2, $member->id_troop);
        $this->assertSame('1. oddíl', $member->troop_name);
        $this->assertSame(15, $member->completed_tasks);
        $this->assertSame(30, $member->total_tasks);
        $this->assertSame(3, $member->active_path_level);
    }

    public function testInitializationWithMinimalData(): void
    {
        $data = [
            'id_user' => 1,
            'id_patrol' => 2
        ];

        $member = new GangMember($data);

        $this->assertSame(1, $member->id_user);
        $this->assertSame(2, $member->id_patrol);
        $this->assertNull($member->nickname);
        $this->assertNull($member->avatar_url);
        $this->assertNull($member->gang_name);
        $this->assertNull($member->gang_color);
        $this->assertNull($member->id_troop);
        $this->assertNull($member->troop_name);
        $this->assertNull($member->completed_tasks);
        $this->assertNull($member->total_tasks);
        $this->assertNull($member->active_path_level);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GangMember([]);
    }

    public function testJsonSerializeReturnsExpectedFields(): void
    {
        $member = new GangMember($this->fullData);
        $json = $member->jsonSerialize();

        $this->assertSame(5, $json['id_user']);
        $this->assertSame(8, $json['id_patrol']);
        $this->assertSame('Skautík', $json['nickname']);
        $this->assertSame('https://example.com/avatar.png', $json['avatar_url']);
        $this->assertSame('Rysi', $json['patrol_name']);
        $this->assertSame('green', $json['patrol_color']);
        $this->assertSame(2, $json['id_troop']);
        $this->assertSame('1. oddíl', $json['troop_name']);
        $this->assertSame(15, $json['completed_tasks']);
        $this->assertSame(30, $json['total_tasks']);
        $this->assertSame(3, $json['active_path_level']);
    }

    public function testToDatabaseOnlyContainsPersistentFields(): void
    {
        $member = new GangMember($this->fullData);
        $db = $member->toDatabase();

        $this->assertSame(5, $db['id_user']);
        $this->assertSame(8, $db['id_patrol']);
        $this->assertCount(2, $db);
        $this->assertArrayNotHasKey('nickname', $db);
        $this->assertArrayNotHasKey('completed_tasks', $db);
    }

    public function testGetIdReturnsIdUser(): void
    {
        $member = new GangMember($this->fullData);
        $this->assertSame(5, $member->getId());
    }
}