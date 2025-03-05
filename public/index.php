<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require __DIR__ . '/../vendor/autoload.php';

// Použití kontejneru z bootstrap.php
$container = require __DIR__ . '/../src/bootstrap.php';

// Nastavení kontejneru pro Slim
AppFactory::setContainer($container);

// Vytvoření instance Slim aplikace
$app = AppFactory::create();

// **Přidání error middleware** (až po vytvoření `$app`)
$errorMiddleware = new ErrorMiddleware(
    $app->getCallableResolver(),
    $app->getResponseFactory(),
    true, // Display error details
    true, // Log errors
    true  // Log error details
);
$errorMiddleware->setDefaultErrorHandler($container->get(App\Middleware\ErrorHandlerMiddleware::class));
$app->add($errorMiddleware);


// Načítání rout
(require __DIR__ . '/../src/Routes/api.php')($app);

// Spuštění aplikace
$app->run();


