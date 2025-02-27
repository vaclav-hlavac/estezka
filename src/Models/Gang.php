<?php

namespace App\Models;

use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../vendor/autoload.php';

class Gang  implements JsonSerializable
{
    public $name;
    public $troopId;
    public $gangId;

    /**
     * @param $name
     * @param $troopId
     * @param $gangId
     */
    public function __construct(array $data)
    {
        $this->requiredArgumentsControl();

        $this->name = $data['name'];
        $this->troopId = $data['id_troop'];
        $this->gangId = $data['id_gang'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_gang' => $this->gangId,
            'id_troop' => $this->troopId,
            'name' => $this->name
        ];
    }

    private function requiredArgumentsControl(): void
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Missing required field: name");
        }
        if (empty($data['id_troop'])) {
            throw new InvalidArgumentException("Missing required field: id_troop");
        }
        if (empty($data['id_gang'])) {
            throw new InvalidArgumentException("Missing required field: id_gang");
        }
    }
}