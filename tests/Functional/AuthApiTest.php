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
final class AuthApiTest extends TestCase
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

        // Insert a test user into the database
        $this->seedUser();
    }

    /**
     * Inserts a test user used for login and refresh token tests.
     */
    private function seedUser(): void
    {
        $repo = new UserRepository($this->pdo);
        $repo->insert([
            'nickname' => 'api',
            'name' => 'API',
            'surname' => 'Tester',
            'password' => 'secret123', // plain text, hashed in repository
            'email' => 'api@example.com',
            'notifications_enabled' => true
        ]);
    }

    /**
     * Tests successful login with valid credentials.
     * Verifies that the response contains access and refresh tokens.
     */
    public function testLoginSuccess(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/auth/login');
        $body = (new StreamFactory())->createStream(json_encode([
            'email' => 'api@example.com',
            'password' => 'secret123'
        ]));

        $request = $request->withBody($body)->withHeader('Content-Type', 'application/json');

        $response = $this->app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertArrayHasKey('refresh_token', $data);
    }

    /**
     * Tests registration with a new troop.
     * Verifies that a user is created and the response contains their email.
     */
    public function testRegisterWithNewTroop(): void
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', '/auth/register');
        $body = (new StreamFactory())->createStream(json_encode([
            'nickname' => 'registrator',
            'name' => 'Register',
            'surname' => 'User',
            'email' => 'register@example.com',
            'password' => 'register123',
            'notifications_enabled' => true,
            'new_troop' => ['name' => 'Test Troop']
        ]));

        $request = $request
            ->withBody($body)
            ->withHeader('Content-Type', 'application/json');

        $response = $this->app->handle($request);

        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('email', $data);
        $this->assertSame('register@example.com', $data['email']);
    }

    /**
     * Tests refreshing an access token using a valid refresh token.
     * First logs in to get a refresh token, then uses it to obtain a new access token.
     */
    public function testRefreshTokenReturnsNewAccessToken(): void
    {
        // Step 1: Login to get refresh token
        $loginReq = (new ServerRequestFactory())->createServerRequest('POST', '/auth/login');
        $loginBody = (new StreamFactory())->createStream(json_encode([
            'email' => 'api@example.com',
            'password' => 'secret123'
        ]));
        $loginReq = $loginReq->withBody($loginBody)->withHeader('Content-Type', 'application/json');

        $loginResponse = $this->app->handle($loginReq);
        $this->assertSame(200, $loginResponse->getStatusCode());

        $loginData = json_decode((string) $loginResponse->getBody(), true);
        $refreshToken = $loginData['refresh_token'];

        // Step 2: Refresh access token
        $refreshReq = (new ServerRequestFactory())->createServerRequest('POST', '/auth/refresh');
        $refreshBody = (new StreamFactory())->createStream(json_encode([
            'refresh_token' => $refreshToken
        ]));
        $refreshReq = $refreshReq->withBody($refreshBody)->withHeader('Content-Type', 'application/json');

        $refreshResponse = $this->app->handle($refreshReq);
        $this->assertSame(200, $refreshResponse->getStatusCode());

        $refreshData = json_decode((string) $refreshResponse->getBody(), true);
        $this->assertArrayHasKey('access_token', $refreshData);
        $this->assertArrayHasKey('user_with_roles', $refreshData);
    }
}