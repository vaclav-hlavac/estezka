<?php

namespace App\Services;

use App\Exceptions\DatabaseException;
use App\Repository\Roles\GangLeaderRepository;
use App\Repository\Roles\TroopLeaderRepository;

class AccessService
{

    private TroopLeaderRepository $troopLeaderRepository;
    private GangLeaderRepository $gangLeaderRepository;

    public function __construct(
        TroopLeaderRepository $troopLeaderRepository,
        GangLeaderRepository $gangLeaderRepository
    ) {
        $this->troopLeaderRepository = $troopLeaderRepository;
        $this->gangLeaderRepository = $gangLeaderRepository;
    }

    /**
     * Checks if the user has access to a troop.
     *
     * @param int $userId The ID of the user.
     * @param int $troopId The ID of the troop.
     * @return bool True if the user has access, false otherwise.
     * @throws DatabaseException
     */
    public function hasAccessToTroop(int $userId, int $troopId): bool
    {
        return $this->troopLeaderRepository->isUserTroopLeaderOfTroop($userId, $troopId);
    }

    /**
     * Checks if the user has access to a gang.
     * The user can have access either as a troop leader or as a gang leader.
     *
     * @param int $userId The ID of the user.
     * @param int $gangId The ID of the gang.
     * @return bool True if the user has access, false otherwise.
     * @throws DatabaseException
     */
    public function hasAccessToGang(int $userId, int $gangId): bool
    {
        return $this->troopLeaderRepository->isUserTroopLeaderWithGang($userId, $gangId) ||
            $this->gangLeaderRepository->isUserGangLeaderOfGang($userId, $gangId);
    }

}