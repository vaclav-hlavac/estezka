<?php

namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';

class Troop extends BaseModel {
    public $name;
    public $id_troop;


    /**
     * @param array $data associative array with id_troop and name
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
        return [
            'id_troop' => $this->id_troop,
            'name' => $this->name
        ];
    }

    public function getId()
    {
        return $this->id_troop;
    }
}