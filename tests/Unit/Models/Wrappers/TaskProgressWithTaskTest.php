<?php

declare(strict_types=1);
namespace Tests\Unit\Models\Wrappers;

use PHPUnit\Framework\TestCase;
use App\Models\Wrappers\TaskProgressWithTask;
use App\Models\Task;
use App\Models\TaskProgress;

final class TaskProgressWithTaskTest extends TestCase
{
    public function testJsonSerializationReturnsCorrectStructure(): void
    {
        $progressMock = $this->createMock(TaskProgress::class);
        $taskMock = $this->createMock(Task::class);

        $progressMock->method('jsonSerialize')->willReturn([
            'id_task_progress' => 11,
            'status' => 'in_progress',
        ]);

        $taskMock->method('jsonSerialize')->willReturn([
            'id_task' => 22,
            'name' => 'Rozdělej oheň',
        ]);

        $wrapper = new TaskProgressWithTask($progressMock, $taskMock);

        $expectedJson = json_encode([
            'progress' => ['id_task_progress' => 11, 'status' => 'in_progress'],
            'task' => ['id_task' => 22, 'name' => 'Rozdělej oheň'],
        ]);

        $this->assertJsonStringEqualsJsonString($expectedJson, json_encode($wrapper));
    }

    public function testWrapperStoresReferencesCorrectly(): void
    {
        $progressMock = $this->createMock(TaskProgress::class);
        $taskMock = $this->createMock(Task::class);

        $wrapper = new TaskProgressWithTask($progressMock, $taskMock);

        $this->assertSame($progressMock, $wrapper->progress);
        $this->assertSame($taskMock, $wrapper->task);
    }
}