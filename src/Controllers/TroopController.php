<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\Gang;
use App\Repository\TroopRepository;
use OpenApi\Annotations as OA;
use App\Models\Troop;
use PhpParser\Node\Expr\Array_;


/**
 * @OA\Tag(name="Troops", description="Správa oddílů")
 * @OA\PathItem(path="/troops")
 */
class TroopController extends CRUDController
{

    public function __construct($pdo) {
        parent::__construct($pdo, Troop::class, TroopRepository::class );
    }


    /**
     * @OA\Post(
     *     path="/troops/{id}/gang",
     *     summary="Vytvořit novou družinu v oddíle",
     *     tags={"Troops", "Gangs"},
     *     @OA\Parameter(
     *             name="id",
     *             in="path",
     *             required=true,
     *             @OA\Schema(type="integer")
     *       ),
     *     @OA\Response(response="201", description="Nová družina vytvořena"),
     *     @OA\Response(response="400", description="Chybějící název")
     * )
     */
    public function createGang($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        //Arguments control
        if (!isset($data['name'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing required field: name']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        //Existing troop control
        $troop = Troop::find($this->pdo, $args['id']);
        if (!$troop) {
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        //creating new gang
        $data['troopId'] = $args['id'];
        $gang = new Gang($this->pdo, $data);
        $gang->save();

        $response->getBody()->write(json_encode($gang));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }


    /**
     * @OA\Get(
     *     path="/troops/{id}/gang",
     *      summary="Získat všechny družiny oddílu",
     *      tags={"Troops", "Gangs"},
     *      @OA\Parameter(
     *              name="id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(type="integer")
     *        ),
     *     @OA\Response(response="200", description="Seznam oddílů")
     * )
     */
    public function getTroopGangs($request, $response, $args) {
        if(Troop::find($this->pdo, $args['id']) == null){
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $gangs = Gang::getAllByTroopId($this->pdo, $args['id']);

        $response->getBody()->write(json_encode($gangs));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}