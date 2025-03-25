<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Gang>
 */
class GangRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang', 'id_gang', Gang::class);
    }

    /**
     * @param int $id_troop
     * @return array<Gang>
     * @throws DatabaseException
     */
    public function findAllByTroopId(int $id_troop): array
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_troop = :id_troop");
            $stmt->execute(['id_troop' => $id_troop]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findGangByInviteCode(int $invite_code): ?Gang {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE invite_code = :invite_code");
        try {
            $stmt->execute(['invite_code' => $invite_code]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? new Gang($result) : null;
    }
}