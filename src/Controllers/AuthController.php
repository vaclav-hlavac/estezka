<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\User;
use App\Repository\UserRepository;
use Exception;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\MissingInputException;

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

        try {
            // required arguments check
            $user = new User($data);

            // unique email check
            $userRepository = new UserRepository($this->pdo);
            $userRepository->emailExists($user->email);

            // save to DB + response
            $savedUser = $userRepository->insert($user->toArray());
            return $response->withJson($savedUser, 201);

        } catch (DatabaseException $e) {
            return $response->withJson(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function login($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // Check required fields
        if (empty($data['login_name']) || empty($data['password'])) {
            return $response->withJson(['message' => 'Missing nickname or password'], 400);
        }

        // Find user by login and verify
        $loginName = trim(strtolower($data['login_name']));
        $userRepository = new UserRepository($this->pdo);
        try {
            $user = $userRepository->findByLoginName($loginName);
        } catch (DatabaseException $e) {
            return $response->withJson(['message' => $e->getMessage()], $e->getCode());
        }

        // authorization
        if ($user == null || !password_verify($data['password'], $user->password)) {
            return $response->withJson(['message' => 'Invalid login name or password'], 401);
        }

        // generate JWT token
        try {
            $jwt = $this->generateJWT($user);
        } catch (MissingInputException $e) {
            return $response->withJson(['message' => $e->getMessage()], $e->getCode());
        }

        // Return response with token
        return $response->withJson(['token' => $jwt], 200);
    }


    //******** PRIVATE ***********************************************************
    private function generateJWT(User $user): string
    {
        // Check if JWT secret exists
        $secret = $_ENV['JWT_SECRET'] ?? null;
        if (!$secret) {
            throw new MissingInputException('Server error: Missing JWT secret', 500);
        }

        // Generating JWT token
        $payload = $user->getPayload();
        return JWT::encode($payload, $secret, 'HS256');
    }

}