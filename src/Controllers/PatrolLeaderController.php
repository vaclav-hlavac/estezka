<?php

namespace App\Controllers;

use App\Models\Roles\GangLeader;
use App\Repository\Roles\GangLeaderRepository;
use App\Utils\JsonResponseHelper;

class PatrolLeaderController extends CRUDController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo,  GangLeader::class, new GangLeaderRepository($pdo));
    }

    public function create($request, $response, $args)
    {
        $data = [];
        $data['id_user'] = $args['id_user'];
        $data['id_patrol'] = $args['id_patrol'];

        $object = new $this->modelClass($data);

        // save + response
        $savedObject = $this->repository->insert($object->toDatabase());
        return JsonResponseHelper::jsonResponse($savedObject, 201, $response);
    }

    public function delete($request, $response, $args){

        $args['id'] = $args['id_patrol_leader'];
        return parent::delete($request, $response, $args);
    }
}