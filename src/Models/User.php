<?php

namespace App\Models;

use InvalidArgumentException;

require_once __DIR__ . '/../../vendor/autoload.php';

class User extends BaseModel {
    public $id_user;
    public $nickname;
    public $name;
    public $surname;
    public $password;
    public $login_name;
    public $email;
    public $notifications_enabled;

    public function __construct(array $data) {
        $notNullArguments = [];
        $notEmptyArguments = ['nickname', 'name', 'surname', 'login_name', 'password', 'email'];
        $this->requiredArgumentsControl($data, $notNullArguments, $notEmptyArguments);

        $this->id_user = $data['id_user'] ?? null;
        $this->nickname = $data['nickname'];
        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->password = $data['password'];
        $this->login_name = $data['login_name'];
        $this->email = $data['email'];
        $this->notifications_enabled = $data['notifications_enabled'] ?? true;
    }

    public function toArray(): array {
        return [
            'id_user' => $this->id_user,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'login_name' => $this->login_name,
            'password' => $this->password,
            'notifications_enabled' => $this->notifications_enabled
        ];
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id_user,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'login_name' => $this->login_name,
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled
        ];
    }

    public function getPayload(): array {
        return [
            'id_user' => $this->id_user,
            'login_name' => $this->login_name,
            'email' => $this->email,
            'exp' => time() + 3600 // Token expires in 1 hour
        ];
    }

    public function getId()
    {
        return $this->id_user;
    }
}