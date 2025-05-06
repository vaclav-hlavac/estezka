<?php

namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Troop model representing a scouting troop entity.
 *
 * Provides basic serialization and validation logic for a troop, including its name and optional ID.
 */
class Troop extends BaseModel {
    public string $name;
    public ?int $id_troop;


    /**
     * @param array $data associative array with name and (optionally) id_troop
     */
    public function __construct(array $data) {
        $notNullArguments = [];
        $notEmptyArguments = ['name'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->name = $data['name'];
        $this->id_troop = $data['id_troop'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'name' => $this->name,
        ];

        if ($this->id_troop != null) { $data['id_troop'] = $this->id_troop;}

        return $data;
    }

    public function getId()
    {
        return $this->id_troop;
    }

    public function toDatabase()
    {
        return $this->jsonSerialize();
    }
}