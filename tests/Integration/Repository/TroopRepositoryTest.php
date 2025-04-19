<?php
namespace Tests\Integration\Repository;


use App\Models\Roles\GangMember;
use App\Repository\TroopRepository;
use PDO;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../vendor/autoload.php';

class TroopRepositoryTest extends TestCase
{
    private PDO $pdo;
    private TroopRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=e_stezka_test', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->resetDatabase();
        $this->seedData();

        $this->repository = new TroopRepository($this->pdo);
    }

    private function resetDatabase(): void
    {
        $sql = file_get_contents(__DIR__ . '/../../create-script.sql');
        $this->pdo->exec($sql);
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