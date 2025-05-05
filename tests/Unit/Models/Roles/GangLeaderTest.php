<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Roles;

use PHPUnit\Framework\TestCase;
use App\Models\Roles\GangLeader;
use InvalidArgumentException;

final class GangLeaderTest extends TestCase
{
    private array $fullData = [
        'id_patrol_leader' => 5,
        'id_user' => 10,
        'id_patrol' => 20,
        'patrol_name' => 'Vlci',
        'patrol_color' => 'green',
        'id_troop' => 3,
        'troop_name' => '1. oddíl',
    ];

    public function testInitializationWithValidData(): void
    {
        $leader = new GangLeader($this->fullData);

        $this->assertSame(5, $leader->getId());
        $this->assertSame(10, $leader->id_user);
        $this->assertSame(20, $leader->id_patrol);
        $this->assertSame('Vlci', $leader->gang_name);
        $this->assertSame('green', $leader->gang_color);
        $this->assertSame(3, $leader->id_troop);
        $this->assertSame('1. oddíl', $leader->troop_name);
    }

    public function testInitializationWithMinimalData(): void
    {
        $data = [
            'id_user' => 1,
            'id_patrol' => 2,
        ];

        $leader = new GangLeader($data);

        $this->assertSame(1, $leader->id_user);
        $this->assertSame(2, $leader->id_patrol);
        $this->assertNull($leader->getId());
        $this->assertNull($leader->gang_name);
        $this->assertNull($leader->gang_color);
        $this->assertNull($leader->id_troop);
        $this->assertNull($leader->troop_name);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new GangLeader([]);
    }

    public function testJsonSerializeContainsExpectedFields(): void
    {
        $leader = new GangLeader($this->fullData);
        $json = $leader->jsonSerialize();

        $this->assertSame(5, $json['id_patrol_leader']);
        $this->assertSame(10, $json['id_user']);
        $this->assertSame(20, $json['id_patrol']);
        $this->assertSame('Vlci', $json['patrol_name']);
        $this->assertSame('green', $json['patrol_color']);
        $this->assertSame(3, $json['id_troop']);
        $this->assertSame('1. oddíl', $json['troop_name']);
    }

    public function testToDatabaseReturnsOnlyPersistentFields(): void
    {
        $leader = new GangLeader($this->fullData);
        $db = $leader->toDatabase();

        $this->assertSame(10, $db['id_user']);
        $this->assertSame(20, $db['id_patrol']);

        $this->assertCount(2, $db);
        $this->assertArrayNotHasKey('patrol_name', $db);
        $this->assertArrayNotHasKey('troop_name', $db);
    }
}