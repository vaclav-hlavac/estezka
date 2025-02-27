<?php

namespace App\Repository\Roles;

use App\Models\Roles\TroopLeader;
use App\Repository\GenericRepository;
use PDO;

require_once __DIR__ . '/../../../vendor/autoload.php';


class TroopLeaderRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'troop_leader', 'id_troop_leader', TroopLeaderRepository::class);
    }

    public function findAllByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ?");
        $stmt->execute([$userId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findAllByTroopId(int $troopId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_troop = ?");
        $stmt->execute([$troopId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findByUserAndTroopId(int $userId, int $troopId): ?TroopLeader {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ? AND id_troop = ?");
        $stmt->execute([$userId, $troopId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

}