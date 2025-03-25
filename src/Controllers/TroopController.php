<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use App\Repository\GangRepository;
use App\Repository\TroopRepository;
use App\Utils\JsonResponseHelper;
use Exception;
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
        $troopRepository = new TroopRepository($this->pdo);
        try {
            $troop = $troopRepository->findById($args['id']);
            if (!$troop) {
                $response->getBody()->write(json_encode(['message' => 'Troop not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }


        //creating new gang
        $data['troopId'] = $args['id'];
        $gang = new Gang($data);
        $gangRepository = new GangRepository($this->pdo);

        // Refresh token
        try {
            $attempts = 0;
            while($gangRepository->findGangByInviteCode($gang->invite_code) != null) {
                $gang->refreshInviteCode();
                $attempts++;
                if ($attempts > 10) { // Protection against too many loops
                    return JsonResponseHelper::jsonResponse('Invite code could not be generated.', 500, $response);
                }
            }
        }catch (Exception $e) {
            return JsonResponseHelper::jsonResponse('Invite code could not be generated.', 500, $response);
        }

        try {
            $gang = $gangRepository->insert($gang->jsonSerialize());
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

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
        $troopRepository = new TroopRepository($this->pdo);
        try {
            $troop = $troopRepository->findById($args['id']);
            if (!$troop) {
                $response->getBody()->write(json_encode(['message' => 'Troop not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $gangRepository = new GangRepository($this->pdo);
            $gangs = $gangRepository->findAllByTroopId($args['id']);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        $response->getBody()->write(json_encode($gangs));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }


    public function getTroopMembers($request, $response, $args) {
        $troopRepository = new TroopRepository($this->pdo);

        try {
            $members = $troopRepository->findAllMembersById($args['id']);


            $gangRepository = new GangRepository($this->pdo);
            $gangs = $gangRepository->findAllByTroopId($args['id']);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        if(Troop::find($this->pdo, $args['id']) == null){
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $gangs = Gang::getAllByTroopId($this->pdo, $args['id']);

        $response->getBody()->write(json_encode($gangs));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}