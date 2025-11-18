<?php

namespace App\Repository;

use App\Models\Staff;
use App\Repository\AuthRepository;   // <-- import it

use function Symfony\Component\Clock\now;

class StaffRepository
{
    protected $authRepo;

    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;   // dependency injection
    }

    public function add_staff($staff_data, $department_id, $position_id, $role_id)
    {
        // $loggedInUser = auth()->user();

        // // If not logged in
        // if (!$loggedInUser) {
        //     throw new \Exception("Unauthorized", 401);
        // }

        // // Only allow: 1 = Super Admin, 2 = Admin
        // if (!in_array($loggedInUser->role_id, [1, 2])) {
        //     throw new \Exception("Permission denied", 403);
        // }

        // Create staff record
        $staff = Staff::create([
            'first_name'      => $staff_data["first_name"],
            'last_name'       => $staff_data["last_name"],
            'email'           => $staff_data["email"],
            'phone_number'    => $staff_data["phone_number"] ?? null,
            'position_id'     => $position_id,
            'department_id'   => $department_id,
            'status'          => $staff_data["status"],
            'date_of_joining' => now(),
            'date_of_birth'   => $staff_data["date_of_birth"],
            'profile_picture' => $staff_data["profile_picture"] ?? null,
        ]);

        // Create linked User
        $createdUser = $this->authRepo->createUser([
            'name'          => $staff_data['first_name'] . ' ' . $staff_data['last_name'],
            'staff_id'      => $staff->id,
            'email'         => $staff_data['email'],
            'role_id'       => $role_id,
            'password'      => $staff_data['password'],
            'profile_image' => $staff_data['profile_image'] ?? null,
        ]);

        return $staff; // Repository only returns the data
    }
}
