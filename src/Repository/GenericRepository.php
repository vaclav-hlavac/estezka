<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use PDO;
use PDOException;
use RuntimeException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @template T of object
 */
abstract class GenericRepository {
    protected PDO $pdo;
    protected string $table;
    protected string $primaryKey;
    protected string $modelClass;

    /**
     * @param class-string<T> $modelClass
     */
    public function __construct(PDO $pdo, string $table, string $primaryKey, string $modelClass) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->modelClass = $modelClass;
    }

    /**
     * @param int $id
     * @return object|null
     * @throws DatabaseException
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
     * @return T[]
     * @throws DatabaseException
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
     * Vloží nový záznam do databáze.
     * @param array $data
     * @return object|null nově vloženy záznam nebo null v případě nezdaru
     * @throws DatabaseException
     */
    public function insert(array $data): ?object {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        try {
            $stmt->execute($data);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /**
     * Aktualizuje záznam v databázi.
     * @param int $id
     * @param array $data
     * @return object|null
     * @throws DatabaseException
     */
    public function update(int $id, array $data): ?object {
        $fields = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data[$this->primaryKey] = $id;

        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET $fields WHERE {$this->primaryKey} = :{$this->primaryKey}");

        try {
            $stmt->execute($data);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /**
     * Smaže záznam podle ID.
     * @param int $id
     * @return void
     * @throws DatabaseException
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
     * Vytvoří instanci modelu dynamicky.
     * @param array $data
     * @return object
     */
    protected function hydrateModel(array $data): object {
        return new $this->modelClass($data); //todo az nebude potreba, odstarnit pdo z modelu
    }
}