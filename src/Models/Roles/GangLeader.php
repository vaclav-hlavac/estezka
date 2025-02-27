<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
use InvalidArgumentException;
use JsonSerializable;

require_once __DIR__ . '/../../../vendor/autoload.php';


class GangLeader implements JsonSerializable
{
    public $userId;
    public $gangId;

    public function __construct(array $data) {
        $this->requiredArgumentsControl();

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

    private function requiredArgumentsControl(): void
    {
        if (empty($data['id_user'])) {
            throw new InvalidArgumentException("Missing required field: id_user");
        }
        if (empty($data['id_gang'])) {
            throw new InvalidArgumentException("Missing required field: id_gang");
        }
    }


}