<?php

namespace App\Repository;

use App\Models\User;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<User>
 */
class UserRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'user', 'id_user', User::class);
    }
}