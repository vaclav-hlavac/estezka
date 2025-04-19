<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Models\Gang;
use App\Repository\GangRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\TestUtils\DatabaseCleaner;

final class GangRepositoryTest extends TestCase
{
    private PDO $pdo;
    private GangRepository $repository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(GangRepository::class);
        $this->pdo = $container->get(PDO::class);

        DatabaseCleaner::cleanAll($this->pdo);

        $this->seedData();
    }

    private function seedData(): void
    {
        $this->pdo->exec("
            INSERT INTO troop (name) VALUES ('Test Troop');
            INSERT INTO patrol (id_troop, name, color, invite_code)
            VALUES 
                (1, 'Red Patrol', 'red', 'code-123'),
                (1, 'Blue Patrol', 'blue', 'code-456');
        ");
    }

    public function testFindAllByTroopIdReturnsAllPatrols(): void
    {
        $patrols = $this->repository->findAllByTroopId(1);

        $this->assertCount(2, $patrols);
        $this->assertContainsOnlyInstancesOf(Gang::class, $patrols);
    }

    public function testFindGangByInviteCodeReturnsCorrectGang(): void
    {
        $gang = $this->repository->findGangByInviteCode('code-123');

        $this->assertInstanceOf(Gang::class, $gang);
        $this->assertSame('code-123', $gang->invite_code);
        $this->assertSame('Red Patrol', $gang->name);
    }

    public function testFindGangByInviteCodeReturnsNullIfNotFound(): void
    {
        $gang = $this->repository->findGangByInviteCode('non-existent-code');

        $this->assertNull($gang);
    }
}