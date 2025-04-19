<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Comment;
use InvalidArgumentException;

final class CommentTest extends TestCase
{
    private array $baseData = [
        'user_by' => 1,
        'user_to' => 2,
        'posted_at' => '2025-04-17T14:00:00+00:00',
        'text' => 'Dobrá práce!',
        'id_comment' => 10,
        'id_task_progress' => 7,
    ];

    public function testInitializationWithValidData(): void
    {
        $comment = new Comment($this->baseData);

        $this->assertSame(1, $comment->user_by);
        $this->assertSame(2, $comment->user_to);
        $this->assertSame('Dobrá práce!', $comment->text);
        $this->assertSame(10, $comment->id_comment);
        $this->assertSame(7, $comment->id_task_progress);
        $this->assertInstanceOf(\DateTime::class, $comment->posted_at);
        $this->assertSame('2025-04-17T14:00:00+00:00', $comment->posted_at->format(\DateTimeInterface::ATOM));
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Comment([]);
    }

    public function testEmptyTextThrowsException(): void
    {
        $data = [
            'user_by' => 1,
            'user_to' => 2,
            'posted_at' => '2025-04-17T14:00:00+00:00',
            'text' => '',
        ];

        $this->expectException(InvalidArgumentException::class);
        new Comment($data);
    }

    public function testJsonSerializeStructure(): void
    {
        $comment = new Comment($this->baseData);
        $json = $comment->jsonSerialize();

        $this->assertSame(10, $json['id_comment']);
        $this->assertSame(7, $json['id_task_progress']);
        $this->assertSame(1, $json['user_by']);
        $this->assertSame(2, $json['user_to']);
        $this->assertSame('Dobrá práce!', $json['text']);
        $this->assertSame('2025-04-17T14:00:00+00:00', $json['posted_at']);
    }

    public function testToDatabaseReturnsExpectedStructure(): void
    {
        $comment = new Comment($this->baseData);
        $db = $comment->toDatabase();

        $this->assertSame(10, $db['id_comment']);
        $this->assertSame(7, $db['id_task_progress']);
        $this->assertSame(1, $db['user_by']);
        $this->assertSame(2, $db['user_to']);
        $this->assertSame('Dobrá práce!', $db['text']);
        $this->assertMatchesRegularExpression('/^2025-04-17 \d{2}:\d{2}:\d{2}$/', $db['posted_at']);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $comment = new Comment($this->baseData);
        $this->assertSame(10, $comment->getId());
    }
}