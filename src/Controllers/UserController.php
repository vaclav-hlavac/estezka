<?php

namespace App\Controllers;
use App\Models\User;
use App\Repository\UserRepository;

require_once __DIR__ . '/../../vendor/autoload.php';

class UserController extends CRUDController
{

    public function __construct($pdo) {
        parent::__construct($pdo, User::class, new UserRepository($pdo) );
    }
}