<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
use InvalidArgumentException;
use JsonSerializable;
use PDO;
require_once __DIR__ . '/../../../vendor/autoload.php';


class TroopLeader implements JsonSerializable
{
    public $userId;
    public $troopId;

    public function __construct(array $data) {
        $this->requiredArgumentsControl();

        $this->userId = $data['id_user'];
        $this->troopId = $data['id_troop'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->userId,
            'id_troop' => $this->troopId
        ];
    }

    private function requiredArgumentsControl(): void
    {
        if (empty($data['id_user'])) {
            throw new InvalidArgumentException("Missing required field: id_user");
        }
        if (empty($data['id_troop'])) {
            throw new InvalidArgumentException("Missing required field: id_troop");
        }
    }
}