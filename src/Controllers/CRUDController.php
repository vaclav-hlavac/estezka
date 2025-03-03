<?php

namespace App\Controllers;
use App\Exceptions\DatabaseException;
use App\Models\BaseModel;
use App\Repository\GenericRepository;
use InvalidArgumentException;
use PDO;

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
     * @param class-string<TRepository> $repositoryClass
     */
    public function __construct(PDO $pdo, string $modelClass, string $repositoryClass) {
        $this->pdo = $pdo;
        $this->modelClass = $modelClass;
        $this->repository = new $repositoryClass($this->pdo);
    }

    public function getAll($request, $response, $args) {
        try {
            $foundObjects = $this->repository->findAll();
            return $response->withJson($foundObjects, 200);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
    }

    public function getById($request, $response, $args) {
        try {
            $foundObject = $this->repository->findById($args['id']);
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }


        if ($foundObject) {
            return $response->withJson($foundObject, 200);
        } else {
            return $response->withJson(['message' => 'Object not found'], 404);
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
            return $response->withJson($e->getMessage(), $e->getCode());
        }

        // save + response
        try {
            $savedObject = $this->repository->insert($object->jsonSerialize()); //todo toArray??
            return $response->withJson($savedObject, 201);

        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        try{
            // exist check
            $foundObject = $this->repository->findById($args['id']);
            if ($foundObject == null) {
                return $response->withJson(['message' => 'Object not found'], 404);
            }

            // set new attributes
            $foundObject->setAttributes($data);

            // update
            $updatedObject = $this->repository->update($foundObject->getId(), $foundObject->jsonSerialize()); // todo toArray??

        }
        catch (DatabaseException|InvalidArgumentException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }

        // response
        if ($updatedObject) {
            return $response->withJson($updatedObject, 200);
        } else {
            return $response->withJson(['message' => 'Object not found'], 404);
        }
    }

    public function delete($request, $response, $args) {
        // exist check
        try {
            $foundObject = $this->repository->findById($args['id']);
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        if ($foundObject == null) {
            return $response->withJson(['message' => 'Object not found'], 404);
        }

        // delete + response
        try {
            $this->repository->delete($foundObject->getId());
        }catch (DatabaseException $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withStatus(204);
    }



}