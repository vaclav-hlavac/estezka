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
    public function __construct($pdo, array $data)
    {
        if (isset($data['id_gang'])) {
            $data['id'] = $data['id_gang'];
        }
        if (isset($data['id_troop'])) {
            $data['troopId'] = $data['id_troop'];
        }

        parent::__construct($pdo, $data['id'] ?? null);
        $this->name = $data['name'] ?? null;
        $this->troopId = $data['troopId'] ?? null;
    }

    public function save()
    {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Aktualizace existující družiny
            $stmt = $this->pdo->prepare("UPDATE $tableName SET name = ?, id_troop = ? WHERE id_gang = ?");
            $stmt->execute([$this->name, $this->troopId, $this->id]);
        } else {
            // Vložení nové družiny
            $stmt = $this->pdo->prepare("INSERT INTO $tableName (name, id_troop) VALUES (?, ?)");
            $stmt->execute([$this->name, $this->troopId]);
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public static function getAllByTroopId($pdo, $troopId)
    {
        $tableName = static::$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE id_troop = ?");
        $stmt->execute([$troopId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new static($pdo, $row);
        }
        return $results;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id_gang' => $this->id,
            'id_troop' => $this->troopId,
            'name' => $this->name
        ];
    }
}