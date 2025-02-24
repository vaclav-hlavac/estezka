<?php

namespace App\Models;
use JsonSerializable;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Basic ORM from models to my DB.
 * Uses static $tablename from extended class
 */
abstract class BaseModel implements JsonSerializable
{
    protected $id;
    protected $pdo;
    /**
     * @var $tableName string containing name of table, that the extended class uses
     * should be always renamed by extended class
     */
    protected static $tableName;
    public function __construct($pdo, $id) {
        $this->pdo = $pdo;
        $this->id = $id;
    }

    public static function find($pdo, $id) {
        $tableName = static::$tableName;
        $id_name = "id_".$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE $id_name = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        return new static($pdo, $data);
    }

    public static function all($pdo): array {
        $tableName = static::$tableName;
        $stmt = $pdo->query("SELECT * FROM $tableName");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new static($pdo, $row);
        }
        return $results;
    }

    abstract public function save();

    public function delete(): bool {
        $tableName = static::$tableName;
        $id_name = "id_".$tableName;

        if(BaseModel::find($this->id, $this->pdo) == null){
            return false;
        }
        $stmt = $this->pdo->prepare("DELETE FROM $tableName WHERE $id_name = ?");
        $stmt->execute([$this->id]);
        return true;
    }


    public function getId()
    {
        return $this->id;
    }


}