<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
use InvalidArgumentException;
use PDO;
require_once __DIR__ . '/../../../vendor/autoload.php';


class TroopLeader extends BaseModel
{
    static protected $tableName = "troop_leader";
    protected $userId;
    protected $troopId;

    public function __construct($pdo, array $data) {

        parent::__construct($pdo, $data['id'] ?? null);

        $this->userId = $data['id_user'] ?? null;
        $this->troopId = $data['id_troop'] ?? null;
    }


    /**
     * Inserts new TroopLeader role into DB. Can not update existing instance. (It has to be deleted and created a new role.)
     * @return void
     * @throws InvalidArgumentException if user id or troop id is not set
     */
    public function save()
    {
        if(!isset($this->userId)){
            throw new InvalidArgumentException("User id is not set");
        }
        if(!isset($this->troopId)){
            throw new InvalidArgumentException("Troop id is not set");
        }
        $tableName = static::$tableName;

        // Insertion
        $stmt = $this->pdo->prepare("INSERT INTO $tableName (id_user, id_troop) VALUES (?, ?)");
        $stmt->execute([$this->userId, $this->troopId]);
        $this->id = $this->pdo->lastInsertId();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->userId,
            'id_troop' => $this->troopId
        ];
    }

    public static function findAllByUserId($pdo, $userId): array
    {
        $tableName = static::$tableName;

        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id_user = ?");
        $stmt->execute([$userId]);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new static($pdo, $row);
        }
        return $results;
    }

    public static function findByUserAndTroopId($pdo, $userId, $troopId): TroopLeader|null {
        $tableName = static::$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id_user = ? AND id_troop = ?");
        $stmt->execute([$userId, $troopId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }
        return new static($pdo, $data);
    }

    public function getUserId(): mixed
    {
        return $this->userId;
    }

    public function getTroopId(): mixed
    {
        return $this->troopId;
    }



}