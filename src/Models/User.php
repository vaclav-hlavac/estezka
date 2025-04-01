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
    public ?string $gang_name;
    public ?String $gang_color;
    public ?int $completed_tasks;
    public ?int $total_tasks;
    public ?int $activePathLevel;

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
        $this->completed_tasks = $data['completed_tasks'] ?? null;
        $this->total_tasks = $data['total_tasks'] ?? null;
        $this->activePathLevel = $data['active_path_level'] ?? null;
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
        if ($this->gang_name != null) { $data['patrol_name'] = $this->gang_name;}
        if ($this->gang_color != null) { $data['color'] = $this->gang_color;}
        if ($this->completed_tasks != null) { $data['completed_tasks'] = $this->completed_tasks;}
        if ($this->total_tasks != null) { $data['total_tasks'] = $this->total_tasks;}
        if ($this->activePathLevel != null) { $data['active_path_level'] = $this->activePathLevel;}

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
        if ($this->gang_name != null) { $data['patrol_name'] = $this->gang_name;}
        if ($this->gang_color != null) { $data['color'] = $this->gang_color;}
        if ($this->completed_tasks != null) { $data['completed_tasks'] = $this->completed_tasks;}
        if ($this->total_tasks != null) { $data['total_tasks'] = $this->total_tasks;}
        if ($this->activePathLevel != null) { $data['active_path_level'] = $this->activePathLevel;}

        return $data;
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