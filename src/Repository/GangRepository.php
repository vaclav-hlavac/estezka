<?php

namespace App\Repository;

use App\Models\Gang;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Gang>
 */
class GangRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang', 'id_gang', Gang::class);
    }
}