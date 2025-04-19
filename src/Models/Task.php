<?php

namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

class Task extends BaseModel {
    public ?int $id_task;
    public int $number;
    public string $name;
    public string $description;
    public string $category;
    public string $subcategory;
    public int $path_level;
    public ?string $tag;
    public ?int $id_troop;


    public function __construct(array $data) {
        $notNullArguments = ['number', 'path_level'];
        $notEmptyArguments = ['name', 'description', 'category', 'subcategory'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->number = $data['number'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->category = $data['category'];
        $this->subcategory = $data['subcategory'];
        $this->path_level = $data['path_level'];
        $this->tag = $data['tag'] ?? null;
        $this->id_troop = $data['id_troop'] ?? null;
        $this->id_task = $data['id_task'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'number' => $this->number,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'path_level' => $this->path_level,
        ];

        if ($this->id_troop != null) { $data['id_troop'] = $this->id_troop;}
        if ($this->tag != null) { $data['tag'] = $this->tag;}
        if ($this->id_task != null) { $data['id_task'] = $this->id_task;}

        return $data;
    }

    public function getId()
    {
        return $this->id_task;
    }

    public function toDatabase()
    {
        return $this->jsonSerialize();
    }
}