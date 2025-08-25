<?php

namespace App\Middleware;

use App\Enums\RoleScope;
use App\Exceptions\ForbiddenException;
use App\Services\AccessService;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpForbiddenException;
use Slim\Routing\RouteContext;

/**
 * Middleware that restricts access to routes based on the user's role and scope.
 *
 * This middleware supports multiple scopes (e.g., SELF, PATROL, TROOP) and allows
 * access if the user satisfies at least one of the provided scope conditions.
 *
 * Scopes:
 * - SELF: The user can only access resources associated with their own user ID.
 * - PATROL: The user must be a patrol leader or a troop leader with access to the patrol.
 * - TROOP: The user must be a troop leader of the given troop.
 */
class RoleScopeMiddleware implements MiddlewareInterface
{
    /**
     * @param RoleScope[] $requiredScopes
     */
    public function __construct(
        private array $requiredScopes,
        private AuthService $authService,
        private AccessService $accessService
    ) {}

    /**
     * Processes an incoming server request and checks if the user has the required scope.
     *
     * If the user satisfies at least one of the required scopes, the request is passed to the next handler.
     * Otherwise, an HTTP 403 Forbidden response is returned.
     *
     * @param Request $request The incoming server request.
     * @param RequestHandlerInterface $handler The request handler to delegate to if access is granted.
     * @return Response The response from the next middleware or controller.
     * @throws HttpForbiddenException If the user does not meet any of the required scope conditions.
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $route = RouteContext::fromRequest($request)->getRoute();

        $token = $request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $token);
        $userId = $this->authService->getUserIdFromToken($token);

        foreach ($this->requiredScopes as $scope) {
            switch ($scope) {
                case RoleScope::SELF:
                    $idInPath = (int) $route?->getArgument('id_user');

                    if ($idInPath === $userId) {
                        return $handler->handle($request);
                    }
                    break;

                case RoleScope::PATROL:
                    $gangId = (int) $route?->getArgument('id_patrol');

                    if ($this->accessService->hasAccessToGang($userId, $gangId)) {
                        return $handler->handle($request);
                    }
                    break;

                case RoleScope::TROOP:
                    $troopId = (int) $route?->getArgument('id_troop');

                    error_log($troopId);
                    if ($this->accessService->hasAccessToTroop($userId, $troopId)) {
                        return $handler->handle($request);
                    }
                    break;
            }
        }

        throw new ForbiddenException('Access denied. You do not meet any of the required role conditions.', 403);
    }
}