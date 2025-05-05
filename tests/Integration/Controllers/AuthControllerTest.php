<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Controllers\AuthController;
use App\Models\RefreshToken;
use App\Repository\RefreshTokenRepository;
use App\Repository\Roles\TroopLeaderRepository;
use App\Repository\UserRepository;
use App\Services\AuthService;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestUtils\DatabaseCleaner;

/**
 * Integration tests for AuthController.
 * These tests directly invoke controller methods without going through Slim routing.
 * The focus is on verifying controller logic and its side effects in the database.
 */
final class AuthControllerTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $userRepository;
    private AuthController $controller;

    /**
     * Prepares the test environment:
     * - Loads DI container and retrieves services.
     * - Creates an instance of AuthController.
     * - Cleans the test database before each test.
     */
    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->pdo = $container->get(PDO::class);
        $this->userRepository = $container->get(UserRepository::class);
        $authService = $container->get(AuthService::class);

        $this->controller = new AuthController($this->pdo, $authService);

        DatabaseCleaner::cleanAll($this->pdo);
    }

    /**
     * Tests user registration with a new troop.
     * This test directly invokes the controller's register() method without going through API routes.
     * It verifies that:
     * - The user is created in the database.
     * - The troop is created with the correct name.
     * - The user is assigned the role of troop leader.
     */
    public function testRegisterDirectly(): void
    {
        $email = 'newbie@example.com';

        $request = $this->createJsonRequest([
            'nickname' => 'newbie',
            'name' => 'New',
            'surname' => 'User',
            'password' => 'secret',
            'email' => $email,
            'new_troop' => ['name' => 'Direct Troop']
        ]);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->register($request, $response, []);
        $this->assertSame(201, $result->getStatusCode());

        // Verify user creation
        $user = $this->userRepository->findByEmail($email);
        $this->assertNotNull($user);

        // Verify role assignment
        $troopLeaderRepo = new TroopLeaderRepository($this->pdo);
        $leaders = $troopLeaderRepo->findAll();
        $this->assertCount(1, $leaders);
        $this->assertSame($user->getId(), $leaders[0]->id_user);

        // Verify troop creation
        $stmt = $this->pdo->query("SELECT name FROM troop");
        $this->assertSame('Direct Troop', $stmt->fetchColumn());
    }

    /**
     * Tests user login and refresh token creation.
     * The test:
     * - Inserts a test user into the database.
     * - Calls the controller's login() method.
     * - Asserts successful login (200 OK).
     * - Verifies that a refresh token was created in the database.
     */
    public function testLoginDirectly(): void
    {
        $this->userRepository->insert([
            'nickname' => 'tester',
            'name' => 'Test',
            'surname' => 'User',
            'password' => 'pass123', // plain password, repository will hash it
            'email' => 'tester@example.com',
            'notifications_enabled' => true
        ]);

        $request = $this->createJsonRequest([
            'email' => 'tester@example.com',
            'password' => 'pass123'
        ]);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->login($request, $response, []);
        $this->assertSame(200, $result->getStatusCode());

        // Verify that a refresh token was saved in the database
        $repo = new RefreshTokenRepository($this->pdo);
        $tokens = $repo->findAll();

        $this->assertCount(1, $tokens);
        $this->assertInstanceOf(RefreshToken::class, $tokens[0]);
    }

    /**
     * Helper method to create a JSON POST request.
     * Used for mocking HTTP requests passed to controller methods.
     */
    private function createJsonRequest(array $data): \Psr\Http\Message\ServerRequestInterface
    {
        $requestFactory = new ServerRequestFactory();
        $streamFactory = new StreamFactory();

        $stream = $streamFactory->createStream(json_encode($data));

        return $requestFactory
            ->createServerRequest('POST', '/')
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json');
    }
}