<?php

namespace App\Middleware;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware {
    public function __invoke(Request $request, RequestHandler $handler) {
        // Getting authorization header and syntax control
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse();
        }

        $jwt = $matches[1];

        // Decoding token
        try {
            $decoded = JWT::decode($jwt, new Key($_ENV['JWT_SECRET'], 'HS256'));
            // Adding authorized user to request
            $request = $request->withAttribute('auth_user', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse();
        }

        return $handler->handle($request);
    }


    private function unauthorizedResponse() {
        $response = new Response();
        $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}