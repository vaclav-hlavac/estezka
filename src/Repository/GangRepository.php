<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Repository for managing patrols (represented as `Gang` models) stored in the `patrol` table.
 *
 * Provides access to patrols by troop or invite code.
 *
 * @extends GenericRepository<Gang>
 */
class GangRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'patrol', 'id_patrol', Gang::class);
    }

    /**
     * Find all patrols (gangs) that belong to the specified troop.
     *
     * @param int $id_troop The ID of the troop to fetch gangs for.
     * @return Gang[] Array of Gang instances belonging to the troop.
     * @throws DatabaseException If a database error occurs.
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

    /**
     * Find a patrol (gang) using its invite code.
     *
     * @param string $invite_code The unique invite code for the patrol.
     * @return Gang|null The matching Gang instance or null if not found.
     * @throws DatabaseException If a database error occurs.
     */
    public function findGangByInviteCode(string $invite_code): ?Gang {
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