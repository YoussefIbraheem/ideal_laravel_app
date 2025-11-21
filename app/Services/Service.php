<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Enums\TaskStatus;
use App\Enums\UserRole;

class Service
{
    protected function limitUserVisibility(User $user, $query)
    {
        if ($user->hasAnyRole([UserRole::ADMIN, UserRole::MANAGER])) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('assignees', function ($assigneeQuery) use ($user) {
                $assigneeQuery->where('user_id', $user->id);
            })->orWhere('owner_id', $user->id);
        });
    }

    protected function checkDependents(Task $task): bool
    {
        $dependents = $task->dependents()->get();

        if ($dependents->count() == 0) {
            return false;
        }

        foreach ($dependents as $dependent) {
            if ($dependent->status == TaskStatus::PENDING || $dependent->status == TaskStatus::IN_PROGRESS) {
                return true;
            }
        }

        return false;
    }

}
