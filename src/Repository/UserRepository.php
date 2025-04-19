<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
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


    /**
     * @param $email
     * @return User|null
     * @throws DatabaseException
     */
    public function findByEmail($email): ?User {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");

        try{
            $stmt->execute([$email]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

    /**
     * @param string $email
     * @return bool
     * @throws DatabaseException
     */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) != null;
    }

    /**
     * @param array $data
     * @return User|null
     * @throws DatabaseException
     */
    public function insert(array $data): ?User {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return parent::insert($data);
    }
}