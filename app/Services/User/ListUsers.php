<?php

namespace App\Services\User;

use App\Models\User;

class ListUsers
{
    public function execute()
    {
        return User::all();
    }
}
