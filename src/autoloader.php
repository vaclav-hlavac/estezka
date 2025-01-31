<?php

spl_autoload_register(function ($class) {
    // Předpokládáme, že třídy jsou ve struktuře namespace odpovídající složkám
    // Například třída config\Database bude mít soubor src/config/Database.php
    $baseDir = __DIR__ . '/'; // nebo jiný základní adresář pro třídy
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});