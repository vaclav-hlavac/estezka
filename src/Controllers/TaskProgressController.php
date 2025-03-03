<?php

namespace App\Controllers;

use App\Models\TaskProgress;
use App\Repository\TaskProgressRepository;

class TaskProgressController extends CRUDController
{
    public function __construct($pdo) {
        parent::__construct($pdo, TaskProgress::class, TaskProgressRepository::class );
    }

}