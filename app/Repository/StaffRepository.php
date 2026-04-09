<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;
use App\Models\Staff;
use App\Repository\AuthRepository;   // <-- import it
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        $createdUser = null;



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
            'status'          => "active",
            'date_of_joining' => $staff_data['date_of_joining'],
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

        // 2️⃣ Create linked User account if isCreatedUser is true
        $isCreatedUser = !empty($staff_data['isCreatedUser']); // true only when present and truthy

        if ($isCreatedUser) {
            $createdUser = $this->authRepo->createUser([
                'name'          => $staff_data['first_name'] . ' ' . $staff_data['last_name'],
                'staff_id'      => $staff->id,
                'email'         => $staff_data['email'],
                'role_id'       => $role_id,
                'password'      => $staff_data['password'],
                'profile_image' => $staff_data['profile_picture'],
            ]);

            $staff->load([
                'department:id,department_name',
                'position:id,position_name'
            ]);

            $createdUser->load([
                'role:id,role_name'
            ]);
        }


        // 4️⃣ Prepare return data
        $data = [
            'staff' => $staff,
            'user'  => $createdUser,
        ];

        return $data;
    }

    public function update_staff($staff, $staff_data, $department_id, $position_id)
    {
        $fillable = ['staff_id', 'first_name', 'last_name', 'label_id', 'genders', 'email', 'phone_number', 'date_of_joining', 'date_of_birth'];
        
        foreach ($fillable as $field) {
            if (isset($staff_data[$field])) {
                $staff->{$field} = $staff_data[$field];
            }
        }

        if ($department_id) {
            $staff->department_id = $department_id;
        }

        if ($position_id) {
            $staff->position_id = $position_id;
        }

        if (isset($staff_data['profile_picture'])) {
            $file = $staff_data['profile_picture'];
            
            if ($staff->profile_picture && Storage::disk('private')->exists($staff->profile_picture)) {
                Storage::disk('private')->delete($staff->profile_picture);
            }

            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';
            $filename = 'staff_' . Str::slug($staff->first_name . '_' . $staff->last_name) . '.' . $extension;
            $path = $file->storeAs('staff/profile_pictures', $filename, 'private');
            $staff->profile_picture = $path;
        }

        $staff->save();
        
        $staff->load(['department', 'position', 'user']);
        return $staff;
    }

    public function searchStaff($query)
    {
        return Staff::with(['department', 'position', 'user'])
            ->where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('staff_id', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->latest()
            ->paginate(12);
    }

    public function deleteStaffs($staff_ids)
    {
        $staffs = Staff::whereIn('id', $staff_ids)->get();
        foreach ($staffs as $staff) {
            if ($staff->profile_picture && Storage::disk('private')->exists($staff->profile_picture)) {
                Storage::disk('private')->delete($staff->profile_picture);
            }
            if ($staff->user) {
                // Also optionally delete the user's profile image if it exists and is separate from staff
                $staff->user->delete();
            }
            $staff->delete();
        }
        return true;
    }
}
