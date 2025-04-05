<?php

namespace App\Repository\Roles;

use App\Exceptions\DatabaseException;
use App\Models\Roles\GangMember;
use App\Repository\GenericRepository;
use PDO;
use PDOException;

class GangMemberRepository extends GenericRepository
{
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, 'patrol_member', 'id_user', GangMember::class);
    }

    public function findById(int $id_user): ?GangMember
    {
        $sql = "
            SELECT 
                gm.id_user,
                gm.id_patrol,
                u.nickname,
                u.avatar_url,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name,
                gm.active_path_level,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user
                ) AS total_tasks,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user AND tp.status = 'completed'
                ) AS completed_tasks
            FROM patrol_member gm
            INNER JOIN user u ON u.id_user = gm.id_user
            INNER JOIN patrol g ON g.id_patrol = gm.id_patrol
            INNER JOIN troop t ON t.id_troop = g.id_troop
            WHERE gm.id_user = :id_user
        ";


        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $id_user]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return null;
            }

            return new GangMember($data);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Finds all GangMembers belonging to a given Gang ID.
     *
     * @param int $gangId
     * @return GangMember[]
     * @throws DatabaseException
     */
    public function findAllByGangId(int $patrolId): array
    {
        $sql = "
            SELECT 
                gm.id_user,
                gm.id_patrol,
                u.nickname,
                u.avatar_url,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name,
                gm.active_path_level,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user
                ) AS total_tasks,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user AND tp.status = 'completed'
                ) AS completed_tasks
            FROM patrol_member gm
            INNER JOIN user u ON u.id_user = gm.id_user
            INNER JOIN patrol g ON g.id_patrol = gm.id_patrol
            INNER JOIN troop t ON t.id_troop = g.id_troop
            WHERE gm.id_patrol = :id_patrol
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_patrol' => $patrolId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new GangMember($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Finds all GangMembers belonging to a given Troop ID.
     *
     * @param int $troopId
     * @return GangMember[]
     * @throws DatabaseException
     */
    public function findAllByTroopId(int $troopId): array
    {
        $sql = "
            SELECT 
                gm.id_user,
                gm.id_patrol,
                u.nickname,
                u.avatar_url,
                g.name AS patrol_name,
                g.color AS patrol_color,
                t.id_troop AS id_troop,
                t.name AS troop_name,
                gm.active_path_level,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user
                ) AS total_tasks,
                (
                    SELECT COUNT(*) FROM task_progress tp WHERE tp.id_user = gm.id_user AND tp.status = 'completed'
                ) AS completed_tasks
            FROM patrol_member gm
            INNER JOIN user u ON u.id_user = gm.id_user
            INNER JOIN patrol g ON g.id_patrol = gm.id_patrol
            INNER JOIN troop t ON t.id_troop = g.id_troop
            WHERE t.id_troop = :id_troop
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_troop' => $troopId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new GangMember($row), $results);
        } catch (PDOException $e) {
            throw new DatabaseException('Database error: ' . $e->getMessage(), 500, $e);
        }
    }
}