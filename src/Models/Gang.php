<?php

namespace App\Models;


class Gang  extends BaseModel
{
    static $tableName = "gang";
    public $name;
    public $troopId;


    /**
     * @param $name
     * @param $troopId
     * @param $id
     */
    public function __construct($name, $troopId, $id = null)
    {
        parent::__construct($id);
        $this->name = $name;
        $this->troopId = $troopId;
    }

    public function save($pdo)
    {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Aktualizace existující družiny
            $stmt = $pdo->prepare("UPDATE $tableName SET name = ?, id_troop = ? WHERE id_gang = ?");
            $stmt->execute([$this->name, $this->troopId, $this->id]);
        } else {
            // Vložení nové družiny
            $stmt = $pdo->prepare("INSERT INTO $tableName (name, id_troop) VALUES (?, ?)");
            $stmt->execute([$this->name, $this->troopId]);
            $this->id = $pdo->lastInsertId();
        }
    }
}