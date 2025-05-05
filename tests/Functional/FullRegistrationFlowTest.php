<?php

declare(strict_types=1);

namespace Tests\Functional;

use PDO;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestUtils\DatabaseCleaner;

/**
 * Functional test covering the full flow:
 * - Register troop leader
 * - Login
 * - Create patrol
 * - Register new user with invite code from that patrol
 */
final class FullRegistrationFlowTest extends TestCase
{
    private App $app;
    private PDO $pdo;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../tests/bootstrap.php';
        $this->pdo = $container->get(PDO::class);
        DatabaseCleaner::cleanAll($this->pdo);

        $this->app = AppFactory::create();
        (require __DIR__ . '/../../src/routes/api.php')($this->app);
    }

    public function testUserJoinsPatrolUsingInviteCode(): void
    {
        // 1. Register first user as troop leader
        $response = $this->postJson('/auth/register', [
            'nickname' => 'leader',
            'name' => 'Troop',
            'surname' => 'Leader',
            'email' => 'leader@example.com',
            'password' => 'leader123',
            'new_troop' => ['name' => 'Blue Troop']
        ]);
        $this->assertSame(201, $response->getStatusCode());

        // 2. Login as troop leader
        $response = $this->postJson('/auth/login', [
            'email' => 'leader@example.com',
            'password' => 'leader123'
        ]);
        $data = $this->getJson($response);
        $this->assertArrayHasKey('access_token', $data);
        $token = $data['access_token'];
        $troopId = $data['user_with_roles']['troop_leaders'][0]['id_troop'];

        // 3. Create new patrol under troop
        $response = $this->postJson("/troops/{$troopId}/patrols", [
            'name' => 'Red Patrol',
            'color' => 'red'
        ], $token);
        $this->assertSame(201, $response->getStatusCode());
        $patrolData = $this->getJson($response);
        $inviteCode = $patrolData['invite_code'];
        $expectedPatrolName = $patrolData['name'];
        $expectedPatrolColor = $patrolData['color'];

        // 4. Register second user using patrol invite code
        $response = $this->postJson('/auth/register', [
            'nickname' => 'scout',
            'name' => 'Scout',
            'surname' => 'User',
            'email' => 'scout@example.com',
            'password' => 'scout123',
            'invite_code' => $inviteCode
        ]);
        $this->assertSame(201, $response->getStatusCode());

        $joinedUser = $this->getJson($response);
        $this->assertSame('scout@example.com', $joinedUser['email']);

        // 5. Login as second user and check roles
        $response = $this->postJson('/auth/login', [
            'email' => 'scout@example.com',
            'password' => 'scout123'
        ]);
        $this->assertSame(200, $response->getStatusCode());
        $data = $this->getJson($response);

        $this->assertArrayHasKey('user_with_roles', $data);
        $roles = $data['user_with_roles'];

        // 6. Check patrol_member role
        $this->assertArrayHasKey('patrol_member', $roles);
        $this->assertNotNull($roles['patrol_member']);

        // 7. Check patrol and troop name match
        $this->assertSame($expectedPatrolName, $roles['patrol_member']['patrol_name']);
        $this->assertSame($expectedPatrolColor, $roles['patrol_member']['patrol_color']);
        $this->assertSame('Blue Troop', $roles['patrol_member']['troop_name']);
    }

    private function postJson(string $uri, array $data, string $authToken = null): \Psr\Http\Message\ResponseInterface
    {
        $request = (new ServerRequestFactory())->createServerRequest('POST', $uri);
        $stream = (new StreamFactory())->createStream(json_encode($data));
        $request = $request
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json');

        if ($authToken) {
            $request = $request->withHeader('Authorization', 'Bearer ' . $authToken);
        }

        return $this->app->handle($request);
    }

    private function getJson(\Psr\Http\Message\ResponseInterface $response): array
    {
        return json_decode((string)$response->getBody(), true);
    }
}