<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\BaseModel;
use InvalidArgumentException;
use DateTime;

class DummyModel extends BaseModel {
    public ?int $id = null;
    public ?string $name = null;
    public ?bool $enabled = null;
    public ?DateTime $created_at = null;

    public function jsonSerialize(): mixed
    {
        return [];
    }
    public function getId() { return $this->id; }
    public function toDatabase(): array { return []; }

    public function testRequired(array $data, array $notNull = [], array $notEmpty = []): void
    {
        $this->requiredArgumentsControl($data, $notNull, $notEmpty);
    }

    public function testFormat($value): mixed
    {
        return $this->formatForDatabase($value);
    }

    public function testToDateTime($value): ?DateTime
    {
        return $this->convertToDateTimeIfNeeded($value);
    }
}

final class BaseModelTest extends TestCase
{
    public function testRequiredArgumentsControlThrowsException(): void
    {
        $model = new DummyModel();

        $this->expectException(InvalidArgumentException::class);
        $model->testRequired([], ['name'], []);
    }

    public function testRequiredArgumentsControlThrowsOnEmpty(): void
    {
        $model = new DummyModel();

        $this->expectException(InvalidArgumentException::class);
        $model->testRequired(['name' => ''], [], ['name']);
    }

    public function testSetAttributesWorks(): void
    {
        $model = new DummyModel();
        $model->setAttributes([
            'name' => 'Test',
            'enabled' => true,
        ]);

        $this->assertSame('Test', $model->name);
        $this->assertTrue($model->enabled);
    }

    public function testSetAttributesWithUnknownKeyThrows(): void
    {
        $model = new DummyModel();

        $this->expectException(InvalidArgumentException::class);
        $model->setAttributes([
            'unknown_property' => 'value',
        ]);
    }

    public function testFormatForDatabaseHandlesDateTime(): void
    {
        $model = new DummyModel();
        $dt = new DateTime('2025-04-17 12:00:00');
        $this->assertSame('2025-04-17 12:00:00', $model->testFormat($dt));
    }

    public function testFormatForDatabaseHandlesBoolean(): void
    {
        $model = new DummyModel();
        $this->assertSame(1, $model->testFormat(true));
        $this->assertSame(0, $model->testFormat(false));
    }

    public function testConvertToDateTimeWorks(): void
    {
        $model = new DummyModel();

        $dt = $model->testToDateTime('2025-04-17T12:00:00+00:00');
        $this->assertInstanceOf(DateTime::class, $dt);
        $this->assertSame('2025-04-17 12:00:00', $dt->format('Y-m-d H:i:s'));
    }

    public function testConvertToDateTimeWithDateTimeInput(): void
    {
        $model = new DummyModel();
        $now = new DateTime();
        $this->assertSame($now, $model->testToDateTime($now));
    }

    public function testConvertToDateTimeWithNull(): void
    {
        $model = new DummyModel();
        $this->assertNull($model->testToDateTime(null));
    }
}