<?php

namespace App\Repository\Roles;

use App\Models\Roles\GangMember;
use App\Repository\GenericRepository;
use PDO;

class GangMemberRepository extends GenericRepository
{
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'gang_member', 'id_user', GangMember::class);
    }

    public function findAllByGangId(int $gangId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id_gang = ?");
        $stmt->execute([$gangId]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

}