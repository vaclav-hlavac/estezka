<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\TaskProgress;
use DateTime;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Repository for managing task progress records stored in the `task_progress` table.
 *
 * Handles creation of progress entries for users, updates of individual fields, and retrieval of records.
 *
 * @extends GenericRepository<TaskProgress>
 */
class TaskProgressRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task_progress', 'id_task_progress', TaskProgress::class);
    }

    /**
     * Create progress entries for all tasks available to the given user.
     *
     * This includes both general tasks (`id_troop IS NULL`) and tasks associated
     * with the troop that the user's patrol belongs to.
     *
     * @param int $id_user The ID of the user (must be present in `patrol_member`).
     * @return void
     * @throws DatabaseException If the user is not in a patrol or a DB error occurs.
     */
    public function createAllToUser(int $id_user): void
    {
        $sqlFindTroop = "
        SELECT t.id_troop
        FROM troop t
        JOIN patrol g ON g.id_troop = t.id_troop
        JOIN patrol_member gm ON gm.id_patrol = g.id_patrol
        WHERE gm.id_user = :id_user
        LIMIT 1
    ";

        try {
            $stmt = $this->pdo->prepare($sqlFindTroop);
            $stmt->execute(['id_user' => $id_user]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Chyba při získávání troop_id: " . $e->getMessage(), 500, $e);
        }

        if (!$row) {
            // User is not in any patrol
            throw new DatabaseException("Chyba při získávání troop_id: User is not in any patrol." , 400);
        }
        $troopId = (int)$row['id_troop'];


        $sqlInsert = "
        INSERT INTO task_progress (id_user, id_task, status)
        SELECT :id_user, t.id_task, 'not_started'
        FROM task t
        WHERE t.id_troop = :troop_id
           OR t.id_troop IS NULL
    ";

        try {
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                'id_user' => $id_user,
                'troop_id' => $troopId,
            ]);
        } catch (PDOException $e) {
            throw new DatabaseException("Chyba při vkládání do task_progress: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Check whether the user has any task progress records.
     *
     * @param int $id_user The ID of the user to check.
     * @return bool True if at least one task progress record exists, false otherwise.
     * @throws DatabaseException If a database error occurs.
     */
    public function userHasAnyProgress(int $id_user): bool
    {
        $sql = "SELECT COUNT(*) AS cnt 
            FROM task_progress 
            WHERE id_user = :id_user";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $id_user]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result && $result['cnt'] > 0;
        } catch (PDOException $e) {
            throw new DatabaseException("Chyba při ověřování task_progress: " . $e->getMessage(), 500, $e);
        }
    }


    /**
     * Retrieve all task progress records for a specific user.
     *
     * @param int $id_user The ID of the user whose task progress should be retrieved.
     * @return TaskProgress[] Array of TaskProgress objects.
     * @throws DatabaseException If a database error occurs.
     */
    public function findAllByIdUser(int $id_user): array
    {
        $sql = "SELECT * FROM task_progress WHERE id_user = :id_user";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_user' => $id_user]);

            $results =  $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(fn($row) => new TaskProgress($row), $results);


        } catch (PDOException $e) {
            throw new DatabaseException("Chyba při získávání task_progress pro uživatele: " . $e->getMessage(), 500, $e);
        }
    }
}