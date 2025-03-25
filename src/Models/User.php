<?php

namespace App\Models;

use InvalidArgumentException;
use Symfony\Component\Console\Color;

require_once __DIR__ . '/../../vendor/autoload.php';

class User extends BaseModel {
    public $id_user;
    public $nickname;
    public $name;
    public $surname;
    public $password;
    public $email;
    public $notifications_enabled;
    public string $gang_name;
    public Color $gang_color;
    public int $completed_tasks;
    public int $total_tasks;
    public int $activePathLevel;

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
        $this->gang_name = $data['gang_name'] ?? null;
        $this->gang_color = $data['gang_color'] ?? null;
    }

    public function toDatabase(): array {
        return [
            'id_user' => $this->id_user,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
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
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled,
            'patrol_name' => $this->gang_name,
            'color' => $this->gang_color,
            'completed_tasks' => $this->completed_tasks,
            'total_tasks' => $this->total_tasks,
            'active_path_level' => $this->activePathLevel,
        ];
    }

    public function getPayload(): array {
        return [
            'id_user' => $this->id_user,
            'email' => $this->email,
            'exp' => time() + 3600 // Token expires in 1 hour
        ];
    }

    public function getId()
    {
        return $this->id_user;
    }
}