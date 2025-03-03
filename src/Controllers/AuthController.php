<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\User;
use App\Repository\UserRepository;
use App\Services\AuthService;
use Exception;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use PDO;
use Symfony\Component\Console\Exception\MissingInputException;

/**
 * @OA\Tag(name="Auth", description="Autorizace uživatelů")
 * @OA\PathItem(path="/auth")
 */
class AuthController {
    private PDO $pdo;
    private AuthService $authService;

    public function __construct($pdo, $authService) {
        $this->pdo = $pdo;
        $this->authService = $authService;
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

        // Authenticate user
        try {
            $user = $this->authenticateUser($data['login_name'], $data['password']);
            if($user == null){
                return $response->withJson(['message' => 'Wrong login name or password'], 401);
            }
        } catch (DatabaseException $e) {
            return $response->withJson(['message' => $e->getMessage()], $e->getCode());
        }

        // Return response with token
        $jwt = $this->authService->generateJWT($user);
        return $response->withJson(['token' => $jwt], 200);
    }

    /**
     * @param $loginName
     * @param $password
     * @return User|null
     * @throws DatabaseException
     */
    private function authenticateUser($loginName, $password): ?User {
        // Find user by login and verify
        $loginName = trim(strtolower($loginName));
        $userRepository = new UserRepository($this->pdo);
        $user = $userRepository->findByLoginName($loginName);


        // authorization
        if ($user == null || !password_verify($password, $user->password)) {
            return null;
        }
        return $user;
    }


}