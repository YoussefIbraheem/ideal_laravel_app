<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Services\Task\TaskService;

class AddTaskDependents extends TaskService
{
    public function execute(int $id, array $data)
    {
        $task = Task::findOrFail($id);

        foreach ($data['dependent_tasks_ids'] as $dependentId) {
            $dependent = Task::find($dependentId);

            if ($dependent->id === $task->id) {
                abort(422, 'You cannot make the task dependent on itself');
            }
        }

        $task->dependents()->sync($data['dependent_tasks_ids']);

        return $task;
    }
}
