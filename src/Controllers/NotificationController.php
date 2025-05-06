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

/**
 * @OA\Tag(name="Notifications", description="Manage notifications")
 * @OA\PathItem(path="/notifications")
 */
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
     * Retrieves all notifications received by the specified user.
     *
     * @param Request $request PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array $args Contains 'id_user'
     * @return Response JSON response with notifications wrapped in creator info
     *
     * @OA\Get(
     *     path="/users/{id_user}/notifications",
     *     summary="Get all notifications for a user",
     *     tags={"Notifications"},
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of notifications")
     * )
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
     * Creates a new notification.
     *
     * @param Request $request PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array $args
     * @return Response JSON with created notification or error
     *
     * @OA\Post(
     *     path="/notifications",
     *     summary="Create a new notification",
     *     tags={"Notifications"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_user_creator", "id_user_receiver", "text"},
     *             @OA\Property(property="id_user_creator", type="integer", example=1),
     *             @OA\Property(property="id_user_receiver", type="integer", example=2),
     *             @OA\Property(property="text", type="string", example="Task completed"),
     *             @OA\Property(property="id_task_progress", type="integer", example=5),
     *             @OA\Property(property="type", type="string", example="task_status_change")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Notification created"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=500, description="Database error")
     * )
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
     * Updates a notification (e.g. marking it as received).
     *
     * @param Request $request PSR-7 request object
     * @param Response $response PSR-7 response object
     * @param array $args Contains 'id_notification'
     * @return Response JSON with updated notification
     *
     * @OA\Patch(
     *     path="/notifications/{id_notification}",
     *     summary="Update a notification",
     *     tags={"Notifications"},
     *     @OA\Parameter(
     *         name="id_notification",
     *         in="path",
     *         required=true,
     *         description="ID of the notification",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="was_received", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Notification updated"),
     *     @OA\Response(response=404, description="Notification not found")
     * )
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