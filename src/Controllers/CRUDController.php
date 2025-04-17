<?php

namespace App\Controllers;
use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Models\BaseModel;
use App\Repository\GenericRepository;
use App\Utils\JsonResponseHelper;
use DI\Container;
use InvalidArgumentException;
use PDO;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * @template TModel of BaseModel
 * @template TRepository of GenericRepository
 */
abstract class CRUDController
{

    protected PDO $pdo;

    /** @var class-string<TModel> */
    protected string $modelClass;

    /** @var TRepository */
    protected GenericRepository $repository;

    /**
     * @param PDO $pdo
     * @param class-string<TModel> $modelClass
     * @param GenericRepository $repository
     */
    public function __construct(PDO $pdo, string $modelClass, GenericRepository $repository) {
        $this->pdo = $pdo;
        $this->modelClass = $modelClass;
        $this->repository = $repository;
    }

    public function getAll($request, $response, $args) {
        try {
            $foundObjects = $this->repository->findAll();
            return JsonResponseHelper::jsonResponse($foundObjects, 200, $response);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    public function getById($request, $response, $args) {
        $foundObject = $this->repository->findById($args['id']);


        if ($foundObject) {
            return JsonResponseHelper::jsonResponse($foundObject, 200, $response);
        } else {
            return JsonResponseHelper::jsonResponse('Object not found', 404, $response);
        }
    }

    public function create($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // required arguments check
        try {
            /** @var TModel $object */
            $object = new $this->modelClass($data);
        }catch (InvalidArgumentException $e){
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        // save + response
        try {
            $savedObject = $this->repository->insert($object->toDatabase());
            return JsonResponseHelper::jsonResponse($savedObject, 201, $response);

        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    public function update($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // exist check
        $foundObject = $this->repository->findById($args['id']);
        if ($foundObject == null) {
            throw new NotFoundException('Object not found', 404);
        }

        // set new attributes and update
        $foundObject->setAttributes($data);
        $updatedObject = $this->repository->update($foundObject->getId(), $foundObject->toDatabase());

        // response
        if ($updatedObject) {
            return JsonResponseHelper::jsonResponse($updatedObject, 200, $response);
        } else {
            throw new DatabaseException('Object update failed', 500);
        }
    }

    public function delete($request, $response, $args) {
        // exist check
        try {
            $foundObject = $this->repository->findById($args['id']);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
        if ($foundObject == null) {
            return JsonResponseHelper::jsonResponse('Object not found', 404, $response);
        }

        // delete + response
        try {
            $this->repository->delete($foundObject->getId());
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
        return $response->withStatus(204);
    }



}