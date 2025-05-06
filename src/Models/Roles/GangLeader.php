<?php

namespace App\Models\Roles;

use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * GangLeader model representing a user's role as a leader of a patrol (gang).
 *
 * Includes patrol and troop metadata for use in enriched API responses.
 */
class GangLeader extends BaseModel
{
    protected ?int $id_gang_leader;
    public int $id_user;
    public int $id_patrol;

    public ?string $gang_name;
    public ?string $gang_color;
    public ?int $id_troop;
    public ?string $troop_name;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_patrol'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_gang_leader = $data['id_patrol_leader'] ?? null;
        $this->id_user = $data['id_user'];
        $this->id_patrol = $data['id_patrol'];

        // Optional fields
        $this->gang_name = $data['patrol_name'] ?? null;
        $this->gang_color = $data['patrol_color'] ?? null;
        $this->id_troop = $data['id_troop'] ?? null;
        $this->troop_name = $data['troop_name'] ?? null;
    }

    /**
     * Full JSON serialization for API output
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id_patrol_leader' => $this->id_gang_leader,
            'id_user' => $this->id_user,
            'id_patrol' => $this->id_patrol,
            'patrol_name' => $this->gang_name,
            'patrol_color' => $this->gang_color,
            'id_troop' => $this->id_troop,
            'troop_name' => $this->troop_name,
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
            'id_patrol' => $this->id_patrol,
        ];
    }

    public function getId()
    {
        return $this->id_gang_leader;
    }
}