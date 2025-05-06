<?php

namespace App\Middleware;

use App\Utils\JsonResponseHelper;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

/**
 * Authentication middleware that verifies a JWT token from the Authorization header.
 *
 * If the token is valid, it attaches user data to the request under the attribute `auth_user`.
 * Returns a 401 JSON error response if the token is missing, invalid, or expired.
 */
class AuthMiddleware {

    /**
     * Invokes the middleware.
     *
     * Validates the JWT from the Authorization header. On success, enriches the request with
     * user data; on failure, returns a 401 Unauthorized JSON response.
     *
     * @param Request $request The incoming HTTP request.
     * @param RequestHandler $handler The next middleware or route handler.
     * @return Response The response, either an error or the result of the next handler.
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
        // Read Authorization header
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            error_log("[AuthMiddleware] Missing or invalid Authorization header.");
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }

        $jwt = $matches[1];

        try {
            // Decode token
            $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));

            // Check token expiration
            if (isset($decoded->exp) && $decoded->exp < time()) {
                error_log("[AuthMiddleware] Token expired.");
                return JsonResponseHelper::jsonResponse('Token expired', 401, new SlimResponse());
            }

            // Attach user info to request
            $request = $request->withAttribute('auth_user', (array)$decoded);
        } catch (Exception $e) {
            error_log("[AuthMiddleware] JWT decode error: " . $e->getMessage());
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }

        // Continue to next middleware/controller
        return $handler->handle($request);
    }
}