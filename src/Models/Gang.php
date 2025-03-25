<?php

namespace App\Models;

use Random\RandomException;

class Gang extends BaseModel
{
    public $name;
    public $id_troop;
    public $id_gang;
    public string $invite_code;

    /**
     * @param array $data
     * @throws RandomException
     */
    public function __construct(array $data)
    {
        $notNullArguments = ['id_troop'];
        $notEmptyArguments = ['name'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->name = $data['name'];
        $this->id_troop = $data['id_troop'];
        $this->id_gang = $data['id_gang'] ?? null;
        $this->invite_code = bin2hex(random_bytes(32));
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_gang' => $this->id_gang,
            'id_troop' => $this->id_troop,
            'name' => $this->name,
            'invite_code' => $this->invite_code,
        ];
    }

    public function getId()
    {
        return $this->id_gang;
    }

    public function refreshInviteCode(){
        $this->invite_code = bin2hex(random_bytes(32));
    }
}