<?php

namespace App\Models;
use PDO;

require_once __DIR__ . '/../../vendor/autoload.php';

class User extends BaseModel{
    static protected $tableName = "user";
    public $nickname;
    public $name;
    public $surname;
    public $password;
    public $email;
    public $notifications_enabled;

    /**
     * @param $pdo
     * @param array $data associative array with id and name
     */
    public function __construct($pdo, array $data) {
        if (isset($data['id_user'])) {
            $data['id'] = $data['id_user'];
        }
        parent::__construct($pdo, $data['id'] ?? null);
        $this->nickname = $data['nickname'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->surname = $data['surname'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->notifications_enabled = $data['notifications_enabled'] ?? true;
    }

    public static function findAllByNickname($pdo, $nickname): array
    {
        $tableName = static::$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE nickname = ?");
        $stmt->execute([$nickname]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $results[] = new static($pdo, $row);
        }
        return $results;
    }

    public static function findByEmail($pdo, $email): User|null {
        $tableName = static::$tableName;
        $stmt = $pdo->prepare("SELECT * FROM $tableName WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }
        return new static($pdo, $data);
    }

    public function save() {
        $tableName = static::$tableName;
        if (isset($this->id)) {
            // Actualization of existing user
            $stmt = $this->pdo->prepare("UPDATE $tableName SET nickname = ?, name = ?, surname = ?, password = ?, email = ?, notifications_enabled = ? WHERE id_user = ?");
            $stmt->execute([$this->nickname, $this->name, $this->surname, $this->password, $this->email, $this->notifications_enabled, $this->id]);
        } else {
            // Insertion of new user
            $stmt = $this->pdo->prepare("INSERT INTO $tableName (nickname, name, surname, password, email, notifications_enabled) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$this->nickname, $this->name, $this->surname, $this->password, $this->email, $this->notifications_enabled]);
            $this->id = $this->pdo->lastInsertId();
        }
    }


    public function jsonSerialize(): mixed
    {
        return [
            'id_user' => $this->id,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'notifications_enabled' => $this->notifications_enabled
        ];
    }

    public function password_verify($password): bool{
        return password_verify($password, $this->password);
    }

    public function getPayload(): array {
        return [
            'id_user' => $this->id,
            'email' => $this->email,
            'exp' => time() + 3600 // Token expires in 1 hour
        ];
    }

}