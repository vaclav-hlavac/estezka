<?php

namespace App\Controllers;

use App\Models\Comment;
use App\Repository\CommentRepository;
use Psr\Container\ContainerInterface;

class CommentController extends CRUDController
{
    public function __construct($pdo, ContainerInterface $container) {
        $commentRepository = $container->get(CommentRepository::class);
        parent::__construct($pdo, $container, Comment::class, $commentRepository);
    }
}