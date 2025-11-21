<?php

namespace App\Services\Task;

use App\Models\Task;
use App\Models\User;
use App\Services\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class ListTasks extends Service
{

    public function execute(
        User $user,
        string $status,
        string $title,
        int $owner_id,
        int $assignee_id,
        string $due_date_from,
        string $due_date_to,
        int $per_page = 15,
    ): LengthAwarePaginator {
        $query = Task::query();

        $query = $this->limitUserVisibility($user, $query);

        // Apply filters based on provided parameters
        if ($status) {
            $query->where('status', $status);
        }

        if ($title) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if ($owner_id) {
            $query->where('owner_id', $owner_id);
        }

        if ($assignee_id) {
            $query->whereHas('assignees', function ($q) use ($assignee_id) {
                $q->where('user_id', $assignee_id);
            });
        }

        if ($due_date_from) {
            $query->where('due_date', '>=', $due_date_from);
        }

        if ($due_date_to) {
            $query->where('due_date', '<=', $due_date_to);
        }

        return $query->paginate($per_page);
    }
}
