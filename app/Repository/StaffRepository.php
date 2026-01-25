<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;
use App\Models\Staff;
use App\Repository\AuthRepository;   // <-- import it
use Illuminate\Support\Str;

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
        // 1️⃣ Create staff record




        $staff = Staff::create([
            'staff_id'      => $staff_data['staff_id'],
            'first_name'      => $staff_data['first_name'],
            'last_name'       => $staff_data['last_name'],
            'label_id'        => $staff_data['label_id'],
            'genders'         => $staff_data['genders'],
            'email'           => $staff_data['email'],
            'phone_number'    => $staff_data['phone_number'] ?? null,
            'position_id'     => $position_id,
            'department_id'   => $department_id,
            'status'          => $staff_data['status'],
            'date_of_joining' => today(),
            'date_of_birth'   => $staff_data['date_of_birth'],
        ]);


        if (isset($staff_data['profile_picture'])) {
            $file = $staff_data['profile_picture'];

            // Get safe extension
            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';

            // Create safe filename
            $filename = 'staff_' .
                Str::slug($staff_data['first_name'] . '_' . $staff_data['last_name']) .
                '.' . $extension;

            // Store file
            $path = $file->storeAs(
                'staff/profile_pictures',
                $filename,
                'private'
            );

            // Save result
            $staff->profile_picture = $path;
        }


        $staff->save();

        // 2️⃣ Create linked User account
        $createdUser = $this->authRepo->createUser([
            'name'          => $staff_data['first_name'] . ' ' . $staff_data['last_name'],
            'staff_id'      => $staff->id,
            'email'         => $staff_data['email'],
            'role_id'       => $role_id,
            'password'      => $staff_data['password'],
            'profile_image' => $staff_data['profile_image'] ?? null,
        ]);

        // 3️⃣ Load Department and Position relationships
        $staff->load([
            'department:id,department_name', // only select id and name
            'position:id,position_name'
        ]);

        $createdUser->load([
            'role:id,role_name'
        ]);

        // 4️⃣ Prepare return data
        $data = [
            'staff' => $staff,
            'user'  => $createdUser,
        ];

        return $data;
    }
}
