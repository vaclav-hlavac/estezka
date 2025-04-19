<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Task;
use InvalidArgumentException;

final class TaskTest extends TestCase
{
    private array $validTaskData = [
        'number' => 1,
        'name' => 'Rozdělej oheň',
        'description' => 'Nauč se rozdělávat oheň za každého počasí.',
        'category' => 'Příroda',
        'subcategory' => 'Tábornické dovednosti',
        'tag' => 'oheň',
        'id_troop' => 3
    ];

    private array $taskDataWithId = [
        'id_task' => 42,
        'number' => 2,
        'name' => 'Zorientuj se v mapě',
        'description' => 'Práce s buzolou a mapou.',
        'category' => 'Orientace',
        'subcategory' => 'Mapy',
    ];

    public function testInitializationWithValidData(): void
    {
        $task = new Task($this->validTaskData);

        $this->assertSame(1, $task->number);
        $this->assertSame('Rozdělej oheň', $task->name);
        $this->assertSame('Příroda', $task->category);
        $this->assertSame('Tábornické dovednosti', $task->subcategory);
        $this->assertSame('oheň', $task->tag);
        $this->assertSame(3, $task->id_troop);
    }

    public function testInitializationWithId(): void
    {
        $task = new Task($this->taskDataWithId);

        $this->assertSame(2, $task->number);
        $this->assertSame(42, $task->id_task);
        $this->assertSame('Zorientuj se v mapě', $task->name);
        $this->assertNull($task->tag);
        $this->assertNull($task->id_troop);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Task([]);
    }

    public function testEmptyRequiredFieldsThrowsException(): void
    {
        $data = [
            'number' => 5,
            'name' => '',
            'description' => '',
            'category' => '',
            'subcategory' => '',
        ];

        $this->expectException(InvalidArgumentException::class);
        new Task($data);
    }

    public function testJsonSerializationStructure(): void
    {
        $task = new Task($this->validTaskData);
        $json = $task->jsonSerialize();

        $this->assertArrayHasKey('number', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertArrayHasKey('category', $json);
        $this->assertArrayHasKey('subcategory', $json);
        $this->assertArrayHasKey('tag', $json);
        $this->assertArrayHasKey('id_troop', $json);
    }

    public function testJsonSerializationWithId(): void
    {
        $task = new Task($this->taskDataWithId);
        $json = $task->jsonSerialize();

        $this->assertSame(42, $json['id_task']);
        $this->assertSame('Zorientuj se v mapě', $json['name']);

        $this->assertArrayNotHasKey('tag', $json);
        $this->assertArrayNotHasKey('id_troop', $json);
    }

    public function testToDatabaseReturnsSameAsJsonSerialize(): void
    {
        $task = new Task($this->validTaskData);
        $json = $task->toDatabase();

        $this->assertArrayHasKey('number', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('description', $json);
        $this->assertArrayHasKey('category', $json);
        $this->assertArrayHasKey('subcategory', $json);
        $this->assertArrayHasKey('tag', $json);
        $this->assertArrayHasKey('id_troop', $json);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $task = new Task($this->taskDataWithId);
        $this->assertSame(42, $task->getId());
    }
}