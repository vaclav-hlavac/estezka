<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\TaskProgress;
use DateTime;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<TaskProgress>
 */
class TaskProgressRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'task_progress', 'id_task_progress', TaskProgress::class);
    }

    /**
     * Create task_progress for all general tasks and for all tasks
     * of a troop, that the user is in (over patrol_member → patrol → troop).
     *
     * @param int $id_user ID uživatele (musí existovat v patrol_member).
     * @return void
     * @throws DatabaseException pokud dojde k DB chybě.
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
     * Saves DateTime atribut to planned_to column of Task by task's ID
     * @param int $id
     * @param DateTime $plannedTo
     * @return void
     * @throws DatabaseException
     */
    public function savePlannedTo(int $id, DateTime $plannedTo): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET planned_to = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$plannedTo->format('Y-m-d'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param DateTime $completedAt
     * @return void
     * @throws DatabaseException
     */
    public function saveCompletedAt(int $id, DateTime $completedAt): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET completed_at = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$completedAt->format('Y-m-d H:i:s'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param int $confirmedBy
     * @return void
     * @throws DatabaseException
     */
    public function saveConfirmedBy(int $id, int $confirmedBy): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_by = ? WHERE {$this->primaryKey} = ?");

        try {
            $stmt->execute([$confirmedBy, $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param int $confirmedAt
     * @return void
     * @throws DatabaseException
     */
    public function saveConfirmedAt(int $id, int $confirmedAt): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET confirmed_at = ? WHERE {$this->primaryKey} = ?");

        try{
            $stmt->execute([$confirmedAt->format('Y-m-d H:i:s'), $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * @param int $id
     * @param string $filledText
     * @return void
     * @throws DatabaseException
     */
    public function saveFilledText(int $id, string $filledText): void {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET filled_text = ? WHERE {$this->primaryKey} = ?");

        try{
            $stmt->execute([$filledText, $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * CHeck, if user (id_user) has already inserted any task_progress.
     *
     * @param int $id_user
     * @return bool true, if exists at least one row
     * @throws DatabaseException
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
     * Retrieves all task progress records associated with a specific user.
     *
     * @param int $id_user The ID of the user whose task progress we want to retrieve.
     * @return array<TaskProgress> An array of all matching task progress records.
     * @throws DatabaseException If a database error occurs during the operation.
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