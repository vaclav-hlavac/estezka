<?php

namespace App\Middleware;

use App\Repository\TroopRepository;
use App\Services\AccessService;
use App\Services\AuthService;
use App\Utils\JsonResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
class TroopAuthorizationMiddleware
{
    private $repository;
    private $auth;
    private $accessService;

    public function __construct(TroopRepository $repository, AuthService $auth, AccessService $accessService) {
        $this->repository = $repository;
        $this->auth = $auth;
        $this->accessService = $accessService;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        // Získání ověřeného uživatele z requestu
        $authUser = $request->getAttribute('auth_user');

        if (!$authUser || !isset($authUser->user_id)) {
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }

        $userId = $authUser->user_id;

        // Získání troop_id z requestu
        $troopId = $request->getAttribute('troop_id');

        if (!$troopId) {
            return JsonResponseHelper::jsonResponse('Bad request: Missing troop_id', 400, new SlimResponse());
        }

        // Check if the user has access to the troop
        if (!$this->accessService->hasAccessToTroop($userId, $troopId)) {
            return JsonResponseHelper::jsonResponse('Forbidden', 403, new SlimResponse());
        }

        // Předání requestu dál
        return $handler->handle($request);
    }

}