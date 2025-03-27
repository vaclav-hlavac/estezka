<?php

namespace App\Repository\Roles;

use App\Exceptions\DatabaseException;
use App\Models\Roles\GangMember;
use App\Repository\GenericRepository;
use PDO;
use PDOException;

class GangMemberRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang_member', 'id_user', GangMember::class);
    }

    /**
     * @param int $gangId
     * @return array
     * @throws DatabaseException
     */
    public function findAllByGangId(int $gangId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_gang = ?");

        try{
            $stmt->execute([$gangId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }
}