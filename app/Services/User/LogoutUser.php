<?php

namespace App\Services\User;

class LogoutUser
{
    public function execute($user)
    {
        $user->currentAccessToken()->delete();

        return true;
    }
}
