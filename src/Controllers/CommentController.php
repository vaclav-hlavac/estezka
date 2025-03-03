<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Repository\CommentRepository;

class CommentController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, Comment::class, CommentRepository::class );
    }
}