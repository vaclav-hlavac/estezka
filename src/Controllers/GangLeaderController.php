<?php

namespace App\Controllers;

use App\Models\Roles\GangLeader;
use App\Repository\Roles\GangLeaderRepository;

class GangLeaderController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, GangLeader::class, new GangLeaderRepository($pdo) );
    }

    /**
     * Creates a new patrol leader in the specified patrol.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request containing user ID.
     * @param \Psr\Http\Message\ResponseInterface $response The HTTP response.
     * @param array $args Route arguments including id_patrol.
     * @return \Psr\Http\Message\ResponseInterface JSON with created patrol leader or error.
     *
     * @OA\Post(
     *     path="/patrols/{id_patrol}/leaders",
     *     summary="Add a patrol leader to a patrol",
     *     tags={"Patrols"},
     *     @OA\Parameter(
     *         name="id_patrol",
     *         in="path",
     *         required=true,
     *         description="ID of the patrol to assign leader to",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_user"},
     *             @OA\Property(property="id_user", type="integer", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Patrol leader assigned",
     *         @OA\JsonContent(ref="#/components/schemas/GangLeader")
     *     ),
     *     @OA\Response(response=400, description="Invalid input or patrol ID"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function addPatrolLeader($request, $response, $args)
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        $data['id_patrol'] = $args['id_patrol'];
        $request = $request->withParsedBody($data);

        return parent::create($request, $response, $args);

    }

}