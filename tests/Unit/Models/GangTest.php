<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Gang;
use InvalidArgumentException;
use Random\RandomException;

final class GangTest extends TestCase
{
    private array $baseData = [
        'name' => 'Vlci',
        'id_troop' => 1,
        'id_patrol' => 3,
        'color' => 'green',
    ];

    public function testInitializationWithValidData(): void
    {
        $gang = new Gang($this->baseData);

        $this->assertSame('Vlci', $gang->name);
        $this->assertSame(1, $gang->id_troop);
        $this->assertSame(3, $gang->id_patrol);
        $this->assertSame('green', $gang->color);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{10}$/', $gang->invite_code);
    }

    public function testInitializationWithDefaults(): void
    {
        $data = [
            'name' => 'Rysi',
            'id_troop' => 2,
        ];

        $gang = new Gang($data);

        $this->assertSame('Rysi', $gang->name);
        $this->assertSame(2, $gang->id_troop);
        $this->assertNull($gang->id_patrol);
        $this->assertNull($gang->color);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{10}$/', $gang->invite_code);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Gang([]);
    }

    public function testJsonSerializeExpectedFields(): void
    {
        $gang = new Gang($this->baseData);
        $json = $gang->jsonSerialize();

        $this->assertSame('Vlci', $json['name']);
        $this->assertSame(1, $json['id_troop']);
        $this->assertSame(3, $json['id_patrol']);
        $this->assertSame('green', $json['color']);
        $this->assertArrayHasKey('invite_code', $json);
    }

    public function testToDatabaseExpectedStructure(): void
    {
        $gang = new Gang($this->baseData);
        $db = $gang->toDatabase();

        $this->assertIsArray($db);
        $this->assertSame('Vlci', $db['name']);
        $this->assertSame(1, $db['id_troop']);
        $this->assertSame(3, $db['id_patrol']);
        $this->assertSame('green', $db['color']);
        $this->assertArrayHasKey('invite_code', $db);
    }

    public function testInviteCodeIsRefreshed(): void
    {
        $gang = new Gang($this->baseData);
        $oldCode = $gang->invite_code;

        $gang->refreshInviteCode();

        $this->assertNotSame($oldCode, $gang->invite_code);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $gang->invite_code);
    }

    public function testGetIdReturnsIdPatrol(): void
    {
        $gang = new Gang($this->baseData);
        $this->assertSame(3, $gang->getId());
    }
}