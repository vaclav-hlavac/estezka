<?php

namespace model;

class Troop {
    public $id_troop;
    public $name;

    // Konstruktor pro vytvoření nové jednotky
    public function __construct($name) {
        $this->name = $name;
    }

    // Načtení jednotky z databáze podle ID
    public static function find($id, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM troop WHERE id_troop = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return new Troop($data['name']);
    }

    // Uložení jednotky do databáze
    public static function all(\PDO $pdo){
        $stmt = $pdo->query("SELECT * FROM troop");
        $troops = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $troops;
    }

    public function save($pdo) {
        if (isset($this->id_troop)) {
            // Aktualizace existující jednotky
            $stmt = $pdo->prepare("UPDATE troop SET name = ? WHERE id_troop = ?");
            $stmt->execute([$this->name, $this->id_troop]);
        } else {
            // Vložení nové jednotky
            $stmt = $pdo->prepare("INSERT INTO troop (name) VALUES (?)");
            $stmt->execute([$this->name]);
            $this->id_troop = $pdo->lastInsertId();
        }
    }

    // Smazání jednotky
    public function delete($pdo) {
        $stmt = $pdo->prepare("DELETE FROM troop WHERE id_troop = ?");
        $stmt->execute([$this->id_troop]);
    }

    // Převod objektu na asociativní pole (pro JSON)
    public function toArray() {
        return [
            'id_troop' => $this->id_troop,
            'name' => $this->name
        ];
    }
}