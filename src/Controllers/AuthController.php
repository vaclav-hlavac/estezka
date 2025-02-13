<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use Firebase\JWT\JWT;

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

        // Check of required arguments
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['nickname'])
            || !isset($data['name']) || !isset($data['surname'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing information (nickname, name, surname, email or password)']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Check unique email
        if(User::findByEmail($this->pdo, $data['email']) != null){
            $response->getBody()->write(json_encode(['message' => 'User with this email already exists.']));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        // Hashing password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['password'] = $hashedPassword;

        // Save to database
        $user = new User($this->pdo, $data);
        $user->save();

        // Response
        $response->getBody()->write(json_encode($user));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function login($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // Check required fields
        if (!isset($data['nickname']) || !isset($data['password'])) {
            $response->getBody()->write(json_encode(['message' => 'Missing nickname or password']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Find users by nickname
        $usersWithNickname = User::findAllByNickname($this->pdo, $data['nickname']);
        if (count($usersWithNickname) == 0) {
            $response->getBody()->write(json_encode(['message' => 'Invalid nickname']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Verify password
        $loggedUser = null;
        foreach ($usersWithNickname as $user) {
            if($user->password_verify($data['password'])){
                $loggedUser = $user;
                break;
            }
            $response->getBody()->write(json_encode(['message' => 'Invalid nickname or password']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Generating JWT token
        $payload = $loggedUser->getPayload();
        $secret = $_ENV['JWT_SECRET'];
        $jwt = JWT::encode($payload, $secret, 'HS256');

        // Return response with token
        $response->getBody()->write(json_encode(['token' => $jwt]));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

}