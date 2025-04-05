<?php

namespace App\Models;

use InvalidArgumentException;
use Symfony\Component\Console\Color;

require_once __DIR__ . '/../../vendor/autoload.php';

class User extends BaseModel {
    public ?int $id_user;
    public string $nickname;
    public string $name;
    public string $surname;
    public string $password;
    public string $email;
    public bool $notifications_enabled;

    public function __construct(array $data) {
        $notNullArguments = [];
        $notEmptyArguments = ['nickname', 'name', 'surname', 'password', 'email'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->id_user = $data['id_user'] ?? null;
        $this->nickname = $data['nickname'];
        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->notifications_enabled = $data['notifications_enabled'] ?? true;
    }

    public function toDatabase(): array {
        $data = [
            'password' => $this->password,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled,
        ];

        if ($this->id_user != null) { $data['id_user'] = $this->id_user;}

        return $data;
    }

    public function jsonSerialize(): mixed
    {
        $data = [
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled,
        ];

        if ($this->id_user != null) { $data['id_user'] = $this->id_user;}

        return $data;
    }

    public function getPayload(): array {
        return [
            'id_user' => $this->id_user,
            'email' => $this->email,
            'exp' => time() + 60000 // Token expires in cca 2 hours
        ];
    }

    public function getId()
    {
        return $this->id_user;
    }
}