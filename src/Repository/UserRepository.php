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

    public function findAllByNickname($nickname): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :nickname");
        $stmt->execute(['nickname' => $nickname]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    public function findByEmail($email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }
}