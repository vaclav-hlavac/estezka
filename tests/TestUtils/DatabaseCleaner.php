<?php

declare(strict_types=1);

namespace Tests\TestUtils;

use PDO;

final class DatabaseCleaner
{
    public static function cleanAll(PDO $pdo): void
    {
        // Disable FK checks temporarily
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

        $tables = [
            'task_progress',
            'task',
            'patrol_member',
            'patrol_leader',
            'patrol',
            'troop_leader',
            'troop',
            'notification',
            'comment',
            'user',
            'refresh_tokens',
        ];

        foreach ($tables as $table) {
            $pdo->exec("DELETE FROM {$table}");
            $pdo->exec("ALTER TABLE {$table} AUTO_INCREMENT = 1");
        }

        // Re-enable FK checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

    public static function cleanUserDataOnly(PDO $pdo): void
    {
        $pdo->exec("DELETE FROM user");
        $pdo->exec("ALTER TABLE user AUTO_INCREMENT = 1");
    }
}