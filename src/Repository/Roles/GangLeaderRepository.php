<?php

namespace App\Repository\Roles;

use App\Exceptions\DatabaseException;
use App\Models\Roles\GangLeader;
use App\Repository\GenericRepository;
use PDO;
use PDOException;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * Repository for managing patrol leaders (gang leaders) stored in the `patrol_leader` table.
 *
 * Provides enriched queries for loading gang leadership roles with related patrol and troop metadata.
 *
 * @extends GenericRepository<GangLeader>
 */
class GangLeaderRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'patrol_leader', 'id_patrol_leader', GangLeader::class);
    }

    /**
     * Find a PatrolLeader by ID.
     * Includes patrol name, color, troop ID and troop name.
     *
     * @param int $id
     * @return GangLeader|null
     * @throws DatabaseException
     */
    public function findById(int $id): ?GangLeader
    {
        $sql = "
            SELECT 
                gl.*,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name
            FROM patrol_leader gl
            INNER JOIN patrol g ON gl.id_patrol = g.id_patrol
            INNER JOIN troop t ON g.id_troop = t.id_troop
            WHERE gl.id_patrol_leader = :id
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return new GangLeader($data);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find all GangLeader roles of a user by user's ID.
     *
     * @param int $userId
     * @return GangLeader[]
     * @throws DatabaseException
     */
    public function findAllByUserId(int $userId): array
    {
        $sql = "
            SELECT 
                gl.*,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name
            FROM patrol_leader gl
            INNER JOIN patrol g ON gl.id_patrol = g.id_patrol
            INNER JOIN troop t ON g.id_troop = t.id_troop
            WHERE gl.id_user = :id_user
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new GangLeader($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find all GangLeaders of a specific gang by gang ID.
     *
     * @param int $gangId
     * @return GangLeader[]
     * @throws DatabaseException
     */
    public function findAllByGangId(int $patrolId): array
    {
        $sql = "
            SELECT 
                gl.*,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name
            FROM patrol_leader gl
            INNER JOIN patrol g ON gl.id_patrol = g.id_patrol
            INNER JOIN troop t ON g.id_troop = t.id_troop
            WHERE gl.id_patrol = :id_patrol
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_patrol' => $patrolId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new GangLeader($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Find GangLeader role by user ID and gang ID.
     *
     * @param int $userId
     * @param int $gangId
     * @return GangLeader|null
     * @throws DatabaseException
     */
    public function findByUserAndGangId(int $userId, int $patrolId): ?GangLeader
    {
        $sql = "
            SELECT 
                gl.*,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name
            FROM patrol_leader gl
            INNER JOIN patrol g ON gl.id_patrol = g.id_patrol
            INNER JOIN troop t ON g.id_troop = t.id_troop
            WHERE gl.id_user = :id_user AND gl.id_patrol = :id_patrol
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $userId, 'id_patrol' => $patrolId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return new GangLeader($data);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Check if a user is a gang leader of a specific gang.
     *
     * @param int $userId
     * @param int $gangId
     * @return bool
     * @throws DatabaseException
     */
    public function isUserGangLeaderOfGang(int $userId, int $patrolId): bool
    {
        return $this->findByUserAndGangId($userId, $patrolId) !== null;
    }
}