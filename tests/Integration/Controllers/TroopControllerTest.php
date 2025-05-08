<?php

declare(strict_types=1);

namespace Tests\Integration\Controllers;

use App\Controllers\TroopController;
use App\Models\Gang;
use App\Models\Troop;
use App\Repository\GangRepository;
use App\Repository\TroopRepository;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;
use Tests\TestUtils\DatabaseCleaner;

/**
 * Integration tests for TroopController.
 * Tests CRUD + createGang() + getTroopGangs() without using Slim routing.
 */
final class TroopControllerTest extends TestCase
{
    private PDO $pdo;
    private TroopController $controller;
    private TroopRepository $troopRepository;
    private GangRepository $gangRepository;

    protected function setUp(): void
    {
        $container = require __DIR__ . '/../../../tests/bootstrap.php';
        $this->pdo = $container->get(PDO::class);
        $this->controller = new TroopController($this->pdo, $container);
        $this->troopRepository = $container->get(TroopRepository::class);
        $this->gangRepository = $container->get(GangRepository::class);

        DatabaseCleaner::cleanAll($this->pdo);
    }

    public function testCreateAndRetrieveTroop(): void
    {
        $request = $this->createJsonRequest(['name' => 'Test Troop']);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->create($request, $response, []);
        $this->assertSame(201, $result->getStatusCode());

        $troops = $this->troopRepository->findAll();
        $this->assertCount(1, $troops);
        $this->assertSame('Test Troop', $troops[0]->name);
    }

    public function testCreateGangForTroop(): void
    {
        $troop = $this->troopRepository->insert(['name' => 'Alpha Troop']);
        $request = $this->createJsonRequest(['name' => 'Red Patrol']);
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->createGang($request, $response, ['id' => $troop->getId()]);
        $this->assertSame(201, $result->getStatusCode());

        $gangs = $this->gangRepository->findAllByTroopId($troop->getId());
        $this->assertCount(1, $gangs);
        $this->assertSame('Red Patrol', $gangs[0]->name);
    }

    public function testGetTroopGangsReturnsCorrectPatrols(): void
    {
        $troop = $this->troopRepository->insert(['name' => 'Bravo Troop']);
        $this->gangRepository->insert([
            'name' => 'Blue Patrol',
            'color' => 'blue',
            'id_troop' => $troop->getId(),
            'invite_code' => 'testcode123'
        ]);

        $request = (new ServerRequestFactory())->createServerRequest('GET', '/');
        $response = (new ResponseFactory())->createResponse();

        $result = $this->controller->getTroopGangs($request, $response, ['id' => $troop->getId()]);
        $this->assertSame(200, $result->getStatusCode());

        $json = json_decode((string)$result->getBody(), true);
        $this->assertCount(1, $json);
        $this->assertSame('Blue Patrol', $json[0]['name']);
    }

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