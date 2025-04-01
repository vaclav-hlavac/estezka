<?php

namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Models\Troop;
use App\Models\User;
use PDO;
use PDOException;

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Troop>
 */
class TroopRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'troop', 'id_troop', Troop::class);
    }

    public function findAllMembersById(int $id_troop): array {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id_troop");
            $stmt->execute(['id_troop' => $id_troop]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }
        return array_map(fn($row) => $this->hydrateModel($row), $results);
    }

    /**
     * Najde všechny uživatele, kteří patří do troopu (oddílu) a mají roli gang_member.
     * @param int $troopId
     * @return User[]
     * @throws DatabaseException
     */
    public function findAllMembersWithRoleGangMember(int $troopId): array
    {

        $sql = "
                SELECT 
                    u.id_user,
                    u.nickname,
                    u.name,            
                    u.surname,
                    u.password,
                    u.email,
                    u.notifications_enabled,
                
                    gm.active_path_level,
                
                    g.id_gang,
                    g.name AS gang_name,          
                    g.color AS gang_color,
                
                    t.id_troop
                FROM user u
                INNER JOIN gang_member gm ON gm.id_user = u.id_user
                INNER JOIN gang g ON g.id_gang = gm.id_gang
                INNER JOIN troop t ON t.id_troop = g.id_troop
                WHERE t.id_troop = :troopId
        ";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['troopId' => $troopId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new DatabaseException("Database error: " . $e->getMessage(), 500, $e);
        }

        error_log(print_r($results, true));


        return array_map(fn($row) => new User($row), $results);
    }

}