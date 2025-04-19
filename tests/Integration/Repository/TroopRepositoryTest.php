<?php
namespace Tests\Integration\Repository;


use App\Models\Roles\GangMember;
use App\Repository\TroopRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\TestUtils\DatabaseCleaner;

final class TroopRepositoryTest extends TestCase
{
    private PDO $pdo;
    private TroopRepository $repository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(TroopRepository::class);
        $this->pdo = $container->get(PDO::class);

        DatabaseCleaner::cleanAll($this->pdo);

        $this->seedData();
    }


    private function seedData(): void
    {
        $this->pdo->exec("
            INSERT INTO troop (name) VALUES ('Test Troop');
            INSERT INTO user (nickname, name, surname, password, email) 
            VALUES ('nick1', 'John', 'Doe', 'pass', 'john@example.com');

            INSERT INTO patrol (id_troop, name, color, invite_code) 
            VALUES (1, 'Red Patrol', 'red', 'abc123');

            INSERT INTO patrol_member (id_user, id_patrol, active_path_level) 
            VALUES (1, 1, 2);

            INSERT INTO task (number, name, description, category, subcategory, path_level) 
            VALUES (1, 'Task 1', 'Do something', 'A', 'B', 2);

            INSERT INTO task_progress (id_user, id_task, status) 
            VALUES (1, 1, 'confirmed');
        ");
    }

    public function testFindAllMembersWithRoleGangMember(): void
    {
        $members = $this->repository->findAllMembersWithRoleGangMember(1);

        $this->assertCount(1, $members);
        $this->assertInstanceOf(GangMember::class, $members[0]);
        $this->assertSame(1, $members[0]->completed_tasks);
        $this->assertSame(1, $members[0]->total_tasks);
    }

    public function testIsUserGangMemberInTroop(): void
    {
        $result = $this->repository->isUserGangMemberInTroop(1, 1);
        $this->assertTrue($result);

        $result = $this->repository->isUserGangMemberInTroop(999, 1);
        $this->assertFalse($result);
    }
}