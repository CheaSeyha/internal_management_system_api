<?php

namespace App\Repository;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $profileImagePath = null;

        if (isset($data['profile_image'])) {
            $file = $data['profile_image'];

            // Sanitize name/email for file usage
            $namePart = Str::slug($data['name']); // convert spaces/special chars to hyphens
            $emailPart = Str::before($data['email'], '@'); // take email before @

            $extension = $file->getClientOriginalExtension(); // e.g., jpg, png
            $filename = "{$emailPart}_{$namePart}.{$extension}";

            // Store in public/profile_images with custom name
            $profileImagePath = $file->storeAs('profile_images', $filename, 'public');

            // Get full URL
            $profileImagePath = Storage::url($profileImagePath);
        }

        $createdUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'profile_image' => $profileImagePath,
            'password' => bcrypt($data['password']),
        ]);

        return $createdUser;
    }
}
