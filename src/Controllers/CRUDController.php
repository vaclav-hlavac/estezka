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
 * Abstract base controller providing generic CRUD operations.
 *
 * This controller is meant to be extended by specific resource controllers,
 * and it operates on a given model and repository pair.
 *
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

    /**
     * Returns all records of the given model.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @param array $args Route arguments (unused).
     * @return \Psr\Http\Message\ResponseInterface JSON response with array of records.
     */
    public function getAll($request, $response, $args) {
        try {
            $foundObjects = $this->repository->findAll();
            return JsonResponseHelper::jsonResponse($foundObjects, 200, $response);
        }catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    /**
     * Retrieves a single record by its ID.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @param array $args Must include 'id' as route argument.
     * @return \Psr\Http\Message\ResponseInterface JSON response with object or 404.
     */
    public function getById($request, $response, $args) {
        $foundObject = $this->repository->findById($args['id']);


        if ($foundObject) {
            return JsonResponseHelper::jsonResponse($foundObject, 200, $response);
        } else {
            return JsonResponseHelper::jsonResponse('Object not found', 404, $response);
        }
    }

    /**
     * Creates a new record from request body.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @param array $args Route arguments (optional).
     * @return \Psr\Http\Message\ResponseInterface JSON response with created object.
     */
    public function create($request, $response, $args) {
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        // required arguments check
        /** @var TModel $object */
        $object = new $this->modelClass($data);

        // save + response
        $savedObject = $this->repository->insert($object->toDatabase());
        return JsonResponseHelper::jsonResponse($savedObject, 201, $response);
    }

    /**
     * Updates an existing record by ID with data from request body.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @param array $args Must include 'id' as route argument.
     * @return \Psr\Http\Message\ResponseInterface JSON response with updated object.
     * @throws NotFoundException If object with given ID does not exist.
     * @throws DatabaseException If the update fails.
     */
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

    /**
     * Deletes a record by its ID.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP request.
     * @param \Psr\Http\Message\ResponseInterface $response HTTP response.
     * @param array $args Must include 'id' as route argument.
     * @return \Psr\Http\Message\ResponseInterface 204 No Content on success, or 404.
     */
    public function delete($request, $response, $args) {
        // exist check

        $foundObject = $this->repository->findById($args['id']);

        if ($foundObject == null) {
            return JsonResponseHelper::jsonResponse('Object not found', 404, $response);
        }

        // delete + response
        $this->repository->delete($foundObject->getId());

        return $response->withStatus(204);
    }



}