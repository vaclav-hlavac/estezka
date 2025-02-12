<?php
require '../vendor/autoload.php'; // Autoload Slim a všechny závislosti

use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

// Vytvoření instance Slim aplikace
$app = AppFactory::create();

// Vytvoření ServerRequest objektu pomocí Slim PSR-7
$request = ServerRequestFactory::createFromGlobals();

// Vytvoření Response objektu pomocí Slim PSR-7
$response = new ResponseFactory();

// Načítání rout
(require __DIR__ . '/../src/Routes/api.php')($app);

// Spuštění aplikace
$app->run($request, $response);