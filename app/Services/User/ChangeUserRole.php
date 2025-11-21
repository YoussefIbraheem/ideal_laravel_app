<?php

namespace App\Services\User;

use App\Models\User;

class ChangeUserRole
{
    public function execute(User $user, $data)
    {
        $targetUser = User::findOrFail($data['user_id']);

        if ($targetUser->id === $user->id) {
            abort(403, 'You cannot change your own role.');
        }

        if ($targetUser->hasRole('admin')) {
            abort(403, 'You cannot change an admin\'s role.');
        }

        $targetUser->syncRoles([]);
        $targetUser->assignRole($data['role_name']);

        return $targetUser;

    }
}
