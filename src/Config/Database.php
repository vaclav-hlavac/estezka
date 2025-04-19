<?php

namespace App\Config;
require_once __DIR__ . '/../../vendor/autoload.php';

use PDO;

class Database {
    private static $pdo;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}", $_ENV['DB_USER'], $_ENV['DB_PASSWORD']
            );
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return self::$pdo;

/*        if (self::$pdo == null) {
            self::$pdo = new PDO(
                'mysql:host=localhost;dbname=e_stezka', 'root', '3st3zkaSQL!');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;*/
    }
}