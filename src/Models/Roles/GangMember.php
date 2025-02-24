<?php

namespace App\Models\Roles;
use App\Models\BaseModel;
use InvalidArgumentException;

require_once __DIR__ . '/../../../vendor/autoload.php';

class GangMember extends BaseModel
{
    static protected $tableName = "gang_member";
    static private $id_name = "id_user";

    protected $gangId;

    public function __construct($pdo, array $data) {
        if (isset($data['id_user'])) {
            $data['id'] = $data['id_user'];
        }

        parent::__construct($pdo, $data['id'] ?? null);
        $this->gangId = $data['id_gang'] ?? null;
    }


    public function save()
    {
        if(!isset($this->id)){
            throw new InvalidArgumentException("User id is not set");
        }
        if(!isset($this->gangId)){
            throw new InvalidArgumentException("Gang id is not set");
        }
        $tableName = static::$tableName;

        if (self::find($this->pdo, $this->id) != null) {
            // Actualization of existing user
            $stmt = $this->pdo->prepare("UPDATE $tableName SET id_gang = ? WHERE id_user = ?");
            $stmt->execute([$this->gangId]);
        } else {
            // Insertion of new user
            $stmt = $this->pdo->prepare("INSERT INTO $tableName (id_user, id_gang) VALUES (?, ?)");
            $stmt->execute([$this->id, $this->gangId]);
        }
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id,
            'id_gang' => $this->gangId
        ];
    }

    public static function find($pdo, $id) {
        $tableName = static::$tableName;
        $id_name = static::$id_name;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE $id_name = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        return new static($pdo, $data);
    }

    public function getUserId(): mixed
    {
        return $this->id;
    }

    public function getGangId(): mixed
    {
        return $this->gangId;
    }
}