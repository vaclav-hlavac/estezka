<?php

namespace App\Controllers;

use App\Models\Roles\GangLeader;
use App\Repository\Roles\GangLeaderRepository;
use App\Utils\JsonResponseHelper;

/**
 * @OA\Tag(name="Patrol Leaders", description="Manage patrol leaders")
 * @OA\PathItem(path="/troops/{id_troop}/patrols/{id_patrol}")
 */
class PatrolLeaderController extends CRUDController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo,  GangLeader::class, new GangLeaderRepository($pdo));
    }

    /**
     * Assigns a user as patrol leader of the specified patrol.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response
     * @param array $args Route arguments: id_troop, id_patrol, id_user
     * @return \Psr\Http\Message\ResponseInterface JSON response with created patrol leader
     *
     * @OA\Post(
     *     path="/troops/{id_troop}/patrols/{id_patrol}/members/{id_user}/patrol-leaders",
     *     summary="Assign patrol leader role to a user",
     *     tags={"Patrol Leaders"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_patrol",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=201, description="Patrol leader created"),
     *     @OA\Response(response=500, description="Database error")
     * )
     */
    public function create($request, $response, $args)
    {
        $data = [];
        $data['id_user'] = $args['id_user'];
        $data['id_patrol'] = $args['id_patrol'];

        $object = new $this->modelClass($data);

        // save + response
        $savedObject = $this->repository->insert($object->toDatabase());
        return JsonResponseHelper::jsonResponse($savedObject, 201, $response);
    }

    /**
     * Deletes a patrol leader role.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response
     * @param array $args Route arguments: id_troop, id_patrol, id_patrol_leader
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Delete(
     *     path="/troops/{id_troop}/patrols/{id_patrol}/patrol-leaders/{id_patrol_leader}",
     *     summary="Remove patrol leader role",
     *     tags={"Patrol Leaders"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_patrol",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_patrol_leader",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Patrol leader deleted"),
     *     @OA\Response(response=404, description="Patrol leader not found")
     * )
     */
    public function delete($request, $response, $args){

        $args['id'] = $args['id_patrol_leader'];
        return parent::delete($request, $response, $args);
    }
}