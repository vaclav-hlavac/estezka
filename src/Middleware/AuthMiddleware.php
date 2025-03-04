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
        // Getting authorization header and syntax control
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }

        $jwt = $matches[1];

        // Decoding token
        try {
            $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
            // Adding authorized user to request
            $request = $request->withAttribute('auth_user', $decoded);
        } catch (Exception $e) {
            return JsonResponseHelper::jsonResponse('Unauthorized', 401, new SlimResponse());
        }

        return $handler->handle($request);
    }
}