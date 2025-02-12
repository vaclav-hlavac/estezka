<?php

namespace App\Models;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

abstract class BaseModel
{
    protected $id;
    protected static $tableName;
    public function __construct($id = null) {
        $this->id = $id;
    }

    public static function find($id, $pdo) {
        $tableName = static::$tableName;
        $id_name = "id_".$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE $id_name = ?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function all($pdo){
        $tableName = static::$tableName;
        $stmt = $pdo->query("SELECT * FROM $tableName");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    abstract public function save($pdo);

    public function delete($pdo) {
        $tableName = static::$tableName;
        $id_name = "id_".$tableName;
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE $id_name = ?");
        $stmt->execute([$this->id]);
    }


    public function getId()
    {
        return $this->id;
    }


}