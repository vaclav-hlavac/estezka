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
use Exception;
use PDO;
use Psr\Container\ContainerInterface;


/**
 * @OA\Tag(name="Auth", description="Autorizace uživatelů")
 * @OA\PathItem(path="/auth")
 */
class AuthController {
    private PDO $pdo;
    private AuthService $authService;
    private ContainerInterface  $container;

    public function __construct(PDO $pdo, ContainerInterface $container, AuthService $authService) {
        $this->pdo = $pdo;
        $this->container = $container;
        $this->authService = $authService;
    }

    /**
     * Registers a new user. Optionally creates a new troop or joins an existing patrol via invite code.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The HTTP request containing registration data.
     * @param \Psr\Http\Message\ResponseInterface $response The HTTP response to return.
     * @param array $args Route arguments (not used here).
     * @return \Psr\Http\Message\ResponseInterface JSON response with the created user.
     *
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nickname", "name", "surname", "email", "password"},
     *             @OA\Property(property="nickname", type="string", example="scoutjoe"),
     *             @OA\Property(property="name", type="string", example="Joe"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="email", type="string", example="joe@example.com"),
     *             @OA\Property(property="password", type="string", example="hunter2"),
     *             @OA\Property(property="new_troop", type="object",
     *                 @OA\Property(property="name", type="string", example="Falcons")
     *             ),
     *             @OA\Property(property="invite_code", type="string", example="a1b2c3d4")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User successfully registered"),
     *     @OA\Response(response=400, description="Missing required troop info"),
     *     @OA\Response(response=409, description="Email already exists")
     * )
     */
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
            $userRepository = $this->container->get(UserRepository::class);

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
            $userRepository->delete($savedUser->getId());
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($savedUser, 201, $response);
    }


    /**
     * Logs a user in by verifying email and password.
     * Returns access token, refresh token and user with roles.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request with login credentials
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response
     * @param array $args Route arguments (not used)
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Log in a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="joe@example.com"),
     *             @OA\Property(property="password", type="string", example="hunter2")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Successfully logged in"),
     *     @OA\Response(response=400, description="Missing credentials"),
     *     @OA\Response(response=401, description="Invalid login")
     * )
     */
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
            $tokenRepository = $this->container->get(RefreshTokenRepository::class);

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
            'user_with_roles' => $userWithRoles,
        ], 200, $response);
    }

    /**
     * Issues a new access token based on a valid refresh token.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request with refresh token
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response
     * @param array $args Route arguments (not used)
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh access token using refresh token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"refresh_token"},
     *             @OA\Property(property="refresh_token", type="string", example="123456789abcdef")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Access token refreshed"),
     *     @OA\Response(response=400, description="Missing refresh token"),
     *     @OA\Response(response=401, description="Invalid or expired refresh token"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
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
            $tokenRepository = $this->container->get(RefreshTokenRepository::class);
            $userId = $tokenRepository->findUserIdByToken($refreshToken);

            if (!$userId) {
                return JsonResponseHelper::jsonResponse('Invalid or expired refresh token', 401, $response);
            }

            // Fetch user
            $userRepository = $this->container->get(UserRepository::class);
            $user = $userRepository->findById($userId);
            if (!$user) {
                return JsonResponseHelper::jsonResponse('User not found', 404, $response);
            }

            // Fetch user roles
            $userRolesService = new UserRolesService($this->pdo);
            $userWithRoles = $userRolesService->loadByUserId($user->getId());

            if (!$userWithRoles) {
                return JsonResponseHelper::jsonResponse('Failed to load user roles.', 500, $response);
            }

            // Generate new access token
            $jwt = $this->authService->generateJWT($user);

            return JsonResponseHelper::jsonResponse([
                'access_token' => $jwt,
                'user_with_roles' => $userWithRoles,
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
        $userRepository = $this->container->get(UserRepository::class);
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
        $troopRepository = $this->container->get(TroopRepository::class);
        $newTroop = $troopRepository->insert($troop->toDatabase());

        // Setting TroopLeader role
        $troopLeaderRepository = $this->container->get(TroopLeaderRepository::class);
        $troopLeader = new TroopLeader([
            "id_user" => $savedUser->getId(),
            "id_troop" => $newTroop->getId()
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
        $gangRepository = $this->container->get(GangRepository::class);
        $gang = $gangRepository->findGangByInviteCode($invite_code);
        if ($gang == null) {
            return false;
        }

        // Setting GangMember role
        $gangMemberRepository = $this->container->get(GangMemberRepository::class);
        $gangMember = new GangMember([
            "id_user" => $savedUser->getId(),
            "id_patrol" => $gang->getId(),
            "active_path_level" => 1,
        ]);

        $gangMemberRepository->insert($gangMember->toDatabase());

        //creating task_progress for each task
        $taskProgressRepository = $this->container->get(TaskProgressRepository::class);
        try {
            $taskProgressRepository->createAllToUser($savedUser->getId());
        } catch (Exception $e) {
            $gangMemberRepository->delete($savedUser->getId());
            throw $e;
        }

        return true;
    }
}