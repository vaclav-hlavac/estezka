<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use App\Repository\UserRepository;
use Exception;
use Firebase\JWT\JWT;
use InvalidArgumentException;

/**
 * @OA\Tag(name="Auth", description="Autorizace uživatelů")
 * @OA\PathItem(path="/auth")
 */
class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // lower-case and delete spaces of some arguments
        $data['login_name'] = trim(strtolower($data['login_name'] ?? ''));
        $data['email'] = trim(strtolower($data['email'] ?? ''));
        $data['password'] = trim($data['password'] ?? '');

        // required arguments check
        try {
            $user = new User($data);
        }catch (InvalidArgumentException $e){
            $response->getBody()->write(json_encode(['message' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // unique email check
        $userRepository = new UserRepository($this->pdo);
        if($userRepository->emailExists($user->email)){
            $response->getBody()->write(json_encode(['message' => 'User with this email already exists.']));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        // save to DB
        try {
            $savedUser = $userRepository->insert($user->toArray());
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['message' => 'Database error.']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }

        // response
        $response->getBody()->write(json_encode($savedUser));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function login($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // Check required fields
        if (empty($data['login_name']) || empty($data['password'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing nickname or password']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Find user by login and verify
        $loginName = trim(strtolower($data['login_name']));
        $userRepository = new UserRepository($this->pdo);
        $user = $userRepository->findByLoginName($loginName);
        if ($user == null || !password_verify($data['password'], $user->password)) {
            $response->getBody()->write(json_encode(['message' => 'Invalid login name or password']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Check if JWT secret exists
        $secret = $_ENV['JWT_SECRET'] ?? null;
        if (!$secret) {
            return $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['message' => 'Server error: Missing JWT secret']));
        }

        // Generating JWT token
        $payload = $user->getPayload();
        $jwt = JWT::encode($payload, $secret, 'HS256');

        // Return response with token
        return $response->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(['token' => $jwt]));
    }
}