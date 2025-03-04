<?php

namespace App\Repository\Roles;

use App\Exceptions\DatabaseException;
use App\Models\Roles\GangLeader;
use App\Repository\GenericRepository;
use PDO;
use PDOException;

require_once __DIR__ . '/../../../vendor/autoload.php';


class GangLeaderRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang_leader', 'id_gang_leader', GangLeader::class);
    }

    /**
     * Find all roles of a user by user's ID.
     * @param $userId int ID of user, whose roles are searched
     * @return array Array of GangLeader roles of user.
     * @throws DatabaseException
     */
    public function findAllByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ?");

        try{
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * @param int $gangId
     * @return array
     * @throws DatabaseException
     */
    public function findAllByGangId(int $gangId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_gang = ?");
        try{
            $stmt->execute([$gangId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * @param int $userId
     * @param int $gangId
     * @return GangLeader|null
     * @throws DatabaseException
     */
    public function findByUserAndGangId(int $userId, int $gangId): ?GangLeader {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ? AND id_gang = ?");

        try{
            $stmt->execute([$userId, $gangId]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

    /**
     * @param int $userId
     * @param int $gangId
     * @return bool
     * @throws DatabaseException
     */
    public function isUserGangLeaderOfGang(int $userId, int $gangId): bool
    {
        if($this->findByUserAndGangId($userId, $gangId) == null) {
            return false;
        }
        return true;
    }
}