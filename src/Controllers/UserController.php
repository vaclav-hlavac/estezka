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

    public function getById($request, $response, $args)
    {
        $userRolesService = new UserRolesService($this->pdo);
        $userWithRoles = $userRolesService->loadByUserId($args['id']);
        return JsonResponseHelper::jsonResponse($userWithRoles, 200, $response);
    }
}