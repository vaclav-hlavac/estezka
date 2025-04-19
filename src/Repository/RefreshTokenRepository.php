<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\RefreshToken;
use App\Models\User;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


class RefreshTokenRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'refresh_tokens', 'id_refresh_token', RefreshToken::class);
    }

    /**
     * @param string $token
     * @return Bool
     * @throws DatabaseException
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
     * @param string $refreshToken
     * @return Int id_user
     * @throws DatabaseException
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