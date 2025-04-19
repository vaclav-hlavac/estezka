<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Models\Notification;
use App\Models\Wrappers\NotificationWithPatrolMember;
use App\Models\Wrappers\NotificationWithUser;
use App\Repository\NotificationRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Repository\TaskProgressRepository;
use App\Repository\UserRepository;
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
        $userId = (int) $args['id_user'];

        $notifications = $this->repository->findAllForReceiver($userId);

        $gangMemberRepo = new GangMemberRepository($this->pdo);
        $userRepo = new UserRepository($this->pdo);

        $responses = [];

        // Finding creator - firstly as patrolMember, then at least as user
        foreach ($notifications as $notification) {
            $patrolMember = $gangMemberRepo->findById($notification->id_user_creator);

            if ($patrolMember !== null) {
                // User is a Patrol Member
                $responses[] = new NotificationWithPatrolMember($notification, $patrolMember);
            } else {
                // User is not a Patrol Member
                $user = $userRepo->findById($notification->id_user_creator);
                $responses[] = new NotificationWithUser($notification, $user);
            }
        }

        return JsonResponseHelper::jsonResponse($responses, 200, $response);
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
        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        $id = (int) $args['id_notification'];

        $notification = $this->repository->findById($id);
        if (!$notification) {
            throw new NotFoundException('Notification not found', 404);
        }

        $notification->setAttributes($data);
        $updated = $this->repository->update($notification->getId(), $notification->toDatabase());

        return JsonResponseHelper::jsonResponse($updated, 200, $response);

    }
}