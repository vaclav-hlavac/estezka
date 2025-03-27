<?php

namespace App\Models\Roles;
use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

class GangMember extends BaseModel
{
    public int $id_gang;
    public int $id_user;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_gang'];
        $this->requiredArgumentsControl($data, $notNullArguments);

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
        return $this->id_user;
    }
}