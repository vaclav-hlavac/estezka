<?php

namespace App\Models;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

class Troop implements JsonSerializable {
    public $name;
    public $troopId;


    /**
     * @param array $data associative array with id_troop and name
     */
    public function __construct(array $data) {
        $this->requiredArgumentsControl();

        $this->name = $data['name'];
        $this->troopId = $data['id_troop'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_troop' => $this->troopId,
            'name' => $this->name
        ];
    }

    private function requiredArgumentsControl()
    {
        if (empty($data['id_troop'])) {
            throw new InvalidArgumentException("Missing required field: id_troop");
        }
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Missing required field: name");
        }
    }
}