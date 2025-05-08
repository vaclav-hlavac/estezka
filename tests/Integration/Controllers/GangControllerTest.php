<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Controllers\GangController;
use App\Models\Gang;
use App\Repository\GangRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Repository\TroopRepository;
use App\Repository\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestUtils\DatabaseCleaner;

/**
 * Integration tests for GangController.
 * Tests both CRUD functionality and custom endpoints.
 */
final class GangControllerTest extends TestCase
{
    private PDO $pdo;
    private GangController $controller;
    private GangRepository $gangRepo;
    private TroopRepository $troopRepo;
    private UserRepository $userRepo;
    private GangMemberRepository $gangMemberRepo;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';

        $this->pdo = $container->get(PDO::class);
        $this->controller = new GangController($this->pdo, $container);
        $this->gangRepo = $container->get(GangRepository::class);
        $this->troopRepo = $container->get(TroopRepository::class);
        $this->userRepo = $container->get(UserRepository::class);
        $this->gangMemberRepo = $container->get(GangMemberRepository::class);

        DatabaseCleaner::cleanAll($this->pdo);
    }

    public function testCheckInviteCodeReturnsCorrectGang(): void
    {
        $troop = $this->troopRepo->insert(['name' => 'Troop A']);
        $this->gangRepo->insert([
            'name' => 'Blue Patrol',
            'color' => 'blue',
            'id_troop' => $troop->getId(),
            'invite_code' => 'code-blue'
        ]);

        $request = $this->createJsonRequest(['invite_code' => 'code-blue']);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->checkInviteCode($request, $response, []);
        $this->assertSame(200, $result->getStatusCode());

        $data = json_decode((string)$result->getBody(), true);
        $this->assertSame('Blue Patrol', $data['name']);
    }

    public function testGetGangMembersReturnsUsers(): void
    {
        $troop = $this->troopRepo->insert(['name' => 'Troop B']);
        $gang = $this->gangRepo->insert([
            'name' => 'Red Patrol',
            'color' => 'red',
            'id_troop' => $troop->getId(),
            'invite_code' => 'code-red'
        ]);

        $user = $this->userRepo->insert([
            'nickname' => 'scout',
            'name' => 'Scout',
            'surname' => 'Member',
            'password' => 'pwd',
            'email' => 'scout@example.com',
            'notifications_enabled' => true
        ]);

        $this->gangMemberRepo->insert([
            'id_user' => $user->getId(),
            'id_patrol' => $gang->getId()
        ]);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->getGangMembers($request, $response, [
            'id_troop' => $troop->getId(),
            'id_patrol' => $gang->getId()
        ]);

        $this->assertSame(200, $result->getStatusCode());

        $data = json_decode((string)$result->getBody(), true);
        $this->assertCount(1, $data);
        $this->assertSame('scout', $data[0]['nickname']);
    }

    private function createJsonRequest(array $data): \Psr\Http\Message\ServerRequestInterface
    {
        $streamFactory = new StreamFactory();
        $stream = $streamFactory->createStream(json_encode($data));

        return (new ServerRequestFactory())
            ->createServerRequest('POST', '/')
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json');
    }
}