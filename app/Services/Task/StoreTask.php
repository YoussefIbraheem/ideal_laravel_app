<?php

namespace App\Services\Task;

use App\Models\User;

use App\Services\Task\TaskService;

class StoreTask extends TaskService
{
    public function execute(
        array $data,
        User $user
    ) {
        $task = $user->createdTasks()->create($data);

        if (isset($data['assignees_ids'])) {

            if (isset($data['assignees_ids'])) {
                $task->assignees()->syncWithoutDetaching($data['assignees_ids']);
            }
        }

        return $task;
    }
}
