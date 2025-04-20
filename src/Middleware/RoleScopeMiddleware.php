<?php

namespace App\Middleware;

use App\Enums\RoleScope;
use App\Services\AccessService;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Exception\HttpForbiddenException;

class RoleScopeMiddleware implements MiddlewareInterface //todo getUserIdFromToken a accessService
{
    public function __construct(
        private RoleScope $requiredScope,
        private AuthService $authService,
        private AccessService $accessService
    ) {}

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $user = $this->authService->getUserIdFromToken($request);

        // SELF: user can only access their own ID
        if ($this->requiredScope === RoleScope::SELF) {
            $idInPath = (int) $request->getAttribute('id_user');
            if ($idInPath !== $user->getId()) {
                throw new HttpForbiddenException($request, 'You can only access your own data.');
            }
        }

        // GANG: user must be a member of the patrol they are trying to access
        if ($this->requiredScope === RoleScope::PATROL) {
            $patrolId = (int) $request->getAttribute('id_patrol');
            if (!$this->accessService->isUserInGang($user->getId(), $patrolId)) {
                throw new HttpForbiddenException($request, 'Access denied to this patrol.');
            }
        }

        // TROOP: user must be a troop leader of the specified troop
        if ($this->requiredScope === RoleScope::TROOP) {
            $troopId = (int) $request->getAttribute('id_troop');
            if (!$this->accessService->isUserTroopLeader($user->getId(), $troopId)) {
                throw new HttpForbiddenException($request, 'Access denied to this troop.');
            }
        }

        return $handler->handle($request);
    }
}