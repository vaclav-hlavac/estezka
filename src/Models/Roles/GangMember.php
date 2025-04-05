<?php

namespace App\Models\Roles;

use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

class GangMember extends BaseModel
{
    public int $id_gang;
    public int $id_user;

    public ?string $nickname;
    public ?string $avatar_url;
    public ?string $gang_name;
    public ?string $gang_color;
    public ?int $id_troop;
    public ?string $troop_name;
    public ?int $completed_tasks;
    public ?int $total_tasks;
    public ?int $active_path_level;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_patrol'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_user = $data['id_user'];
        $this->id_gang = $data['id_patrol'];

        // Optional fields
        $this->nickname = $data['nickname'] ?? null;
        $this->avatar_url = $data['avatar_url'] ?? null;
        $this->gang_name = $data['patrol_name'] ?? null;
        $this->gang_color = $data['patrol_color'] ?? null;
        $this->id_troop = $data['id_troop'] ?? null;
        $this->troop_name = $data['troop_name'] ?? null;
        $this->completed_tasks = $data['completed_tasks'] ?? null;
        $this->total_tasks = $data['total_tasks'] ?? null;
        $this->active_path_level = $data['active_path_level'] ?? null;
    }

    /**
     * Full JSON serialization for API output
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id_user,
            'id_patrol' => $this->id_gang,
            'nickname' => $this->nickname,
            'avatar_url' => $this->avatar_url,
            'patrol_name' => $this->gang_name,
            'patrol_color' => $this->gang_color,
            'id_troop' => $this->id_troop,
            'troop_name' => $this->troop_name,
            'completed_tasks' => $this->completed_tasks,
            'total_tasks' => $this->total_tasks,
            'active_path_level' => $this->active_path_level,
        ];
    }

    /**
     * Returns only the required fields for database insert/update.
     *
     * @return array
     */
    public function toDatabase(): array
    {
        return [
            'id_user' => $this->id_user,
            'id_patrol' => $this->id_gang,
        ];
    }

    public function getId()
    {
        return $this->id_user;
    }
}