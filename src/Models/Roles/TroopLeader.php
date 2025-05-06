<?php

namespace App\Models\Roles;

use App\Models\BaseModel;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * TroopLeader model representing a user's role as a leader of a troop.
 *
 * Includes troop metadata for enriched API responses.
 */
class TroopLeader extends BaseModel
{
    protected ?int $id_troop_leader;
    public int $id_user;
    public int $id_troop;

    public ?string $troop_name;

    public function __construct(array $data) {
        $notNullArguments = ['id_user', 'id_troop'];
        $this->requiredArgumentsControl($data, $notNullArguments);

        $this->id_troop_leader = $data['id_troop_leader'] ?? null;
        $this->id_user = $data['id_user'];
        $this->id_troop = $data['id_troop'];

        // Optional fields
        $this->troop_name = $data['troop_name'] ?? null;
    }

    /**
     * Full JSON serialization for API output
     */
    public function jsonSerialize(): mixed
    {
        return [
            'id_troop_leader' => $this->id_troop_leader,
            'id_user' => $this->id_user,
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
            'id_troop' => $this->id_troop,
        ];
    }

    public function getId()
    {
        return $this->id_troop_leader;
    }
}