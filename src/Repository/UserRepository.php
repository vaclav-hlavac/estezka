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

    public function findByLoginName($loginName): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE login_name = ?");
        $stmt->execute([$loginName]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

    public function findByEmail($email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== null;
    }

    public function insert(array $data): ?User {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return parent::insert($data);
    }
}