<?php

namespace config;
require_once __DIR__ . '/../autoloader.php';

use PDO;

class Database {
    private static $pdo;

    public static function connect(): PDO
    {
        if (self::$pdo == null) {
            self::$pdo = new PDO('mysql:host=localhost;dbname=e_stezka', 'root', '3st3zkaSQL!');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}