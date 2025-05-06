<?php

namespace App\Models;

use Random\RandomException;

/**
 *
 * Gang model representing a patrol within a troop.
 *
 * Contains identifying information including name, troop ID, color, and invite code.
 * Invite code is auto-generated if not provided.
 */
class Gang extends BaseModel
{
    public string $name;
    public int $id_troop;
    public ?int $id_patrol;
    public ?string $color;
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
        $this->id_patrol = $data['id_patrol'] ?? null;
        $this->invite_code = $data['invite_code'] ?? bin2hex(random_bytes(5));
        $this->color = $data['color'] ?? null;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'id_troop' => $this->id_troop,
            'name' => $this->name,
            'invite_code' => $this->invite_code,
        ];

        if ($this->id_patrol != null) { $data['id_patrol'] = $this->id_patrol;}
        if ($this->color != null) { $data['color'] = $this->color;}

        return $data;
    }

    public function getId()
    {
        return $this->id_patrol;
    }

    /**
     * Regenerates the invite code with a new 64-character secure random string.
     *
     * @return void
     * @throws \Random\RandomException If random generation fails.
     */
    public function refreshInviteCode(){
        $this->invite_code = bin2hex(random_bytes(32));
    }

    public function toDatabase()
    {
        return $this->jsonSerialize();
    }
}