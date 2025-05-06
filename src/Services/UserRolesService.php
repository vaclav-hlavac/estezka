<?php

namespace App\Services;

use App\Models\User;
use App\Models\Roles\GangMember;
use App\Models\Roles\GangLeader;
use App\Models\Roles\TroopLeader;
use App\Models\Wrappers\UserWithRoles;
use App\Repository\UserRepository;
use App\Repository\Roles\GangMemberRepository;
use App\Repository\Roles\GangLeaderRepository;
use App\Repository\Roles\TroopLeaderRepository;
use App\Exceptions\DatabaseException;
use DI\NotFoundException;
use PDO;

/**
 * Service responsible for loading a user along with all their assigned roles.
 *
 * It aggregates role data from various role-specific repositories (e.g. gang member, gang leader, troop leader)
 * and wraps it together with the base user entity into a single `UserWithRoles` object.
 */
class UserRolesService
{
    private UserRepository $userRepository;
    private GangMemberRepository $gangMemberRepository;
    private GangLeaderRepository $gangLeaderRepository;
    private TroopLeaderRepository $troopLeaderRepository;

    public function __construct(PDO $pdo)
    {
        $this->userRepository = new UserRepository($pdo);
        $this->gangMemberRepository = new GangMemberRepository($pdo);
        $this->gangLeaderRepository = new GangLeaderRepository($pdo);
        $this->troopLeaderRepository = new TroopLeaderRepository($pdo);
    }

    /**
     * Load a user with all their roles.
     *
     * Retrieves the user entity by ID and attempts to load all role information associated with the user.
     * Roles include GangMember (optional), GangLeader (can be multiple), and TroopLeader (can be multiple).
     *
     * @param int $userId The ID of the user to load.
     * @return UserWithRoles|null Returns a wrapper containing the user and their roles, or null if the user does not exist.
     * @throws DatabaseException If a database error occurs during any of the queries.
     * @throws NotFoundException If the user with the given ID is not found.
     */
    public function loadByUserId(int $userId): ?UserWithRoles
    {
        // 1. Load user
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found', 404);
        }

        // 2. Load optional roles
        $gangMember = $this->gangMemberRepository->findById($userId); // Can be null
        $gangLeaders = $this->gangLeaderRepository->findAllByUserId($userId); // Array
        $troopLeaders = $this->troopLeaderRepository->findAllByUserId($userId); // Array


        // 3. Return wrapped object
        return new UserWithRoles(
            $user,
            $gangMember,
            $gangLeaders,
            $troopLeaders
        );
    }
}