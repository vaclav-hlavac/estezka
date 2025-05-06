<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Notification;
use PDO;
use PDOException;

/**
 * Repository for accessing notification data from the `notification` table.
 *
 * Provides methods to retrieve notifications for a user and to mark them as received.
 *
 * @extends GenericRepository<Notification>
 */
class NotificationRepository extends GenericRepository
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'notification', 'id_notification', Notification::class);
    }

    /**
     * Returns all notifications received by a given user.
     *
     * Notifications are ordered by ID in descending order (latest first).
     *
     * @param int $receiverId The ID of the user receiving the notifications.
     * @return Notification[] Array of Notification model instances.
     * @throws DatabaseException If a database error occurs.
     */
    public function findAllForReceiver(int $receiverId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user_receiver = :receiverId ORDER BY id_notification DESC");
            $stmt->execute(['receiverId' => $receiverId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Error fetching notifications: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * Marks a specific notification as received (sets `was_received = 1`).
     *
     * @param int $notificationId The ID of the notification to update.
     * @return bool True if the update was successful, false otherwise.
     * @throws DatabaseException If a database error occurs.
     */
    public function markAsReceived(int $notificationId): bool
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET was_received = 1 WHERE {$this->primaryKey} = :id");
            return $stmt->execute(['id' => $notificationId]);
        } catch (PDOException $e) {
            throw new DatabaseException("Error updating notification: " . $e->getMessage(), 500, $e);
        }
    }
}