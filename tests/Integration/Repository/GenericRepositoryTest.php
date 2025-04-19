<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Models\User;
use App\Repository\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\TestUtils\DatabaseCleaner;

final class GenericRepositoryTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(UserRepository::class);
        $this->pdo = $container->get(PDO::class);

        DatabaseCleaner::cleanAll($this->pdo);
    }

    public function testInsertAndFindById(): void
    {
        $data = $this->getSampleUserData();

        $inserted = $this->repository->insert($data);

        $this->assertInstanceOf(User::class, $inserted);
        $this->assertSame($data['email'], $inserted->email);

        $found = $this->repository->findById($inserted->id_user);
        $this->assertEquals($inserted->id_user, $found->id_user);
    }

    public function testUpdate(): void
    {
        $user = $this->repository->insert($this->getSampleUserData());

        $updated = $this->repository->update($user->id_user, ['nickname' => 'updatedNick']);

        $this->assertInstanceOf(User::class, $updated);
        $this->assertSame('updatedNick', $updated->nickname);
    }

    public function testDelete(): void
    {
        $user = $this->repository->insert($this->getSampleUserData());

        $this->repository->delete($user->id_user);
        $found = $this->repository->findById($user->id_user);

        $this->assertNull($found);
    }

    public function testFindAll(): void
    {
        $this->repository->insert($this->getSampleUserData('one@example.com'));
        $this->repository->insert($this->getSampleUserData('two@example.com'));

        $users = $this->repository->findAll();

        $this->assertCount(2, $users);
        $this->assertContainsOnlyInstancesOf(User::class, $users);
    }

    public function testUpdateWithEmptyDataThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->repository->update(1, []);
    }

    private function getSampleUserData(string $email = 'test@example.com'): array
    {
        return [
            'nickname' => 'tester',
            'name' => 'Test',
            'surname' => 'User',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'email' => $email,
            'notifications_enabled' => true,
        ];
    }
}