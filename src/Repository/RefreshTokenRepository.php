<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\RefreshToken;
use App\Models\User;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Repository for managing refresh tokens stored in the `refresh_tokens` table.
 *
 * Provides methods to check token existence and to find the associated user ID.
 *
 * @extends GenericRepository<RefreshToken>
 */
class RefreshTokenRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'refresh_tokens', 'id_refresh_token', RefreshToken::class);
    }

    /**
     * Checks whether a refresh token exists in the database.
     *
     * @param string $token The refresh token to check.
     * @return bool True if the token exists, false otherwise.
     * @throws DatabaseException If a database error occurs.
     */
    public function tokenExists(string $token): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE token = :token");
        try {
            $stmt->execute(['token' => $token]);
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Finds the user ID associated with a given refresh token.
     *
     * @param string $refreshToken The refresh token to search by.
     * @return int The ID of the user associated with the token.
     * @throws DatabaseException If the token is not found or a database error occurs.
     */
    public function findUserIdByToken(string $refreshToken): int
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE token = :token");
        try {
            $stmt->execute(['token' => $refreshToken]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result || !isset($result['id_user'])) {
            throw new DatabaseException("Token not found", 404);
        }
        return (int) $result['id_user'];
    }

}