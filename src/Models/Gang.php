<?php

namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';

class Gang extends BaseModel
{
    public $name;
    public $id_troop;
    public $id_gang;

    /**
     * @param $name
     * @param $troopId
     * @param $gangId
     */
    public function __construct(array $data)
    {
        $notNullArguments = ['id_troop'];
        $notEmptyArguments = ['name'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->name = $data['name'];
        $this->id_troop = $data['id_troop'];
        $this->id_gang = $data['id_gang'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_gang' => $this->id_gang,
            'id_troop' => $this->id_troop,
            'name' => $this->name
        ];
    }

    public function getId()
    {
        return $this->id_gang;
    }
}