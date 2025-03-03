<?php

namespace App\Controllers;

use App\Models\Gang;
use App\Repository\GangRepository;

class GangController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, Gang::class, GangRepository::class );
    }

    public function getGangMembers(){

    }

}