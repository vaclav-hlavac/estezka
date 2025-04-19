<?php
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Troop;
use InvalidArgumentException;

final class TroopTest extends TestCase
{
    public function testValidTroopInitialization(): void
    {
        $data = [
            'name' => 'Vlčata',
            'id_troop' => 7
        ];

        $troop = new Troop($data);

        $this->assertSame('Vlčata', $troop->name);
        $this->assertSame(7, $troop->id_troop);
    }

    public function testTroopInitializationWithoutId(): void
    {
        $data = [
            'name' => 'Vlčata'
        ];

        $troop = new Troop($data);

        $this->assertSame('Vlčata', $troop->name);
        $this->assertNull($troop->id_troop);
    }

    public function testMissingRequiredNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Troop([]);
    }

    public function testEmptyRequiredNameThrowsException(): void
    {
        $data = [
            'name' => ''
        ];

        $this->expectException(InvalidArgumentException::class);
        new Troop($data);
    }

    public function testJsonSerializationIncludesOnlyValidFields(): void
    {
        $data = [
            'name' => 'Vlčata',
            'id_troop' => 3
        ];

        $troop = new Troop($data);
        $json = $troop->jsonSerialize();

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('id_troop', $json);
        $this->assertSame('Vlčata', $json['name']);
        $this->assertSame(3, $json['id_troop']);
    }

    public function testGetIdReturnsId(): void
    {
        $data = [
            'name' => 'Vlčata',
            'id_troop' => 10
        ];

        $troop = new Troop($data);
        $this->assertSame(10, $troop->getId());
    }

    public function testToDatabaseReturnsExpectedStructure(): void
    {
        $data = [
            'name' => 'Vlčata',
            'id_troop' => 4
        ];

        $troop = new Troop($data);
        $dbData = $troop->toDatabase();

        $this->assertSame('Vlčata', $dbData['name']);
    }
}