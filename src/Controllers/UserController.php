<?php

namespace App\Controllers;
use App\Models\User;

require_once __DIR__ . '/../../vendor/autoload.php';

class UserController
{
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Získat všechny uživatele",
     *     @OA\Response(response="200", description="Seznam uživatelů")
     * )
     */
    public function getAllUsers($request, $response, $args) {
        $users = User::all($this->pdo);
        $response->getBody()->write(json_encode($users));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Získat konkrétního usera",
     *     tags={"Users"},
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Popis usera"),
     *     @OA\Response(response="401", description="User nenalezen")
     * )
     */
    public function getTroop($request, $response, $args) {
        $user = User::find($this->pdo, $args['id']);
        if ($user) {
            $response->getBody()->write(json_encode($user));
            return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['message' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

}