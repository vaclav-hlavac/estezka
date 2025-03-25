<?php

namespace App\Models;

use App\Repository\RefreshTokenRepository;
use DateMalformedStringException;
use DateTime;
use Random\RandomException;

class RefreshToken extends BaseModel
{
    public ?int $id_refresh_token;
    public int $id_user;
    public string $token;
    public $expires_at;
    public $created_at;

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function __construct(array $data)
    {
        $notNullArguments = ['id_user'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->token = bin2hex(random_bytes(32));
        $this->expires_at = (new DateTime())->modify('+120 days')->format('Y-m-d H:i:s');

        $this->id_refresh_token = $data['id_refresh_token'] ?? null;
        $this->id_user = $data['id_user'];
        $this->created_at = $data['created_at'] ?? null;
    }

    public function getId()
    {
        return $this->id_refresh_token;
    }

    /**
     * @throws RandomException
     */
    public function generateNewToken(): void
    {
        $this->token = bin2hex(random_bytes(32));
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_refresh_token' => $this->id_refresh_token,
            'id_user' => $this->id_user,
            'token' => $this->token,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
        ];
    }
}