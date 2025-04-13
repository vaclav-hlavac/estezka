<?php

namespace App\Controllers;

use App\Models\Roles\GangLeader;
use App\Repository\Roles\GangLeaderRepository;

class GangLeaderController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, GangLeader::class, new GangLeaderRepository($pdo) );
    }

    public function addPatrolLeader($request, $response, $args) //todo test and documentation
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        $data['id_patrol'] = $args['id_patrol'];
        $request = $request->withParsedBody($data);

        return parent::create($request, $response, $args);

    }

}