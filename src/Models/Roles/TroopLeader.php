<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
require_once __DIR__ . '/../../../vendor/autoload.php';


class TroopLeader extends BaseModel
{
    protected $id_troop_leader;
    public $userId;
    public $troopId;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_troop'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_troop_leader = $data['id_troop_leader'];
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

    public function getId()
    {
        return $this->id_troop_leader;
    }
}