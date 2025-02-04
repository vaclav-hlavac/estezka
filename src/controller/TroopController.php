<?php

namespace controller;
require_once __DIR__ . '/../autoloader.php';

use model\BaseModel;
use model\Troop;
class TroopController
{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllTroops($request, $response, $args) {
        $troops = Troop::all($this->pdo);
        $response->getBody()->write(json_encode($troops));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function createTroop($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        if (!isset($data['name'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing required field: name']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $troop = new Troop($data['name']);
        $troop->save($this->pdo);

        $response->getBody()->write(json_encode($troop));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function getTroop($request, $response, $args) {
        $troop = Troop::find($args['id'], $this->pdo);
        if ($troop) {
            $response->getBody()->write(json_encode($troop));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');

        } else {
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    public function updateTroop($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        if (!isset($data['name'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing required field: name']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $troop = Troop::find($args['id'], $this->pdo);

        if (!$troop) {
            // Pokud troop neexistuje, vytvoříme nový
            $troop = new Troop($data['name']);
            $troop->save($this->pdo);

            $response->getBody()->write(json_encode([
                'message' => 'Troop created',
                'troop' => $troop
            ]));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        }

        // Pokud troop existuje, aktualizujeme ho
        $troop = new Troop($data['name'], $troop['id_troop']);
        $troop->save($this->pdo);

        $response->getBody()->write(json_encode([
            'message' => 'Troop updated',
            'troop' => $troop
        ]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    public function deleteTroop($request, $response, $args) {
        $troop = Troop::find($args['id'], $this->pdo);
        if (!$troop) {
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $troop = new Troop($troop['name'], $troop['id_troop']);
        $troop->delete($this->pdo);
        $response->getBody()->write(json_encode(['message' => 'Troop deleted']));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

}