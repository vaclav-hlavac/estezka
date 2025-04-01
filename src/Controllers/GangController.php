<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Models\Gang;
use App\Repository\GangRepository;
use App\Utils\JsonResponseHelper;

class GangController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, Gang::class, new GangRepository($pdo) );
    }

    public function getGangMembers(){
        //todo
    }

    public function checkInviteCode($request, $response, $args)
    {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        //Arguments control
        if (!isset($data['invite_code'])) {
            return JsonResponseHelper::jsonResponse('Missing required field: invite_code', 400, $response);
        }

        $gangRepository = new GangRepository($this->pdo);
        try {
            $gang = $gangRepository->findGangByInviteCode($data['invite_code']);
            if (!$gang) {
                return JsonResponseHelper::jsonResponse('The code does not belong to any gang.', 400, $response);
            }
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($gang, 200, $response);
    }

}