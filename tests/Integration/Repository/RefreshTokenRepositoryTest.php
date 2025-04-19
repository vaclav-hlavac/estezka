<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Repository\RefreshTokenRepository;
use App\Exceptions\DatabaseException;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\TestUtils\DatabaseCleaner;

final class RefreshTokenRepositoryTest extends TestCase
{
    private PDO $pdo;
    private RefreshTokenRepository $repository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(RefreshTokenRepository::class);
        $this->pdo = $container->get(PDO::class);

        DatabaseCleaner::cleanAll($this->pdo);

        $this->seedData();
    }

    private function seedData(): void
    {
        $this->pdo->exec("
            INSERT INTO user (nickname, name, surname, password, email)
            VALUES ('nick', 'Refresh', 'User', 'secret', 'refresh@example.com');

            INSERT INTO refresh_tokens (id_user, token, expires_at)
            VALUES (1, 'valid-token-123', NOW() + INTERVAL 1 DAY);
        ");
    }

    public function testTokenExistsReturnsTrue(): void
    {
        $exists = $this->repository->tokenExists('valid-token-123');
        $this->assertTrue($exists);
    }

    public function testTokenExistsReturnsFalse(): void
    {
        $exists = $this->repository->tokenExists('nonexistent-token');
        $this->assertFalse($exists);
    }

    public function testFindUserIdByTokenReturnsCorrectUserId(): void
    {
        $userId = $this->repository->findUserIdByToken('valid-token-123');
        $this->assertSame(1, $userId);
    }

    public function testFindUserIdByTokenThrowsWhenTokenNotFound(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Token not found');

        $this->repository->findUserIdByToken('missing-token-xyz');
    }
}