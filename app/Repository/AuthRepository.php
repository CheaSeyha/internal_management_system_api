<?php

namespace App\Repository;
use App\Models\User;
class AuthRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function checkEmailExists($email)
    {
        // Check if user email exists
        $user = User::where('email', $email)->first();
        return $user ? true : false;
    }

    public function createUser($data)
    {
        $createdUser = User::create([
            'name' => $data->name,
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        return $createdUser;
    }

}
