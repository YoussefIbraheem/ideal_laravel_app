<?php

namespace App\Services\User;

use App\Models\User;

class UpdateUser
{
    public function execute($user, array $data)
    {
        $targetUser = User::findOrFail($user->id);

        $targetUser->update($data);

        return $targetUser;
    }
}
