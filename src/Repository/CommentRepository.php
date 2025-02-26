<?php

namespace App\Repository;
use App\Models\Comment;
use PDO;
require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * @extends GenericRepository<Comment>
 */
class CommentRepository extends GenericRepository {
    public function __construct(PDO $pdo) {
        parent::__construct($pdo, 'comment', 'id_comment', Comment::class);
    }
}