<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
require_once __DIR__ . '/../../../vendor/autoload.php';


class TroopLeader extends BaseModel
{
    protected ?int $id_troop_leader;
    public int $id_user;
    public int $id_troop;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_troop'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_troop_leader = $data['id_troop_leader'] ?? null;
        $this->id_user = $data['id_user'];
        $this->id_troop = $data['id_troop'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id_user,
            'id_troop' => $this->id_troop
        ];
    }

    public function getId()
    {
        return $this->id_troop_leader;
    }
}