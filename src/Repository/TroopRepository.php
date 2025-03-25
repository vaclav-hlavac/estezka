<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Troop;
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

}