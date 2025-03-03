<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Console\Exception\MissingInputException;

class AuthService {
    private string $jwtSecret;

    public function __construct() {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? throw new MissingInputException('Server error: Missing JWT secret', 500);
    }

    public function generateJWT(User $user): string {
        // Generating JWT token
        $payload = $user->getPayload();
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function getUserIdFromToken(string $token): ?int {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $decoded->id_user ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}