<?php

namespace App\Middleware;

use App\Repository\TroopRepository;
use App\Services\AuthService;
use Slim\Handlers\Strategies\RequestHandler;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class TroopAuthorizationMiddleware
{
    private $repository;
    private $auth;

    public function __construct(TroopRepository $repository, AuthService $auth) {
        $this->repository = $repository;
        $this->auth = $auth;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        // Získání ověřeného uživatele z requestu
        $authUser = $request->getAttribute('auth_user');

        if (!$authUser || !isset($authUser->user_id)) {
            return (new Response())->withJson(['message' => 'Unauthorized'], 401);
        }

        $userId = $authUser->user_id;

        // Získání troop_id z requestu
        $troopId = $request->getAttribute('troop_id');

        if (!$troopId) {
            return (new Response())->withJson(['message' => 'Bad request: Missing troop_id'], 400); //todo JSON metoda bokem??
        }

        // Ověření přístupu uživatele k oddílu
        if (!$this->repository->hasUserAccessToTroop($userId, $troopId)) {
            return (new Response())->withJson(['message' => 'Forbidden'], 403);
        }

        // Předání requestu dál
        return $handler->handle($request);
    }

}