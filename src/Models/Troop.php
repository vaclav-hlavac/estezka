<?php

namespace App\Models;
use JsonSerializable;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

class Troop extends BaseModel{
    static protected $tableName = "troop";
    public $name;


    /**
     * @param $pdo
     * @param array $data associative array with id and name
     */
    public function __construct($pdo, array $data) {

        if (isset($data['id_troop'])) {
            $data['id'] = $data['id_troop'];
        }
        parent::__construct($pdo, $data['id'] ?? null);
        $this->name = $data['name'] ?? null;
    }

    public function save() {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Aktualizace existující jednotky
            $stmt = $this->pdo->prepare("UPDATE $tableName SET name = ? WHERE id_troop = ?");
            $stmt->execute([$this->name, $this->id]);
        } else {
            // Vložení nové jednotky
            $stmt = $this->pdo->prepare("INSERT INTO $tableName (name) VALUES (?)");
            $stmt->execute([$this->name]);
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_troop' => $this->id,
            'name' => $this->name
        ];
    }
}