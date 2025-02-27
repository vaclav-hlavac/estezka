<?php

namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';


use JsonSerializable;
use PDO;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class Task implements JsonSerializable{
    private $taskId;
    private $number;
    private $name;
    private $description;
    private $category;
    private $subcategory;
    private $tag;
    private $troopId;


    public function __construct(array $data) {
        $this->requiredArgumentsControl();

        $this->number = $data['number'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->category = $data['category'];
        $this->subcategory = $data['subcategory'];
        $this->tag = $data['tag'] ?? null;
        $this->troopId = $data['id_troop'] ?? null;
        $this->taskId = $data['id_task'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_task' => $this->taskId,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'tag' => $this->tag,
            'id_troop' => $this->troopId
        ];
    }

    // Uložení custom úkolu do databáze
    public function saveCustom($pdo) {
        $this->save($pdo);

        $stmt = $pdo->prepare("UPDATE task SET id_troop = ? WHERE id_task = ?");
        $stmt->execute([$this->troopId, $this->id]);
    }

    private function requiredArgumentsControl()
    {
        if (empty($data['id_task'])) {
            throw new InvalidArgumentException("Missing required field: id_task");
        }
        if (empty($data['number'])) {
            throw new InvalidArgumentException("Missing required field: number");
        }
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Missing required field: name");
        }
        if (empty($data['description'])) {
            throw new InvalidArgumentException("Missing required field: description");
        }
        if (empty($data['category'])) {
            throw new InvalidArgumentException("Missing required field: category");
        }
        if (empty($data['subcategory'])) {
            throw new InvalidArgumentException("Missing required field: subcategory");
        }
    }


}