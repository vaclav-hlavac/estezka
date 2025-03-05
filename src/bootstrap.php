<?php

use App\Config\Database;
use App\Middleware\ErrorHandlerMiddleware;
use App\Repository\CommentRepository;
use App\Repository\GangRepository;
use App\Repository\TaskProgressRepository;
use App\Repository\TaskRepository;
use App\Repository\TroopRepository;
use App\Repository\UserRepository;
use App\Services\AuthService;
use DI\Container;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Services\AccessService;

require __DIR__ . '/../vendor/autoload.php';

// LOAD .ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();



// **** DEPENDENCY INJECTION *****
$container = new Container();

// PDO
$container->set(PDO::class, function (ContainerInterface $c) {
    return Database::connect();
});


// REPOSITORIES
$container->set(CommentRepository::class, fn(ContainerInterface $c) => new CommentRepository($c->get(PDO::class)));
$container->set(GangRepository::class, fn(ContainerInterface $c) => new GangRepository($c->get(PDO::class)));
$container->set(TaskProgressRepository::class, fn(ContainerInterface $c) => new TaskProgressRepository($c->get(PDO::class)));
$container->set(TaskRepository::class, fn(ContainerInterface $c) => new TaskRepository($c->get(PDO::class)));
$container->set(TroopRepository::class, fn(ContainerInterface $c) => new TroopRepository($c->get(PDO::class)));
$container->set(UserRepository::class, fn(ContainerInterface $c) => new UserRepository($c->get(PDO::class)));


// SERVICES
$container->set(AccessService::class, fn(ContainerInterface $c) => new AccessService(
    $c->get(TroopRepository::class),
    $c->get(GangRepository::class)
));

$container->set(AuthService::class, fn(ContainerInterface $c) => new AuthService());


// LOGGER
$container->set(LoggerInterface::class, function () {
    $logger = new Logger('app');

    // Logování do konzole (stdout)
    $logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));

    return $logger;
});

// ERROR HANDLER
$container->set(ErrorHandlerMiddleware::class, fn(ContainerInterface $c) => new ErrorHandlerMiddleware($c->get(LoggerInterface::class)));

return $container;

