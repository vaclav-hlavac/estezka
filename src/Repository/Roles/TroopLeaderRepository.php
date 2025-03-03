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
        parent::__construct($pdo, 'troop_leader', 'id_troop_leader', TroopLeaderRepository::class);
    }

    /**
     * @param int $userId
     * @return array
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
     * @param int $troopId
     * @return array
     * @throws DatabaseException
     */
    public function findAllByTroopId(int $troopId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_troop = ?");

        try{
            $stmt->execute([$troopId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * @param int $userId
     * @param int $troopId
     * @return TroopLeader|null
     * @throws DatabaseException
     */
    public function findByUserAndTroopId(int $userId, int $troopId): ?TroopLeader {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ? AND id_troop = ?");

        try{
            $stmt->execute([$userId, $troopId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return $result ? $this->hydrateModel($result) : null;
    }

}