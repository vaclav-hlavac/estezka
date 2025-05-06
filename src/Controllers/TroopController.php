<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use App\Repository\GangRepository;
use App\Repository\TroopRepository;
use App\Utils\JsonResponseHelper;
use Exception;
use App\Models\Troop;


/**
 * @OA\Tag(name="Troops", description="Správa oddílů")
 * @OA\PathItem(path="/troops")
 */
class TroopController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, Troop::class, new TroopRepository($pdo));
    }


    /**
     * Creates a new patrol (gang) within the specified troop.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request containing JSON body with patrol name
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response object
     * @param array $args Route arguments, especially the troop ID
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Post(
     *     path="/troops/{id}/patrol",
     *     summary="Create a new patrol in a troop",
     *     tags={"Troops", "Patrols"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the troop to add the patrol to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data of the new patrol",
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Wolves"),
     *             @OA\Property(property="color", type="string", example="#1a73e8")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Patrol successfully created"),
     *     @OA\Response(response=400, description="Missing name"),
     *     @OA\Response(response=404, description="Troop not found")
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
        $data['id_troop'] = $args['id'];
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
            $gang = $gangRepository->insert($gang->toDatabase());
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        $response->getBody()->write(json_encode($gang));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }


    /**
     * Returns all patrols belonging to a given troop.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args Route arguments (expects 'id' for troop ID)
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Get(
     *     path="/troops/{id}/patrol",
     *     summary="Get all patrols of a troop",
     *     tags={"Troops", "Patrols"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of patrols"),
     *     @OA\Response(response=404, description="Troop not found")
     * )
     */
    public function getTroopGangs($request, $response, $args) {
        $troopRepository = new TroopRepository($this->pdo);

        $troop = $troopRepository->findById($args['id']);
        if (!$troop) {
            $response->getBody()->write(json_encode(['message' => 'Troop not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $gangRepository = new GangRepository($this->pdo);
        $gangs = $gangRepository->findAllByTroopId($args['id']);


        $response->getBody()->write(json_encode($gangs));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }


    /**
     * Returns all patrol members (gang members) within a given troop.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $args Route arguments (expects 'id' for troop ID)
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Get(
     *     path="/troops/{id}/members",
     *     summary="Get all patrol members in a troop",
     *     tags={"Troops", "Patrols"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of patrol members"),
     *     @OA\Response(response=500, description="Error fetching data")
     * )
     */
    public function getTroopMembers($request, $response, $args)
    {
        $troopId = (int)($args['id'] ?? 0);

        try {
            $members = $this->repository->findAllMembersWithRoleGangMember($troopId);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($members, 200, $response);
    }
}