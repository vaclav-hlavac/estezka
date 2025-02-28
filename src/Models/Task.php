<?php

namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

class Task extends BaseModel {
    protected $id_task;
    protected $number;
    protected $name;
    protected $description;
    protected $category;
    protected $subcategory;
    protected $tag;
    protected $id_troop;


    public function __construct(array $data) {
        $notNullArguments = ['number'];
        $notEmptyArguments = ['name', 'description', 'category', 'subcategory'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->number = $data['number'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->category = $data['category'];
        $this->subcategory = $data['subcategory'];
        $this->tag = $data['tag'] ?? null;
        $this->id_troop = $data['id_troop'] ?? null;
        $this->id_task = $data['id_task'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_task' => $this->id_task,
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'tag' => $this->tag,
            'id_troop' => $this->id_troop
        ];
    }

    public function getId()
    {
        return $this->id_task;
    }
}