<?php

namespace App\Controllers;
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\DatabaseException;
use App\Models\RefreshToken;
use App\Models\Roles\GangMember;
use App\Models\Roles\TroopLeader;
use App\Models\Troop;
use App\Models\User;
use App\Repository\GangRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Repository\Roles\TroopLeaderRepository;
use App\Repository\TaskProgressRepository;
use App\Repository\TroopRepository;
use App\Repository\UserRepository;
use App\Services\AuthService;
use App\Services\UserRolesService;
use App\Utils\JsonResponseHelper;
use DateTime;
use Exception;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use PDO;
use Random\RandomException;
use Slim\Logger;
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
        $data['email'] = trim(strtolower($data['email'] ?? ''));
        $data['password'] = trim($data['password'] ?? '');

        if(!isset($data['new_troop']) && !isset($data['invite_code'])){
            return JsonResponseHelper::jsonResponse('Missing troop info', 400, $response);
        }


        // Creating User
        try {
            // required arguments check
            $user = new User($data);

            // unique email check
            $userRepository = new UserRepository($this->pdo);

            if($userRepository->emailExists($user->email)) {
                return JsonResponseHelper::jsonResponse('Email already exists.', 409, $response);
            }


            // Save to DB + response
            $savedUser = $userRepository->insert($user->toDatabase());
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }


        // Creating roles (+ new troop if needed)
        try {
            // setting user role
            if(isset($data['new_troop'])){
                $this->createTroopAndSetLeaderRole($data, $savedUser);
            }else{
                if(!$this->setGangMemberRoleByInvoiceCode($data['invite_code'], $savedUser)){
                    return JsonResponseHelper::jsonResponse('Wrong invite code', 404, $response);
                }
            }
        } catch (Exception $e) {
            $userRepository->delete($user->getId());
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($savedUser, 201, $response);
    }


    public function login($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // Check required fields
        if (empty($data['email']) || empty($data['password'])) {
            return JsonResponseHelper::jsonResponse('Missing email or password', 400, $response);
        }

        // Authenticate user
        try {
            $user = $this->authenticateUser($data['email'], $data['password']);
            if ($user == null) {
                return JsonResponseHelper::jsonResponse('Wrong email or password', 401, $response);
            }
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        // Refresh token
        try {
            $refreshToken = new RefreshToken(['id_user' => $user->getId()]);
            $tokenRepository = new RefreshTokenRepository($this->pdo);

            $attempts = 0;
            while ($tokenRepository->tokenExists($refreshToken->token)) {
                $refreshToken->generateNewToken();
                $attempts++;
                if ($attempts > 10) {
                    return JsonResponseHelper::jsonResponse('Refresh token could not be generated.', 500, $response);
                }
            }
            $tokenRepository->insert($refreshToken->toDatabase());
        } catch (Exception $e) {
            return JsonResponseHelper::jsonResponse('Refresh token could not be generated.', 500, $response);
        }

        // Load user with roles
        $userRolesService = new UserRolesService($this->pdo);
        $userWithRoles = $userRolesService->loadByUserId($user->getId());

        if (!$userWithRoles) {
            return JsonResponseHelper::jsonResponse('Failed to load user roles.', 500, $response);
        }

        // Return response with token and user with roles
        $jwt = $this->authService->generateJWT($user);
        return JsonResponseHelper::jsonResponse([
            'access_token' => $jwt,
            'refresh_token' => $refreshToken->token,
            'user' => $userWithRoles,
        ], 200, $response);
    }

    public function refresh($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // Check if refresh token is provided
        if (empty($data['refresh_token'])) {
            return JsonResponseHelper::jsonResponse('Missing refresh token', 400, $response);
        }

        $refreshToken = $data['refresh_token'];

        // Validate refresh token
        try {
            $tokenRepository = new RefreshTokenRepository($this->pdo);
            $userId = $tokenRepository->findUserIdByToken($refreshToken);

            if (!$userId) {
                return JsonResponseHelper::jsonResponse('Invalid or expired refresh token', 401, $response);
            }

            // Fetch user
            $userRepository = new UserRepository($this->pdo);
            $user = $userRepository->findById($userId);
            if (!$user) {
                return JsonResponseHelper::jsonResponse('User not found', 404, $response);
            }

            // Fetch user roles
            $userRolesService = new \App\Services\UserRolesService($this->pdo);
            $userWithRoles = $userRolesService->loadByUserId($user->getId());

            if (!$userWithRoles) {
                return JsonResponseHelper::jsonResponse('Failed to load user roles.', 500, $response);
            }

            // Generate new access token
            $jwt = $this->authService->generateJWT($user);

            return JsonResponseHelper::jsonResponse([
                'access_token' => $jwt,
                'user' => $userWithRoles,
            ], 200, $response);
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse('Token refresh failed: ' . $e->getMessage(), 500, $response);
        }
    }

    /**
     * @param $email
     * @param $password
     * @return User|null
     * @throws DatabaseException
     */
    private function authenticateUser($email, $password): ?User {
        // Find user by login and verify
        $email = trim(strtolower($email));
        $userRepository = new UserRepository($this->pdo);
        $user = $userRepository->findByEmail($email);


        // authorization
        if ($user == null || !password_verify($password, $user->password)) {
            return null;
        }
        return $user;
    }

    /**
     * User creating new troop + setting TroopLeader role
     * @param mixed $data
     * @param User $savedUser
     * @return void
     * @throws DatabaseException
     */
    private function createTroopAndSetLeaderRole(mixed $data, User $savedUser): void
    {
        // Creating new troop
        $troop = new Troop($data['new_troop']);
        $troopRepository = new TroopRepository($this->pdo);
        $troopRepository->insert($troop->toDatabase());

        // Setting TroopLeader role
        $troopLeaderRepository = new TroopLeaderRepository($this->pdo);
        $troopLeader = new TroopLeader([
            "id_user" => $savedUser->getId(),
            "id_troop" => $data['id_troop']
        ]);
        $troopLeaderRepository->insert($troopLeader->toDatabase());
    }

    /**
     * @param $invite_code
     * @param User $savedUser
     * @return bool false if no patrol found by invite_code
     * @throws DatabaseException
     */
    private function setGangMemberRoleByInvoiceCode($invite_code, User $savedUser): bool
    {
// User joined up an existing troop (by invite_code) => GroupMember
        // Finding gang by invite code
        $gangRepository = new GangRepository($this->pdo);
        $gang = $gangRepository->findGangByInviteCode($invite_code);
        if ($gang == null) {
            return false;
        }

        // Setting GangMember role
        $gangMemberRepository = new GangMemberRepository($this->pdo);
        $gangMember = new GangMember([
            "id_user" => $savedUser->getId(),
            "id_patrol" => $gang->getId()
        ]);

        $gangMemberRepository->insert($gangMember->toDatabase());

        //creating task_progress for each task
        $taskProgressRepository = new TaskProgressRepository($this->pdo);
        try {
            $taskProgressRepository->createAllToUser($savedUser->getId());
        } catch (Exception $e) {
            $gangMemberRepository->delete($savedUser->getId());
            throw $e;
        }

        return true;
    }
}