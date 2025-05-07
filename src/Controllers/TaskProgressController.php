<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Exceptions\NotFoundException;
use App\Models\Notification;
use App\Models\Roles\GangMember;
use App\Models\Task;
use App\Models\TaskProgress;
use App\Models\Wrappers\TaskProgressWithTask;
use App\Repository\NotificationRepository;
use App\Repository\Roles\GangLeaderRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Repository\Roles\TroopLeaderRepository;
use App\Repository\TaskProgressRepository;
use App\Repository\TaskRepository;
use App\Repository\TroopRepository;
use App\Repository\UserRepository;
use App\Utils\JsonResponseHelper;
use DateTime;
use Exception;
use Psr\Http\Message\ResponseInterface;

class TaskProgressController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, TaskProgress::class, new TaskProgressRepository($pdo) );
    }

    /**
     * Returns all task progress entries for a specific user in a given troop, including task details.
     *
     * @param Request $request   The HTTP request.
     * @param Response $response The HTTP response.
     * @param array $args        Route parameters: id_user, id_troop.
     * @return ResponseInterface
     *
     * @OA\Get(
     *     path="/troops/{id_troop}/members/{id_user}/task-progresses",
     *     summary="Get all task progresses for a user in a troop",
     *     tags={"Task Progress"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         description="ID of the troop",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of task progresses with task details"),
     *     @OA\Response(response=403, description="User is not a patrol member in the troop"),
     *     @OA\Response(response=500, description="Internal error")
     * )
     */
    public function getUserTaskProgresses($request, $response, $args)
    {
        $userId = (int)($args['id_user'] ?? 0);
        $troopId = (int)($args['id_troop'] ?? 0);

        $troopRepository = new TroopRepository($this->pdo);

        if (!$troopRepository->isUserGangMemberInTroop($userId, $troopId)) {
            throw new Exception("Uživatel není člen družiny v tomto oddíle.", 403);
        }

        $progressArray = $this->repository->findAllByIdUser($userId);
        $taskRepo = new TaskRepository($this->pdo);

        $combined = [];
        foreach ($progressArray as $progress) {
            if($progress->id_confirmed_by != null){ //adding nickname of user that confirmed to response
                $userRepo = new UserRepository($this->pdo);
                $user = $userRepo->findById($progress->id_confirmed_by);
                $progress->confirmed_by_nickname = $user->nickname;
            }

            $task = $taskRepo->findById($progress->id_task);
            $combined[] = new TaskProgressWithTask($progress, $task);
        }

        return JsonResponseHelper::jsonResponse($combined, 200, $response);
    }

    /**
     * Updates a specific task progress entry for a user in a troop.
     *
     * @param Request $request   The HTTP request.
     * @param Response $response The HTTP response.
     * @param array $args        Route parameters: id_troop, id_user, id_task_progress.
     * @return ResponseInterface
     *
     * @OA\Patch(
     *     path="/troops/{id_troop}/members/{id_user}/task-progresses/{id_task_progress}",
     *     summary="Update a task progress for a user in a troop",
     *     tags={"Task Progress"},
     *     @OA\Parameter(name="id_troop", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id_user", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="id_task_progress", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="planned_to", type="string", format="date-time"),
     *             @OA\Property(property="signed_at", type="string", format="date-time"),
     *             @OA\Property(property="confirmed_at", type="string", format="date-time"),
     *             @OA\Property(property="witness", type="string"),
     *             @OA\Property(property="filled_text", type="string"),
     *             @OA\Property(property="id_confirmed_by", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Updated task progress"),
     *     @OA\Response(response=400, description="Invalid input"),
     *     @OA\Response(response=403, description="Unauthorized access"),
     *     @OA\Response(response=404, description="Task progress not found")
     * )
     */
    public function updateUserTaskProgress($request, $response, $args)
    {
        $userId = (int) $args['id_user'];
        $troopId = (int) $args['id_troop'];
        $taskProgressId = (int) $args['id_task_progress'];

        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        foreach (['signed_at', 'confirmed_at', 'planned_to'] as $field) {
            if (!empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = new DateTime($data[$field]);
            }
        }

        if (empty($data)) {
            return JsonResponseHelper::jsonResponse("No data provided", 400, $response);
        }

        $repository = new TaskProgressRepository($this->pdo);
        $taskProgress = $repository->findById($taskProgressId);

        if (!$taskProgress) {
            return JsonResponseHelper::jsonResponse("Task progress not found", 404, $response);
        }

        if ($taskProgress->id_user !== $userId) {
            return JsonResponseHelper::jsonResponse("Unauthorized user", 403, $response);
        }

        $taskRepo = new TaskRepository($this->pdo);
        $task = $taskRepo->findById($taskProgress->id_task);
        if (!$task || ($task->id_troop != null && $task->id_troop !== $troopId)) {
            return JsonResponseHelper::jsonResponse("Task does not belong to the specified troop", 403, $response);
        }

        $oldStatus = $taskProgress->status;
        $taskProgress->setAttributes($data);
        $updated = $repository->update($taskProgressId, $taskProgress->toDatabase());

        // Send notifications only if the status changed from something to "signed"
        $gangMemberRepo = new GangMemberRepository($this->pdo);
        $gangMember = $gangMemberRepo->findById($userId);
        if ($oldStatus !== 'signed' && $taskProgress->status === 'signed') {
            $this->notifyLeadersAboutTaskStatusChange($taskProgress, $gangMember->id_troop);
        }
        if($oldStatus !== 'confirmed' && $taskProgress->status === 'confirmed'){
            $this->notifyMemberAboutTaskStatusChange($taskProgress, $taskProgress->id_confirmed_by);
        }

        return JsonResponseHelper::jsonResponse($updated, 200, $response);
    }

    /**
     * Returns a single task progress entry with task data.
     *
     * @param Request $request   The HTTP request.
     * @param Response $response The HTTP response.
     * @param array $args        Route parameters: id_task_progress.
     * @return ResponseInterface
     *
     * @OA\Get(
     *     path="/task-progresses/{id_task_progress}",
     *     summary="Get a specific task progress by ID",
     *     tags={"Task Progress"},
     *     @OA\Parameter(
     *         name="id_task_progress",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Task progress with task details"),
     *     @OA\Response(response=404, description="Task progress or task not found")
     * )
     */
    public function getUserTaskProgressById($request, $response, $args)
    {
        $taskProgressId = (int)($args['id_task_progress'] ?? 0);

        $taskProgress = $this->repository->findById($taskProgressId);

        if (!$taskProgress) {
            throw new NotFoundException("Progres nenalezen.", 404);
        }

        //adding nickname of user that confirmed to response
        if($taskProgress->id_confirmed_by != null){
            $userRepo = new UserRepository($this->pdo);
            $user = $userRepo->findById($taskProgress->id_confirmed_by);
            $taskProgress->confirmed_by_nickname = $user->nickname;
        }

        $taskRepo = new TaskRepository($this->pdo);
        $task = $taskRepo->findById($taskProgress->id_task);

        if (!$task) {
            throw new NotFoundException("Úkol spojený s tímto progresem nebyl nalezen.", 404);
        }

        $combined = new TaskProgressWithTask($taskProgress, $task);
        return JsonResponseHelper::jsonResponse($combined, 200, $response);
    }



    private function notifyLeadersAboutTaskStatusChange($taskProgress, int $troopId): void
    {
        $gangRepo = new GangMemberRepository($this->pdo);
        $gangLeaderRepo = new GangLeaderRepository($this->pdo);
        $troopLeaderRepo = new TroopLeaderRepository($this->pdo);
        $notificationRepo = new NotificationRepository($this->pdo);

        // Find user's gang (because we need gang leaders too)
        $gangMember = $gangRepo->findById($taskProgress->id_user);
        if (!$gangMember) {
            return; // user is not in a gang
        }

        $gangId = $gangMember->id_patrol;

        // Get all gang leaders
        $gangLeaders = $gangLeaderRepo->findAllByGangId($gangId);

        // Get all troop leaders
        $troopLeaders = $troopLeaderRepo->findAllByTroopId($troopId);

        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($taskProgress->id_task);

        // Prepare notification text
        $text = "Splněný úkol: \"$task->name\".";

        $userRepository = new UserRepository($this->pdo);
        $user = $userRepository->findById($taskProgress->id_user);

        // Merge all leaders (gang + troop)
        $leaders = array_merge($gangLeaders, $troopLeaders);

        // Send notification to each leader
        foreach ($leaders as $leader) {
            $notification = new Notification([
                'id_user_creator' => $taskProgress->id_user,
                'id_user_receiver' => $leader->id_user,
                'text' => $text,
                'creator_name' => $user->nickname,
                'type' => 'task_signed',
                'id_task_progress' => $taskProgress->id_task_progress,
            ]);
            $notificationRepo->insert($notification->toDatabase());
        }
    }

    private function notifyMemberAboutTaskStatusChange($taskProgress, int $leaderId): void
    {
        $container = require __DIR__ . '/../../src/bootstrap.php';
        $userRepo = $container->get(UserRepository::class);
        $leader = $userRepo->findById($leaderId);

        $notificationRepo = new NotificationRepository($this->pdo);


        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($taskProgress->id_task);

        // Prepare notification text
        $text = "Potvzený úkol: \"$task->name\".";

        // Send notification to member
        $notification = new Notification([
            'id_user_creator' => $leader->id_user,
            'id_user_receiver' => $taskProgress->id_user,
            'text' => $text,
            'creator_name' => $leader->nickname,
            'type' => 'task_signed',
            'id_task_progress' => $taskProgress->id_task_progress,
        ]);
        $notificationRepo->insert($notification->toDatabase());

    }

    /**
     * Returns all task progresses for all members of a troop.
     *
     * @param Request $request   The HTTP request.
     * @param Response $response The HTTP response.
     * @param array $args        Route parameters: id_troop.
     * @return ResponseInterface
     *
     * @OA\Get(
     *     path="/troops/{id_troop}/task-progresses",
     *     summary="Get all task progresses for a troop",
     *     tags={"Task Progress"},
     *     @OA\Parameter(
     *         name="id_troop",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of all task progresses with tasks for the troop"),
     *     @OA\Response(response=404, description="Troop not found")
     * )
     */
    public function getTaskProgressesByTroop($request, $response, $args)
    {
        $troopId = (int)($args['id_troop'] ?? 0);

        // Check if troop exists
        $troopRepo = new TroopRepository($this->pdo);
        $troop = $troopRepo->findById($troopId);
        if (!$troop) {
            throw new NotFoundException("Troop not found.", 404);
        }

        //find troop members
        $gangMemberRepo = new GangMemberRepository($this->pdo);
        $gangMembers = $gangMemberRepo->findAllByTroopId($troopId);

        //find progresses
        $taskProgresses = [];
        $taskProgressRepo = new TaskProgressRepository($this->pdo);
        $taskRepo = new TaskRepository($this->pdo);

        foreach ($gangMembers as $member) {
            $progressList = $taskProgressRepo->findAllByIdUser($member->id_user);
            foreach ($progressList as $progress) {
                $task = $taskRepo->findById($progress->id_task);
                if ($task) {
                    $taskProgresses[] = new TaskProgressWithTask($progress, $task);
                }
            }
        }

        return JsonResponseHelper::jsonResponse($taskProgresses, 200, $response);
    }
}