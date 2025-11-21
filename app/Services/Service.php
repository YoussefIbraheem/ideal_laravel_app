<?php
namespace App\Services;
use App\Models\User;

class Service
{
    protected function limitUserVisibility(User $user, $query)
    {
        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhere('assignee_id', $user->id);
        });
    }
}
