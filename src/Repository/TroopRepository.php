<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Roles\GangMember;
use App\Models\Troop;
use App\Models\User;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Troop>
 */
class TroopRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'troop', 'id_troop', Troop::class);
    }

    public function findAllMembersById(int $id_troop): array {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id_troop");
            $stmt->execute(['id_troop' => $id_troop]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * Find all patrol members belonging to a troop, including optional fields.
     *
     * @param int $troopId
     * @return GangMember[]
     * @throws DatabaseException
     */
    public function findAllMembersWithRoleGangMember(int $troopId): array
    {
        $sql = "
        SELECT 
            gm.id_user,
            gm.id_patrol,
            gm.active_path_level,
            
            g.name AS patrol_name,
            g.color AS patrol_color,
            
            t.id_troop AS id_troop,
            t.name AS troop_name,
            
            u.nickname,
            u.avatar_url,
            
            (
                SELECT COUNT(*) 
                FROM task_progress tp 
                WHERE tp.id_user = gm.id_user
            ) AS total_tasks,
            
            (
                SELECT COUNT(*) 
                FROM task_progress tp 
                WHERE tp.id_user = gm.id_user 
                AND tp.status = 'confirmed'
            ) AS completed_tasks
        FROM patrol_member gm
        INNER JOIN patrol g ON gm.id_patrol = g.id_patrol
        INNER JOIN troop t ON g.id_troop = t.id_troop
        INNER JOIN user u ON gm.id_user = u.id_user
        WHERE t.id_troop = :troopId
    ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['troopId' => $troopId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => new GangMember($row), $results);
    }

    /**
     * Checks whether the given user is a patrol member in a patrol that belongs to the specified troop.
     *
     * @param int $userId   The ID of the user to check.
     * @param int $troopId  The ID of the troop to verify membership within.
     * @return bool True if the user is a patrol member in the troop, false otherwise.
     * @throws DatabaseException If a database error occurs during the check.
     */
    public function isUserGangMemberInTroop(int $userId, int $troopId): bool
    {
        $sql = "SELECT COUNT(*) AS cnt
            FROM patrol_member gm
            JOIN patrol g ON gm.id_patrol = g.id_patrol
            WHERE gm.id_user = :id_user AND g.id_troop = :id_troop";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id_user' => $userId,
                'id_troop' => $troopId
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['cnt'] > 0;
        } catch (PDOException $e) {
            throw new DatabaseException("Chyba při ověřování členství člena družiny v oddílu: " . $e->getMessage(), 500, $e);
        }
    }

}