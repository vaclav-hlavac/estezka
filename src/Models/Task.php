<?php

namespace App\Models;
//require_once __DIR__ . '/../autoloader.php';
require_once __DIR__ . '/../../vendor/autoload.php';


use PDO;

class Task {
    public $id;
    public $number;
    public $name;
    public $description;
    public $category;
    public $tag;

    // Konstruktor pro vytvoření nového úkolu
    public function __construct($number, $name, $description, $category, $tag = null, $id = null) {
        $this->number = $number;
        $this->name = $name;
        $this->description = $description;
        $this->category = $category;
        $this->tag = $tag;
        $this->id = $id;
    }

    // Načtení úkolu z databáze podle ID
    public static function find($id, $pdo) {
        $stmt = $pdo->prepare("SELECT * FROM task WHERE id_task = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return new Task($data['number'], $data['name'], $data['description'], $data['category'], $data['tag'], $data['id_task']);
    }

    // Uložení úkolu do databáze
    public function save($pdo) {
        if (isset($this->id)) {
            // Aktualizace existujícího úkolu
            $stmt = $pdo->prepare("UPDATE task SET number = ?, name = ?, description = ?, category = ?, tag = ? WHERE id_task = ?");
            $stmt->execute([$this->number, $this->name, $this->description, $this->category, $this->tag, $this->id]);
        } else {
            // Vložení nového úkolu
            $stmt = $pdo->prepare("INSERT INTO task (number, name, description, category, tag) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$this->number, $this->name, $this->description, $this->category, $this->tag]);
            $this->id = $pdo->lastInsertId();
        }
    }

    // Smazání úkolu
    public function delete($pdo) {
        $stmt = $pdo->prepare("DELETE FROM task WHERE id_task = ?");
        $stmt->execute([$this->id]);
    }

    // Statická metoda pro získání všech úkolů
    public static function all($pdo) {
        // Příprava a vykonání SQL dotazu
        $stmt = $pdo->query("SELECT * FROM task");

        // Získání výsledků dotazu
        $tasksData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Vytvoření pole objektů Task
        $tasks = [];
        foreach ($tasksData as $taskData) {
            // Vytvoříme nový objekt Task pro každý řádek
            $tasks[] = new Task($taskData['number'], $taskData['name'], $taskData['description'], $taskData['category'], $taskData['tag'], $taskData['id_task']);
        }

        // Vrátíme pole objektů Task
        return $tasks;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'tag' => $this->tag
        ];
    }
}