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
use App\Repository\TroopRepository;
use App\Repository\UserRepository;
use App\Services\AuthService;
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

        error_log(print_r($data, true));

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
            error_log("jdu deletovat!!!" . $user->jsonSerialize());
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
            return JsonResponseHelper::jsonResponse('Missing nickname or password', 400, $response);
        }

        // Authenticate user
        try {
            $user = $this->authenticateUser($data['email'], $data['password']);
            if($user == null){
                return JsonResponseHelper::jsonResponse('Wrong email or password', 401, $response);
            }
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        // Refresh token
        try {
            $refreshToken = new RefreshToken(['id_user' => $user->getId()]);
            $token_repository = new RefreshTokenRepository($this->pdo);
            $attempts = 0;
            while($token_repository->tokenExists($refreshToken->token)) {
                $refreshToken->generateNewToken();
                $attempts++;
                if ($attempts > 10) { // Protection against too many loops
                    return JsonResponseHelper::jsonResponse('Refresh token could not be generated.', 500, $response);
                }
            }
            $token_repository->insert($refreshToken->jsonSerialize());
        } catch (Exception $e){
            return JsonResponseHelper::jsonResponse('Refresh token could not be generated.', 500, $response);
        }

        // Return response with token
        $jwt = $this->authService->generateJWT($user);
        return JsonResponseHelper::jsonResponse([
            'access_token' => $jwt,
            'refresh_token' => $refreshToken->token,
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
            $token_repository = new RefreshTokenRepository($this->pdo);
            $user_id = $token_repository->findUserIdByToken($refreshToken);

            if (!$user_id) {
                return JsonResponseHelper::jsonResponse('Invalid or expired refresh token', 401, $response);
            }

            // Fetch user
            $user_repository = new UserRepository($this->pdo);
            $user = $user_repository->findById($user_id);
            if (!$user) {
                return JsonResponseHelper::jsonResponse('User not found', 404, $response);
            }

            // Generate new access token
            $jwt = $this->authService->generateJWT($user);

            return JsonResponseHelper::jsonResponse([
                'access_token' => $jwt
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
        $troopRepository->insert($troop->jsonSerialize());

        // Setting TroopLeader role
        $troopLeaderRepository = new TroopLeaderRepository($this->pdo);
        $troopLeader = new TroopLeader([
            "id_user" => $savedUser->getId(),
            "id_troop" => $data['id_troop']
        ]);
        $troopLeaderRepository->insert($troopLeader->jsonSerialize());
    }

    /**
     * @param $invite_code
     * @param User $savedUser
     * @return bool false if no gang found by invite_code
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
            "id_gang" => $gang->getId()
        ]);

        $gangMemberRepository->insert($gangMember->jsonSerialize());

        return true;
    }

    private function f(){

    }


}