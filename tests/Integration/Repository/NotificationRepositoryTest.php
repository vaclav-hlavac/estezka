<?php

declare(strict_types=1);
namespace Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use App\Repository\NotificationRepository;
use App\Models\Notification;
use PDO;
use Tests\TestUtils\DatabaseCleaner;

final class NotificationRepositoryTest extends TestCase
{
    private NotificationRepository $repository;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->repository = $container->get(NotificationRepository::class);
        $this->pdo = $container->get(PDO::class);

        // Clean up test data
        DatabaseCleaner::cleanAll($this->pdo);

        // Insert test users
        $this->pdo->prepare("
            INSERT INTO user (id_user, nickname, name, surname, password, email, notifications_enabled)
            VALUES (1, 'testuser', 'Test', 'User', 'testpass', 'test@example.com', 1)
        ")->execute();

        $this->pdo->prepare("
            INSERT INTO user (id_user, nickname, name, surname, password, email, notifications_enabled)
            VALUES (2, 'receiver', 'Receiver', 'User', 'pass', 'receiver@example.com', 1)
        ")->execute();
    }


    public function testFindAllForReceiverReturnsExpectedNotification(): void
    {
        // Insert test notification directly into the database
        $stmt = $this->pdo->prepare("
            INSERT INTO notification (id_user_creator, id_user_receiver, text, was_received, type)
            VALUES (1, 2, 'Test - find me', 0, 'info')
        ");
        $stmt->execute();
        $insertedId = (int) $this->pdo->lastInsertId();

        // Execute repository method
        $results = $this->repository->findAllForReceiver(2);

        // Assert results
        $this->assertCount(1, $results);
        $this->assertInstanceOf(Notification::class, $results[0]);
        $this->assertSame('Test - find me', $results[0]->text);
        $this->assertSame($insertedId, $results[0]->id_notification);
    }


    public function testMarkAsReceivedUpdatesNotification(): void
    {
        // Insert test notification
        $stmt = $this->pdo->prepare("
            INSERT INTO notification (id_user_creator, id_user_receiver, text, was_received, type)
            VALUES (2, 1, 'Test - mark me as received', 0, 'generic')
        ");
        $stmt->execute();
        $notificationId = (int) $this->pdo->lastInsertId();

        // Mark as received using repository
        $success = $this->repository->markAsReceived($notificationId);
        $this->assertTrue($success);

        // Verify updated state
        $stmt = $this->pdo->prepare("SELECT was_received FROM notification WHERE id_notification = :id");
        $stmt->execute(['id' => $notificationId]);
        $wasReceived = $stmt->fetchColumn();

        $this->assertEquals(1, $wasReceived);
    }
}