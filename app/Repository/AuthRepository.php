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
        return User::where('email', $email)->exists(); // ✅ cleaner than first() check
    }

    public function createUser($data): ?User
    {
        $profileImagePath = null;

        if (isset($data['profile_image'])) {
            $file      = $data['profile_image'];
            $namePart  = Str::slug($data['name']);
            $extension = $file->getClientOriginalExtension();
            $filename  = "{$namePart}.{$extension}";

            $storedPath       = $file->storeAs('profile_images', $filename, 'public');
            $profileImagePath = Storage::url($storedPath);
        }

        return User::create([
            'name'          => $data['name'],
            'staff_id'      => $data['staff_id'],
            'email'         => $data['email'],
            'role_id'       => $data['role_id'],
            'profile_image' => $profileImagePath,
            'password'      => bcrypt($data['password']),
        ]);
    }
}
