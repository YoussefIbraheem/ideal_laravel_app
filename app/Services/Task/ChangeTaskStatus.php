<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\User;
use App\Enums\UserRole;
use App\Policies\TaskPolicy;
use App\Services\Service;

class ChangeTaskStatus extends Service
{
    public function execute(int $id, array $data, User $user)
    {

        $task = Task::with(['dependencies', 'dependents'])->findorFail($id);

        $isUser = $user->hasRole(UserRole::USER);
        $unclosedDependents = $this->checkDependents($task);

        $user->can('update', $task) || abort(403);

        if ($isUser && $unclosedDependents) {
            abort(422, 'Action cannot be taken, please check for unclosed dependent tasks');
        }

        $task->update([
            'status' => $data['status'],
        ]);

        return $task;
    }
}
