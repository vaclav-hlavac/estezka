<?php

namespace App\Models;

use Random\RandomException;

class Gang extends BaseModel
{
    public string $name;
    public int $id_troop;
    public ?int $id_gang;
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
        $this->id_gang = $data['id_patrol'] ?? null;
        $this->invite_code = bin2hex(random_bytes(5));
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'id_troop' => $this->id_troop,
            'name' => $this->name,
            'invite_code' => $this->invite_code,
        ];

        if ($this->id_gang != null) { $data['id_patrol'] = $this->id_gang;}

        return $data;
    }

    public function getId()
    {
        return $this->id_gang;
    }

    public function refreshInviteCode(){
        $this->invite_code = bin2hex(random_bytes(32));
    }
}