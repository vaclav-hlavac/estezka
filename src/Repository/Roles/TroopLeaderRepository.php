<?php

namespace App\Repository\Roles;

use App\Exceptions\DatabaseException;
use App\Models\Roles\TroopLeader;
use App\Repository\GenericRepository;
use PDO;
use PDOException;

require_once __DIR__ . '/../../../vendor/autoload.php';

class TroopLeaderRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'troop_leader', 'id_troop_leader', TroopLeader::class);
    }

    /**
     * Find TroopLeader by ID with troop details
     *
     * @param int $id
     * @return TroopLeader|null
     * @throws DatabaseException
     */
    public function findById(int $id): ?TroopLeader
    {
        $sql = "
            SELECT 
                tl.*,
                t.name AS troop_name
            FROM troop_leader tl
            INNER JOIN troop t ON tl.id_troop = t.id_troop
            WHERE tl.id_troop_leader = :id
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new TroopLeader($data) : null;
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find all TroopLeader roles by User ID
     *
     * @param int $userId
     * @return TroopLeader[]
     * @throws DatabaseException
     */
    public function findAllByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                tl.*,
                t.name AS troop_name
            FROM troop_leader tl
            INNER JOIN troop t ON tl.id_troop = t.id_troop
            WHERE tl.id_user = :id_user
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new TroopLeader($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find all TroopLeaders of a specific Troop
     *
     * @param int $troopId
     * @return TroopLeader[]
     * @throws DatabaseException
     */
    public function findAllByTroopId(int $troopId): array
    {
        $sql = "
            SELECT 
                tl.*,
                t.name AS troop_name
            FROM troop_leader tl
            INNER JOIN troop t ON tl.id_troop = t.id_troop
            WHERE tl.id_troop = :id_troop
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_troop' => $troopId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new TroopLeader($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find a TroopLeader by User ID and Troop ID
     *
     * @param int $userId
     * @param int $troopId
     * @return TroopLeader|null
     * @throws DatabaseException
     */
    public function findByUserAndTroopId(int $userId, int $troopId): ?TroopLeader
    {
        $sql = "
            SELECT 
                tl.*,
                t.name AS troop_name
            FROM troop_leader tl
            INNER JOIN troop t ON tl.id_troop = t.id_troop
            WHERE tl.id_user = :id_user AND tl.id_troop = :id_troop
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_user' => $userId,
                'id_troop' => $troopId
            ]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            return $data ? new TroopLeader($data) : null;
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Check if user is a TroopLeader of a Troop
     *
     * @param int $userId
     * @param int $troopId
     * @return bool
     * @throws DatabaseException
     */
    public function isUserTroopLeaderOfTroop(int $userId, int $troopId): bool
    {
        return $this->findByUserAndTroopId($userId, $troopId) !== null;
    }

    /**
     * Check if user is TroopLeader of a troop that owns the specified gang
     *
     * @param int $userId
     * @param int $patrolId
     * @return bool
     * @throws DatabaseException
     */
    public function isUserTroopLeaderWithGang(int $userId, int $patrolId): bool
    {
        $sql = "
            SELECT EXISTS(
                SELECT 1 
                FROM troop_leader tl
                JOIN patrol g ON tl.id_troop = g.id_troop
                WHERE g.id_patrol = :id_patrol AND tl.id_user = :id_user
            ) AS exists_result
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_patrol' => $patrolId,
                'id_user' => $userId,
            ]);
            $result = $stmt->fetchColumn();

            return (bool) $result;
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }
}