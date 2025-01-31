<?php

namespace controller;
require_once __DIR__ . '/../autoloader.php';

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
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function createTroop($request, $response, $args) {
//        $data = $request->getParsedBody();

        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        //todo debuging
/*        error_log("Raw body: " . $request->getBody());
        error_log("Parsed body: " . json_encode($data));*/

        $troop = new Troop($data['name']);
        $troop->save($this->pdo);

        $response->getBody()->write(json_encode($troop));
        return $response->withHeader('Content-Type', 'application/json');
    }

}