<?php

namespace App\Models\Roles;

use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';


class GangLeader extends BaseModel
{
    protected $id_gang_leader;
    public $id_user;
    public $id_gang;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_gang'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_gang_leader = $data['id_gang_leader'];
        $this->id_user = $data['id_user'];
        $this->id_gang = $data['id_gang'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id_user,
            'id_gang' => $this->id_gang
        ];
    }


    public function getId()
    {
        return $this->id_gang_leader;
    }
}