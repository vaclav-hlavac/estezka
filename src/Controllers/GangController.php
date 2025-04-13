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
     * Retrieves all patrol members from a specified patrol within a specified troop.
     *
     * Endpoint: GET /troops/{id_troop}/patrols/{id_patrol}/members
     *
     * Validates that the patrol belongs to the given troop before fetching the members.
     *
     * @param Request $request PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array $args Route parameters (id_troop, id_patrol)
     * @return ResponseInterface JSON response containing the list of patrol members or an error
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

    public function addPatrolToTroop($request, $response, $args) //todo test + documentation
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        $data['id_troop'] = $args['id_troop'];
        $request = $request->withParsedBody($data);

        return parent::create($request, $response, $args);
    }
}