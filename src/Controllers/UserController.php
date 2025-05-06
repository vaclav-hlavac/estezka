<?php

namespace App\Controllers;
use App\Models\User;
use App\Repository\UserRepository;
use App\Services\UserRolesService;
use App\Utils\JsonResponseHelper;

require_once __DIR__ . '/../../vendor/autoload.php';

class UserController extends CRUDController
{

    public function __construct($pdo) {
        parent::__construct($pdo, User::class, new UserRepository($pdo) );
    }

    /**
     * Updates the authenticated user's profile.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response The HTTP response.
     * @param array $args Unused route arguments.
     * @return \Psr\Http\Message\ResponseInterface JSON with updated user or error.
     *
     * @OA\Patch(
     *     path="/users/me",
     *     summary="Update own user profile",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nickname", type="string", example="scout123"),
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="notifications_enabled", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="User updated"),
     *     @OA\Response(response=400, description="Invalid input or JSON"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function updateSelf($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        if ($data === null) {
            return JsonResponseHelper::jsonResponse('Invalid JSON format.', 400, $response);
        }

        $userId = $request->getAttribute('auth_user')['id_user'];

        $foundUser = $this->repository->findById($userId);
        if ($foundUser == null) {
            return JsonResponseHelper::jsonResponse('User not found', 404, $response);
        }

        $foundUser->setAttributes($data);

        $updatedUser = $this->repository->update($foundUser->getId(), $foundUser->toDatabase());

        return JsonResponseHelper::jsonResponse($updatedUser, 200, $response);
    }

    /**
     * Retrieves a user by ID including all roles.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response The HTTP response.
     * @param array $args Route parameters, must include 'id'.
     * @return \Psr\Http\Message\ResponseInterface JSON with user and roles or error.
     *
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get user by ID with roles",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="User with roles returned"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function getById($request, $response, $args)
    {
        $userRolesService = new UserRolesService($this->pdo);
        $userWithRoles = $userRolesService->loadByUserId($args['id']);
        return JsonResponseHelper::jsonResponse($userWithRoles, 200, $response);
    }
}