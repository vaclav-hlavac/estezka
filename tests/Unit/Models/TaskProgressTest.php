<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\TaskProgress;
use InvalidArgumentException;
use DateTime;

final class TaskProgressTest extends TestCase
{
    private array $confirmedData = [
        'id_user' => 1,
        'id_task' => 42,
        'status' => 'confirmed',
        'planned_to' => '2025-05-01T10:00:00+00:00',
        'signed_at' => '2025-05-02T10:00:00+00:00',
        'witness' => 'Vedoucí',
        'id_confirmed_by' => 3,
        'confirmed_by_nickname' => 'Alfa',
        'confirmed_at' => '2025-05-03T10:00:00+00:00',
    ];

    private array $dataWithId = [
        'id_task_progress' => 9,
        'id_user' => 2,
        'id_task' => 43,
    ];

    public function testInitializationWithFullData(): void
    {
        $data = $this->confirmedData;
        $taskProgress = new TaskProgress($data);

        $this->assertSame(1, $taskProgress->id_user);
        $this->assertSame(42, $taskProgress->id_task);
        $this->assertSame('confirmed', $taskProgress->status);
        $this->assertInstanceOf(DateTime::class, $taskProgress->planned_to);
        $this->assertInstanceOf(DateTime::class, $taskProgress->signed_at);
        $this->assertInstanceOf(DateTime::class, $taskProgress->confirmed_at);
        $this->assertSame('Vedoucí', $taskProgress->witness);
        $this->assertSame(3, $taskProgress->id_confirmed_by);
        $this->assertSame('Alfa', $taskProgress->confirmed_by_nickname);
    }

    public function testInitializationWithMinimalData(): void
    {
        $data = [
            'id_user' => 4,
            'id_task' => 5,
        ];

        $taskProgress = new TaskProgress($data);

        $this->assertSame(4, $taskProgress->id_user);
        $this->assertSame(5, $taskProgress->id_task);
        $this->assertSame('not_started', $taskProgress->status);
        $this->assertNull($taskProgress->planned_to);
        $this->assertNull($taskProgress->signed_at);
        $this->assertNull($taskProgress->confirmed_at);
        $this->assertNull($taskProgress->id_confirmed_by);
    }

    public function testJsonSerializationWithDates(): void
    {
        $data = $this->confirmedData;
        $taskProgress = new TaskProgress($data);
        $json = $taskProgress->jsonSerialize();

        $this->assertSame('confirmed', $json['status']);
        $this->assertSame('Vedoucí', $json['witness']);
        $this->assertSame('2025-05-01T10:00:00+00:00', $json['planned_to']);
        $this->assertSame('2025-05-02T10:00:00+00:00', $json['signed_at']);
        $this->assertSame('2025-05-03T10:00:00+00:00', $json['confirmed_at']);
        $this->assertSame('Alfa', $json['confirmed_by_nickname']);
    }

    public function testToDatabaseFormatsDates(): void
    {
        $data = $this->confirmedData;
        $taskProgress = new TaskProgress($data);
        $dbData = $taskProgress->toDatabase();

        $this->assertSame('confirmed', $dbData['status']);
        $this->assertSame('Vedoucí', $dbData['witness']);
        $this->assertMatchesRegularExpression('/^2025-05-01 \d{2}:\d{2}:\d{2}$/', $dbData['planned_to']);
        $this->assertMatchesRegularExpression('/^2025-05-02 \d{2}:\d{2}:\d{2}$/', $dbData['signed_at']);
        $this->assertMatchesRegularExpression('/^2025-05-03 \d{2}:\d{2}:\d{2}$/', $dbData['confirmed_at']);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TaskProgress([]);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $data = $this->dataWithId;
        $taskProgress = new TaskProgress($data);

        $this->assertSame(9, $taskProgress->getId());
    }
}