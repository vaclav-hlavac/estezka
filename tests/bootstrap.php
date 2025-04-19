<?php

use Dotenv\Dotenv;
use App\Config\Database;
use Psr\Container\ContainerInterface;
use PDO;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
$dotenv->load();

$container = require __DIR__ . '/../src/bootstrap.php';

$container->set(PDO::class, function (ContainerInterface $c) {
    return Database::connect();
});

return $container;