<?php

namespace App\Repository\Roles;

use App\Models\Roles\GangLeader;
use App\Repository\GenericRepository;
use PDO;

require_once __DIR__ . '/../../../vendor/autoload.php';


class GangLeaderRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang_leader', 'id_gang_leader', GangLeader::class);
    }

    /**
     * Find all roles of a user by user's ID.
     * @param $pdo
     * @param $userId int ID of user, whose roles are searched
     * @return array Array of GangLeader roles of user.
     */
    public function findAllByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ?");
        $stmt->execute([$userId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findAllByGangId(int $gangId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_gang = ?");
        $stmt->execute([$gangId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findByUserAndGangId(int $userId, int $gangId): ?GangLeader {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_user = ? AND id_gang = ?");
        $stmt->execute([$userId, $gangId]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }
}