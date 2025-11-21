<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Services\Task\TaskService;

class UpdateTask extends TaskService
{
    public function execute(int $id, array $data)
    {
        $task = Task::findOrFail($id);

        unset($data['status']);

        $task->update($data);

        if (isset($data['assignees_ids'])) {
            $task->assignees()->sync($data['assignees_ids']);
        }

        return $task;
    }
}
