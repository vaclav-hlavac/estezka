<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Service responsible for handling JWT authentication logic.
 *
 * Provides methods for generating JWT tokens for users and extracting user ID from existing tokens.
 * Requires a valid `JWT_SECRET` environment variable to function properly.
 *
 * @throws Exception if JWT secret is missing during construction.
 */
class AuthService {
    private string $jwtSecret;

    public function __construct() {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? throw new Exception('Server error: Missing JWT secret', 500);
    }

    /**
     * Generate a JWT token for the given user.
     *
     * Encodes the user's payload (provided by `User::getPayload()`) into a JWT string using the HS256 algorithm.
     *
     * @param User $user The user for whom the JWT is generated.
     * @return string The encoded JWT token.
     */
    public function generateJWT(User $user): string {
        // Generating JWT token
        $payload = $user->getPayload();
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * Extract the user ID from a JWT token.
     *
     * Decodes the JWT using the secret key and attempts to return the `id_user` field.
     * Returns null if decoding fails or the ID is not found in the payload.
     *
     * @param string $token The JWT token string.
     * @return int|null The extracted user ID, or null if invalid or missing.
     */
    public function getUserIdFromToken(string $token): ?int {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $decoded->id_user ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
}