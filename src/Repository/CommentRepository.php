<?php

namespace App\Repository;
use App\Models\Comment;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Repository for managing comments stored in the `comment` table.
 *
 * Provides basic CRUD operations for the `Comment` model via the GenericRepository.
 *
 * @extends GenericRepository<Comment>
 */
class CommentRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'comment', 'id_comment', Comment::class);
    }
}