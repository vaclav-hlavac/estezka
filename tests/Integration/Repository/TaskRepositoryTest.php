<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Models\Task;
use App\Repository\TaskRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\TestUtils\DatabaseCleaner;

final class TaskRepositoryTest extends TestCase
{
    private PDO $pdo;
    private TaskRepository $repository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(TaskRepository::class);
        $this->pdo = $container->get(PDO::class);

        DatabaseCleaner::cleanAll($this->pdo);

        $this->seedData();
    }

    private function seedData(): void
    {
        // Vložíme dva obecné úkoly (bez id_troop) a jeden oddílový
        $this->pdo->exec("
            INSERT INTO task (number, name, description, category, subcategory, path_level)
            VALUES 
                (1, 'General Task 1', 'Description 1', 'Category A', 'Subcategory A', 1),
                (2, 'General Task 2', 'Description 2', 'Category B', 'Subcategory B', 2);

            INSERT INTO troop (name) VALUES ('Oddíl 1');
            INSERT INTO task (number, name, description, category, subcategory, path_level, id_troop)
            VALUES (3, 'Troop Task', 'Internal only', 'Category C', 'Subcategory C', 1, 1);
        ");
    }

    public function testFindAllGeneralTasks(): void
    {
        $tasks = $this->repository->findAllGeneralTasks();

        $this->assertCount(2, $tasks);
        $this->assertContainsOnlyInstancesOf(Task::class, $tasks);

        foreach ($tasks as $task) {
            $this->assertNull($task->id_troop, 'Expected general tasks to have NULL id_troop');
        }
    }

    public function testFindAllGeneralTasksByPathLevel(): void
    {
        $tasks = $this->repository->findAllGeneralTasksByPathLevel(2);

        $this->assertCount(1, $tasks);
        $this->assertInstanceOf(Task::class, $tasks[0]);
        $this->assertSame(2, $tasks[0]->path_level);
        $this->assertNull($tasks[0]->id_troop);
    }
}