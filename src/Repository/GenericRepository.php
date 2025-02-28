<?php

namespace App\Repository;

use PDO;
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
     * @return T|null
     */
    public function findById(int $id): ?object {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id");
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrateModel($result) : null;
    }

    /**
     * @return T[]
     */
    public function findAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * Vloží nový záznam do databáze.
     * @param array $data
     * @return T|null nově vloženy záznam nebo null v případě nezdaru
     */
    public function insert(array $data): ?object {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));

        $stmt = $this->pdo->prepare("INSERT INTO {$this->table} ($columns) VALUES ($placeholders)");
        $stmt->execute($data);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /**
     * Aktualizuje záznam v databázi.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): ?object {
        $fields = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
        $data[$this->primaryKey] = $id;

        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET $fields WHERE {$this->primaryKey} = :{$this->primaryKey}");
        $stmt->execute($data);

        return $this->findById((int) $this->pdo->lastInsertId());
    }

    /**
     * Smaže záznam podle ID.
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Vytvoří instanci modelu dynamicky.
     * @param array $data
     * @return T
     */
    protected function hydrateModel(array $data): object {
        return new $this->modelClass($this->pdo, $data); //todo az nebude potreba, odstarnit pdo z modelu
    }
}