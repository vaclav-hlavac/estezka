<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Models\Notification;
use App\Models\Wrappers\NotificationResponse;
use App\Repository\NotificationRepository;
use App\Repository\TaskProgressRepository;
use App\Utils\JsonResponseHelper;
use InvalidArgumentException;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotificationController
{
    private NotificationRepository $repository;
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->repository = new NotificationRepository($pdo);
        $this->pdo = $pdo;
    }

    /**
     * Get all notifications received by a user
     * GET /users/{id_user}/notifications
     */
    public function getAllForUser(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = (int) $args['id_user'];
            $notifications = $this->repository->findAllForReceiver($userId);

            $taskProgressRepo = new TaskProgressRepository($this->pdo);
            $responses = [];

            foreach ($notifications as $notification) {
                $taskProgress = $notification->id_task_progress
                    ? $taskProgressRepo->findById($notification->id_task_progress)
                    : null;

                $responses[] = new NotificationResponse($notification, $taskProgress);
            }

            return JsonResponseHelper::jsonResponse($responses, 200, $response);
        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }

    /**
     * Create a new notification
     * POST /notifications
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();

        try {
            $notification = new Notification($data);
            $created = $this->repository->insert($notification->toDatabase());
            return JsonResponseHelper::jsonResponse($created, 201, $response);
        } catch (DatabaseException|InvalidArgumentException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }


    /**
     * Update a notification – e.g. mark as received.
     * PATCH /notifications/{id_notification}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id_notification'];
        $data = $request->getParsedBody();

        try {
            $notification = $this->repository->findById($id);

            if (!$notification) {
                return JsonResponseHelper::jsonResponse("Notification not found", 404, $response);
            }

            $notification->setAttributes($data);

            $updated = $this->repository->update($notification->getId(), $notification->toDatabase());

            return JsonResponseHelper::jsonResponse($updated, 200, $response);
        } catch (DatabaseException|InvalidArgumentException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }
    }
}