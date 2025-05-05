<?php

namespace App\Middleware;


use App\Exceptions\DatabaseException;

use App\Services\AccessService;
use App\Services\AuthService;
use App\Utils\JsonResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class GangAuthorizationMiddleware
{

    private AccessService $accessService;

    public function __construct(AccessService $accessService) {
        $this->accessService = $accessService;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        // get auth_user from request
        $authUser = $request->getAttribute('auth_user');
        if (!$authUser || !isset($authUser->id_user)) {
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }
        $userId = $authUser->id_user;

        // Get id_gang from request
        $gangId = $request->getAttribute('id_patrol');
        if (!$gangId) {
            return JsonResponseHelper::jsonResponse('Bad request: Missing id_gang', 400, new SlimResponse());
        }

        // Check if the user has access to the troop
        try {
            if (!$this->accessService->hasAccessToGang($userId, $gangId)) {
                return JsonResponseHelper::jsonResponse('Forbidden', 403, new SlimResponse());
            }
        }catch (DatabaseException $e){
            return JsonResponseHelper::jsonResponse('Database error', 500, new SlimResponse());
        }

        return $handler->handle($request);
    }


}