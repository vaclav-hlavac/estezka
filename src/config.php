<?php

function loadEnv($file)
{
    $env = [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, ';') === 0) {
            continue;  // Skip comments
        }

        $parts = explode('=', $line, 2);

        if (count($parts) === 2) {
            $env[trim($parts[0])] = trim($parts[1]);
        }
    }

    return $env;
}

// Načteme hodnoty z .env souboru
$env = loadEnv(__DIR__ . '/../.env');  // Cesta k souboru .env

define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
?>