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

class AuthMiddleware {
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