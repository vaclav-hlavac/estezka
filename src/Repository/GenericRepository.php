<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use DateTime;
use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Abstract generic repository for common CRUD operations on database models.
 *
 * @template T of object
 */
abstract class GenericRepository {
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey;
    protected string $modelClass;

    /**
     * @param PDO $pdo The PDO database connection.
     * @param string $table The name of the database table.
     * @param string $primaryKey The name of the table's primary key.
     * @param class-string<T> $modelClass Fully qualified class name of the model to hydrate.
     */
    public function __construct(PDO $pdo, string $table, string $primaryKey, string $modelClass) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->modelClass = $modelClass;
    }

    /**
     * Find a record by its primary key.
     *
     * @param int $id The primary key value.
     * @return T|null The found model instance or null if not found.
     * @throws DatabaseException If a database error occurs.
     */
    public function findById(int $id): ?object {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        try {
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $this->hydrateModel($result);
    }

    /**
     * Retrieve all records from the table.
     *
     * @return T[] Array of all model instances.
     * @throws DatabaseException If a database error occurs.
     */
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        try {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * Insert a new record into the table.
     *
     * @param array $data The associative array of column => value pairs to insert.
     * @return T|null The newly inserted model or null if insert failed.
     * @throws DatabaseException If a database error occurs.
     */
    public function insert(array $data): ?object {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");

        try {
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        $insertedId = $data[$this->primaryKey] ?? (int) $this->pdo->lastInsertId();

        return $this->findById($insertedId);
    }


    /**
     * Update an existing record by its primary key.
     *
     * @param int $id The ID of the record to update.
     * @param array $data The associative array of fields to update.
     * @return T|null The updated model instance.
     * @throws DatabaseException If a database error occurs.
     * @throws RuntimeException If the update data array is empty.
     */
    public function update(int $id, array $data): ?object
    {
        if (empty($data)) {
            throw new RuntimeException("No data provided for update");
        }

        $fields = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data[$this->primaryKey] = $id;

        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET $fields WHERE {$this->primaryKey} = :{$this->primaryKey}");

        try {
            // Manual binding
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return $this->findById($id);
    }

    /**
     * Delete a record by its primary key.
     *
     * @param int $id The ID of the record to delete.
     * @return void
     * @throws DatabaseException If a database error occurs.
     */
    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        try {
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
    }

    /**
     * Hydrates an associative array of data into a model instance.
     *
     * @param array|false|null $data The raw data from a database row.
     * @return T|null The hydrated model or null if data is empty.
     */
    protected function hydrateModel(array|false|null $data): ?object {
        if (!$data) {
            return null;
        }

        return new $this->modelClass($data);
    }
}