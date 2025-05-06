<?php

namespace App\Controllers;

use App\Models\Roles\TroopLeader;
use App\Repository\Roles\TroopLeaderRepository;
use App\Utils\JsonResponseHelper;

class TroopLeaderController extends CRUDController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo,  TroopLeader::class, new TroopLeaderRepository($pdo));
    }

    /**
     * Assigns a user as a troop leader to a specific troop.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response      The HTTP response.
     * @param array $args Route parameters including 'id_user' and 'id_troop'.
     * @return \Psr\Http\Message\ResponseInterface JSON with created troop leader or error.
     *
     * @OA\Post(
     *     path="/troops/{id_troop}/members/{id_user}/troop-leaders",
     *     summary="Assign a user as a troop leader",
     *     tags={"Troops"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         description="ID of the troop",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         description="ID of the user to assign",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=201, description="Troop leader assigned successfully"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function create($request, $response, $args)
    {
        $data = [];
        $data['id_user'] = $args['id_user'];
        $data['id_troop'] = $args['id_troop'];

        $object = new $this->modelClass($data);

        // save + response
        $savedObject = $this->repository->insert($object->toDatabase());
        return JsonResponseHelper::jsonResponse($savedObject, 201, $response);
    }

    /**
     * Removes a troop leader by their ID.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  The HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response      The HTTP response.
     * @param array $args Route parameters including 'id_patrol_leader'.
     * @return \Psr\Http\Message\ResponseInterface JSON response with result of deletion.
     *
     * @OA\Delete(
     *     path="/troops/{id_troop}/members/{id_user}/troop-leaders",
     *     summary="Remove a troop leader",
     *     tags={"Troops"},
     *     @OA\Parameter(
     *         name="id_patrol_leader",
     *         in="path",
     *         required=true,
     *         description="ID of the troop leader to remove",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Troop leader removed"),
     *     @OA\Response(response=404, description="Leader not found"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function delete($request, $response, $args){

        $args['id'] = $args['id_troop_leader'];
        return parent::delete($request, $response, $args);
    }

}