<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\Repository\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestUtils\DatabaseCleaner;

/**
 * Functional API tests for authentication endpoints.
 * Tests cover login, registration, and token refresh.
 */
final class NoAuthorizationTest extends TestCase
{
    private App $app;
    private PDO $pdo;



    /**
     * Prepares the Slim app, cleans the database and seeds a user before each test.
     */
    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../tests/bootstrap.php';

        $this->pdo = $container->get(PDO::class);

        // Reset the database before each test
        DatabaseCleaner::cleanAll($this->pdo);

        // Create a new Slim app instance and register routes
        $this->app = AppFactory::create();
        (require __DIR__ . '/../../src/routes/api.php')($this->app);
    }

    public function testUnauthorizedAccessReturns401(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/troops/1');
        $response = $this->app->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }



}