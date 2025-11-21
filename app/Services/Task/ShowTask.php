<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\User;
use App\Services\Task\TaskService;

class ShowTask extends TaskService
{
    public function execute(int $id , User $user)
    {
        $query = Task::query();

        $query = Task::query();

        $query = $this->limitUserVisibility($user, $query);

        $task = $query->find($id);

        if (! $task) {
            abort(404, 'Task not found!');
        }

        return $task;

    }
}
