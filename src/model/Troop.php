<?php

namespace model;
use PDO;

require_once __DIR__ . '/../autoloader.php';

class Troop extends BaseModel{
    static protected $tableName = "troop";

    public $name;

    // Konstruktor pro vytvoření nové jednotky
    public function __construct($name, $id_troop = null) {
        parent::__construct($id_troop);
        $this->name = $name;
    }

    public function save($pdo) {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Aktualizace existující jednotky
            $stmt = $pdo->prepare("UPDATE $tableName SET name = ? WHERE id_troop = ?");
            $stmt->execute([$this->name, $this->id]);
        } else {
            // Vložení nové jednotky
            $stmt = $pdo->prepare("INSERT INTO $tableName (name) VALUES (?)");
            $stmt->execute([$this->name]);
            $this->id = $pdo->lastInsertId();
        }

    }

    // Převod objektu na asociativní pole (pro JSON)
    public function toArray() {
        return [
            'id_troop' => $this->id_troop,
            'name' => $this->name
        ];
    }
}