<?php

namespace App\Controllers;

use App\Models\Roles\TroopLeader;
use App\Repository\Roles\TroopLeaderRepository;
use App\Utils\JsonResponseHelper;

class TroopLeaderController extends CRUDController
{
    public function __construct($pdo)
    {
        parent::__construct($pdo,  TroopLeader::class, new TroopLeaderRepository($pdo));
    }

    public function create($request, $response, $args)
    {
        $data = [];
        $data['id_user'] = $args['id_user'];
        $data['id_troop'] = $args['id_troop'];

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