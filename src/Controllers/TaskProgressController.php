<?php

namespace App\Controllers;

use App\Exceptions\DatabaseException;
use App\Models\Notification;
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
use Psr\Http\Message\ResponseInterface;

class TaskProgressController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, TaskProgress::class, new TaskProgressRepository($pdo) );
    }

    /**
     * Returns all task progress entries for a specific user,
     * including the corresponding task data for each progress.
     * The user must be a patrol member in the specified troop; otherwise, a 403 error is returned.
     *
     * @param Request $request   The HTTP request object.
     * @param Response $response The HTTP response object.
     * @param array $args        Route parameters containing 'id_user' and 'id_troop'.
     *
     * @return ResponseInterface JSON response containing an array of task progress records
     *                           with associated task details, or an error message.
     */
    public function getUserTaskProgress($request, $response, $args)
    {
        $userId = (int)($args['id_user'] ?? 0);
        $troopId = (int)($args['id_troop'] ?? 0);

        try {
            $troopRepository = new TroopRepository($this->pdo);

            if (!$troopRepository->isUserGangMemberInTroop($userId, $troopId)) {
                return JsonResponseHelper::jsonResponse("Uživatel není člen družiny v tomto oddíle.", 403, $response);
            }


            $progressArray = $this->repository->findAllByIdUser($userId);
            $taskRepo = new TaskRepository($this->pdo);

            $combined = [];
            foreach ($progressArray as $progress) {
                $task = $taskRepo->findById($progress->id_task);
                $combined[] = new TaskProgressWithTask($progress, $task);
            }

        } catch (DatabaseException $e) {
            return JsonResponseHelper::jsonResponse($e->getMessage(), $e->getCode(), $response);
        }

        return JsonResponseHelper::jsonResponse($combined, 200, $response);
    }

    /**
     * Updates an existing TaskProgress record for a given user and troop.
     * Validates that the task progress belongs to the user and that the task is part of the specified troop.
     *
     * Endpoint: PATCH /troops/{id_troop}/members/{id_user}/task-progresses/{id_task_progress}
     *
     * @param $request  Request Request
     * @param $response Response Response
     * @param $args     array arguments (id_troop, id_user, id_task_progress)
     * @return ResponseInterface JSON response with updated object or error
     */
    public function updateUserTaskProgress($request, $response, $args)
    {
        $userId = (int) $args['id_user'];
        $troopId = (int) $args['id_troop'];
        $taskProgressId = (int) $args['id_task_progress'];

        $data = $request->getParsedBody();
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
        if (!$task || $task->id_troop !== $troopId) {
            return JsonResponseHelper::jsonResponse("Task does not belong to the specified troop", 403, $response);
        }

        $oldStatus = $taskProgress->status;
        $taskProgress->setAttributes($data);
        $updated = $repository->update($taskProgressId, $taskProgress->toDatabase());

        // Send notifications only if the status changed from something to "signed"
        if ($oldStatus !== 'signed' && $taskProgress->status === 'signed') {
            $this->notifyLeadersAboutTaskStatusChange($taskProgress, $task->id_troop);
        }

        return JsonResponseHelper::jsonResponse($updated, 200, $response);
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

        $gangId = $gangMember->id_gang;

        // Get all gang leaders
        $gangLeaders = $gangLeaderRepo->findAllByGangId($gangId);

        // Get all troop leaders
        $troopLeaders = $troopLeaderRepo->findAllByTroopId($troopId);

        $taskRepository = new TaskRepository($this->pdo);
        $task = $taskRepository->findById($taskProgress->id_task);

        // Prepare notification text
        $text = "Splněný úkol: \"$task->title\".";

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

}