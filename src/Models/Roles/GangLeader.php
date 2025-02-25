<?php

namespace App\Models\Roles;

use App\Models\BaseModel;
use InvalidArgumentException;

require_once __DIR__ . '/../../../vendor/autoload.php';


class GangLeader extends BaseModel
{
    static protected $tableName = "gang_leader";
    protected $userId;
    protected $gangId;

    public function __construct($pdo, array $data) {

        parent::__construct($pdo, $data['id'] ?? null);

        $this->userId = $data['id_user'] ?? null;
        $this->gangId = $data['id_gang'] ?? null;
    }

    /**
     * Inserts new GangLeader role into DB. Can not update existing instance. (It has to be deleted and created a new role.)
     * @return void
     * @throws InvalidArgumentException if user id or gang id is not set
     */
    public function save()
    {
        if(!isset($this->userId)){
            throw new InvalidArgumentException("User id is not set");
        }
        if(!isset($this->gangId)){
            throw new InvalidArgumentException("Gang id is not set");
        }
        $tableName = static::$tableName;

        // Insertion
        $stmt = $this->pdo->prepare("INSERT INTO $tableName (id_user, id_gang) VALUES (?, ?)");
        $stmt->execute([$this->userId, $this->gangId]);
        $this->id = $this->pdo->lastInsertId();
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->userId,
            'id_gang' => $this->gangId
        ];
    }

    /**
     * Find all roles of a user by user's ID.
     * @param $pdo
     * @param $userId int ID of user, whose roles are searched
     * @return array Array of GangLeader roles of user.
     */
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

    public static function findByUserAndGangId($pdo, $userId, $gangId): GangLeader|null {
        $tableName = static::$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id_user = ? AND id_gang = ?");
        $stmt->execute([$userId, $gangId]);
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

    public function getGangId(): mixed
    {
        return $this->gangId;
    }


}