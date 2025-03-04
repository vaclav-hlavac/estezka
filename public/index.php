<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../src/bootstrap.php';


// Vytvoření instance Slim aplikace
$app = AppFactory::create();


// Načítání rout
(require __DIR__ . '/../src/Routes/api.php')($app);

// Spuštění aplikace
$app->run();


