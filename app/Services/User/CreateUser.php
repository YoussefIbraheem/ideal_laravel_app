<?php

namespace App\Services\User;

use App\Models\User;
use App\Enums\UserRole;

class CreateUser
{
    public function execute(array $data)
    {

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole(UserRole::USER);

        return $user;
    }
}
