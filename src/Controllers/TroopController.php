<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use OpenApi\Annotations as OA;
use App\Models\BaseModel;
use App\Models\Troop;



/**
 * @OA\Tag(name="Troops", description="Správa oddílů")
 * @OA\PathItem(path="/troops")
 */
class TroopController
{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * @OA\Get(
     *     path="/troops",
     *     tags={"Troops"},
     *     summary="Získat všechny oddíly",
     *     @OA\Response(response="200", description="Seznam oddílů")
     * )
     */
    public function getAllTroops($request, $response, $args) {
        $troops = Troop::all($this->pdo);
        $response->getBody()->write(json_encode($troops));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @OA\Post(
     *     path="/troops",
     *     summary="Vytvořit nový oddíl",
     *     tags={"Troops"},
     *     @OA\Response(response="201", description="Nový oddíl"),
     *     @OA\Response(response="400", description="Chybějící název")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/troops/{id}",
     *     summary="Získat konkrétní oddíl",
     *     tags={"Troops"},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Popis oddílu"),
     *     @OA\Response(response="401", description="Oddíl nenalezen")
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/troops/{id}",
     *     summary="Upravit oddíl",
     *     tags={"Troops"},
     *     @OA\Parameter(
     *           name="id",
     *           in="path",
     *           required=true,
     *           @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Nový oddíl vytvořen"),
     *     @OA\Response(response="201", description="Oddíl upraven"),
     *     @OA\Response(response="400", description="Chybí argument")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/troops/{id}",
     *     summary="Upravit oddíl",
     *     tags={"Troops"},
     *      @OA\Parameter(
     *            name="id",
     *            in="path",
     *            required=true,
     *            @OA\Schema(type="integer")
     *      ),
     *     @OA\Response(response="200", description="Oddíl smazán"),
     *     @OA\Response(response="404", description="Oddíl nenalezen")
     * )
     */
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