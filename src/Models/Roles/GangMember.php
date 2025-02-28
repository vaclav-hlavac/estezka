<?php

namespace App\Models\Roles;
use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

class GangMember extends BaseModel
{
    protected $id_gang_member;
    public $gangId;
    public $userId;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_gang'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_gang_member = $data['id_gang_member'];
        $this->userId = $data['id_user'];
        $this->gangId = $data['id_gang'];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->userId,
            'id_gang' => $this->gangId
        ];
    }


    public function getId()
    {
        return $this->id_gang_member;
    }
}