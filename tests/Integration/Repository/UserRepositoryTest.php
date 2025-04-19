<?php

declare(strict_types=1);
namespace Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use App\Models\User;
use PDO;

final class UserRepositoryTest extends TestCase
{
    private UserRepository $repository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(UserRepository::class);
        $this->pdo = $container->get(PDO::class);

        // Clean user table
        $this->pdo->exec("DELETE FROM user");
        $this->pdo->exec("ALTER TABLE user AUTO_INCREMENT = 1");
    }

    /**
     * It should insert a new user and return it as a User model.
     */
    public function testInsertCreatesUserWithHashedPassword(): void
    {
        $data = [
            'nickname' => 'tester',
            'name' => 'Test',
            'surname' => 'User',
            'password' => 'secret123',
            'email' => 'test@example.com',
            'notifications_enabled' => true,
        ];

        $user = $this->repository->insert($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(password_verify('secret123', $user->password));
    }

    /**
     * It should return a user found by email.
     */
    public function testFindByEmailReturnsUser(): void
    {
        $this->insertTestUser('findme@example.com');

        $user = $this->repository->findByEmail('findme@example.com');

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('findme@example.com', $user->email);
    }

    /**
     * It should return true if the email exists.
     */
    public function testEmailExistsReturnsTrue(): void
    {
        $this->insertTestUser('exists@example.com');

        $this->assertTrue($this->repository->emailExists('exists@example.com'));
    }

    /**
     * It should return false if the email does not exist.
     */
    public function testEmailExistsReturnsFalse(): void
    {
        $this->assertFalse($this->repository->emailExists('nope@example.com'));
    }

    private function insertTestUser(string $email): void
    {
        $this->pdo->prepare("
            INSERT INTO user (nickname, name, surname, password, email, notifications_enabled)
            VALUES ('test', 'Test', 'User', :password, :email, 1)
        ")->execute([
            'password' => password_hash('testpass', PASSWORD_DEFAULT),
            'email' => $email,
        ]);
    }
}