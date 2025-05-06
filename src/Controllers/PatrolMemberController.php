<?php

namespace App\Controllers;

use App\Models\Roles\GangMember;
use App\Repository\Roles\GangMemberRepository;

/**
 * @OA\Tag(name="Patrol Members", description="Manage patrol members")
 * @OA\PathItem(path="/patrols")
 */
class PatrolMemberController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, GangMember::class, new GangMemberRepository($pdo) );
    }

    /**
     * Updates patrol member information (e.g., active path level).
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR-7 request
     * @param \Psr\Http\Message\ResponseInterface $response PSR-7 response
     * @param array $args Route arguments: id_patrol, id_user
     * @return \Psr\Http\Message\ResponseInterface Updated patrol member object
     *
     * @OA\Patch(
     *     path="/patrols/{id_patrol}/members/{id_user}",
     *     summary="Update patrol member data",
     *     tags={"Patrol Members"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="active_path_level", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Patrol member updated"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=404, description="Patrol member not found")
     * )
     */
    public function updatePatrolMember($request, $response, $args){

        $args['id'] = $args['id_user'];
        return parent::update($request, $response, $args);
    }

}