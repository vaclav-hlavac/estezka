<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\User;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Repository class for interacting with the `user` table in the database.
 *
 * Extends the GenericRepository to provide basic CRUD operations and adds custom methods
 * specific to the User entity, such as lookup by email and hashed password insertion.
 *
 * @extends GenericRepository<User>
 */
class UserRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'user', 'id_user', User::class);
    }


    /**
     * Find a user by their email address.
     *
     * Executes a query to retrieve a user based on the given email. Returns null if no user is found.
     *
     * @param string $email The email address to search for.
     * @return User|null The corresponding User object or null if not found.
     * @throws DatabaseException If a database error occurs.
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
     * Check if a user with the given email exists in the database.
     *
     * @param string $email The email address to check.
     * @return bool True if a user with the email exists, false otherwise.
     * @throws DatabaseException If a database error occurs.
     */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) != null;
    }

    /**
     * Insert a new user into the database.
     *
     * If the provided data contains a plaintext password, it will be hashed before insertion.
     *
     * @param array $data The associative array of user data to insert.
     * @return User|null The created User object, or null if insertion failed.
     * @throws DatabaseException If a database error occurs during insertion.
     */
    public function insert(array $data): ?User {
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        return parent::insert($data);
    }
}