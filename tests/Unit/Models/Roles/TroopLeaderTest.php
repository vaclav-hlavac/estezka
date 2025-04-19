<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Roles;

use PHPUnit\Framework\TestCase;
use App\Models\Roles\TroopLeader;
use InvalidArgumentException;

final class TroopLeaderTest extends TestCase
{
    private array $fullData = [
        'id_troop_leader' => 42,
        'id_user' => 5,
        'id_troop' => 10,
        'troop_name' => '1. oddíl',
    ];

    public function testInitializationWithFullData(): void
    {
        $leader = new TroopLeader($this->fullData);

        $this->assertSame(42, $leader->getId());
        $this->assertSame(5, $leader->id_user);
        $this->assertSame(10, $leader->id_troop);
        $this->assertSame('1. oddíl', $leader->troop_name);
    }

    public function testInitializationWithMinimalData(): void
    {
        $data = [
            'id_user' => 7,
            'id_troop' => 11,
        ];

        $leader = new TroopLeader($data);

        $this->assertSame(7, $leader->id_user);
        $this->assertSame(11, $leader->id_troop);
        $this->assertNull($leader->getId());
        $this->assertNull($leader->troop_name);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TroopLeader([]);
    }

    public function testJsonSerializeStructure(): void
    {
        $leader = new TroopLeader($this->fullData);
        $json = $leader->jsonSerialize();

        $this->assertSame(42, $json['id_troop_leader']);
        $this->assertSame(5, $json['id_user']);
        $this->assertSame(10, $json['id_troop']);
        $this->assertSame('1. oddíl', $json['troop_name']);
    }

    public function testToDatabaseContainsOnlyPersistentFields(): void
    {
        $leader = new TroopLeader($this->fullData);
        $db = $leader->toDatabase();

        $this->assertSame(5, $db['id_user']);
        $this->assertSame(10, $db['id_troop']);

        $this->assertCount(2, $db);
        $this->assertArrayNotHasKey('id_troop_leader', $db);
        $this->assertArrayNotHasKey('troop_name', $db);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $leader = new TroopLeader($this->fullData);
        $this->assertSame(42, $leader->getId());
    }
}