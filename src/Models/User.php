<?php

namespace App\Models;
use InvalidArgumentException;
use JsonSerializable;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

class User implements JsonSerializable{
    public $userId;
    public $nickname;
    public $name;
    public $surname;
    public $password;
    public $email;
    public $notifications_enabled;

    public function __construct(array $data) {
        $this->requiredArgumentsControl();

        $this->userId = $data['id_user'];
        $this->nickname = $data['nickname'];
        $this->name = $data['name'];
        $this->surname = $data['surname'];
        $this->password = $data['password'];
        $this->email = $data['email'];
        $this->notifications_enabled = $data['notifications_enabled'] ?? true;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->userId,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled
        ];
    }

    public function password_verify($password): bool{
        return password_verify($password, $this->password);
    }

    public function getPayload(): array {
        return [
            'id_user' => $this->userId,
            'email' => $this->email,
            'exp' => time() + 3600 // Token expires in 1 hour
        ];
    }

    private function requiredArgumentsControl(): void
    {
        if (empty($data['id_user'])) {
            throw new InvalidArgumentException("Missing required field: id_user");
        }
        if (empty($data['nickname'])) {
            throw new InvalidArgumentException("Missing required field: nickname");
        }
        if (empty($data['name'])) {
            throw new InvalidArgumentException("Missing required field: name");
        }
        if (empty($data['surname'])) {
            throw new InvalidArgumentException("Missing required field: surname");
        }
        if (empty($data['password'])) {
            throw new InvalidArgumentException("Missing required field: password");
        }
        if (empty($data['email'])) {
            throw new InvalidArgumentException("Missing required field: email");
        }
    }
}