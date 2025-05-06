<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use App\Repository\GangRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Utils\JsonResponseHelper;
use Psr\Http\Message\ResponseInterface;

class GangController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, Gang::class, new GangRepository($pdo) );
    }

    /**
     * Retrieves all members of a specific patrol belonging to a troop.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response
     * @param array $args Route arguments: id_troop and id_patrol
     * @return ResponseInterface JSON with members or error message
     *
     * @OA\Get(
     *     path="/troops/{id_troop}/patrols/{id_patrol}/members",
     *     summary="Get all patrol members within a troop",
     *     tags={"Troops", "Patrols"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         description="ID of the troop",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_patrol",
     *         in="path",
     *         required=true,
     *         description="ID of the patrol",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of patrol members"),
     *     @OA\Response(response=403, description="Patrol does not belong to troop"),
     *     @OA\Response(response=404, description="Patrol not found"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function getGangMembers($request, $response, $args)
    {
        $troopId = (int) $args['id_troop'];
        $gangId = (int) $args['id_patrol'];

        try {
            // 1. Verify that the gang belongs to the specified troop
            $gangRepository = new GangRepository($this->pdo);
            $gang = $gangRepository->findById($gangId);

            if (!$gang) {
                return JsonResponseHelper::jsonResponse('Gang not found', 404, $response);
            }

            if ($gang->id_troop !== $troopId) {
                return JsonResponseHelper::jsonResponse('Gang does not belong to the specified troop', 403, $response);
            }

            // 2. Fetch all gang members for the gang
            $gangMemberRepository = new GangMemberRepository($this->pdo);
            $members = $gangMemberRepository->findAllByGangId($gangId);

            return JsonResponseHelper::jsonResponse($members, 200, $response);
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse('Database error: ' . $e->getMessage(), 500, $response);
        }
    }

    /**
     * Validates whether the given invite code corresponds to an existing patrol.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 request with JSON body
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response
     * @param array $args Route arguments (not used)
     * @return ResponseInterface JSON response with gang info or error
     *
     * @OA\Post(
     *     path="/patrol/check-invite",
     *     summary="Check if invite code is valid",
     *     tags={"Patrols"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invite_code"},
     *             @OA\Property(property="invite_code", type="string", example="abc123xyz")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Valid invite code, patrol found"),
     *     @OA\Response(response=400, description="Missing or invalid invite code"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function checkInviteCode($request, $response, $args)
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        //Arguments control
        if (!isset($data['invite_code'])) {
            return JsonResponseHelper::jsonResponse('Missing required field: invite_code', 400, $response);
        }

        $gangRepository = new GangRepository($this->pdo);
        try {
            $gang = $gangRepository->findGangByInviteCode($data['invite_code']);
            if (!$gang) {
                return JsonResponseHelper::jsonResponse('The code does not belong to any patrol.', 400, $response);
            }
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($gang, 200, $response);
    }
}