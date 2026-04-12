<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthRepository
{
    public function __construct() {}

    public function checkEmailExists($email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function createUser($data): ?User
    {
        $profileImagePath = null;

        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];

            if (is_object($file) && method_exists($file, 'getClientOriginalExtension')) {
                $extension = $file->getClientOriginalExtension();
                $filename  = "{$data['name']}.{$extension}";
                $storedPath = $file->storeAs('user_profile', $filename, 'private');
                $profileImagePath = $storedPath;
            } else {
                // It's already a path string (e.g., from existing staff profile)
                $profileImagePath = $file;
            }
        }

        return User::create([
            'name'          => $data['name'],
            'staff_id'      => $data['staff_id'],
            'email'         => $data['email'],
            'role_id'       => $data['role_id'],
            'profile_image' => $profileImagePath,
            'account_status' => 'active',
            'password'      => bcrypt($data['password']),
        ]);
    }
}
