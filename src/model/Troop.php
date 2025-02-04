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

    // Načtení jednotky z databáze podle ID
/*    public static function find($id, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM troop WHERE id_troop = ?");
        $stmt->execute([$id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null; // Nebyl nalezen žádný záznam
        }

        return new Troop($data['name'], $data['id_troop']);
    }*/

    // Uložení jednotky do databáze
/*    public static function all($pdo){
        $stmt = $pdo->query("SELECT * FROM troop");
        $troops = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $troops;
    }*/

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

    // Smazání jednotky
   /* public function delete($pdo) {
        $stmt = $pdo->prepare("DELETE FROM troop WHERE id_troop = ?");
        $stmt->execute([$this->id_troop]);
    }*/

    // Převod objektu na asociativní pole (pro JSON)
    public function toArray() {
        return [
            'id_troop' => $this->id_troop,
            'name' => $this->name
        ];
    }
}