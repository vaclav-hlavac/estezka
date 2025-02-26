<?php

namespace App\Repository;

use App\Models\Troop;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Troop>
 */
class TroopRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'troop', 'id_troop', Troop::class);
    }
}