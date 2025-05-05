<?php

namespace App\Controllers;

use App\Models\Roles\GangMember;
use App\Repository\Roles\GangMemberRepository;

class PatrolMemberController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, GangMember::class, new GangMemberRepository($pdo) );
    }

    public function updatePatrolMember($request, $response, $args){

        $args['id'] = $args['id_user'];
        return parent::update($request, $response, $args);
    }

}