<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
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
        $createdUser = null;

        $staff = Staff::create([
            'staff_id'      => $staff_data['staff_id'],
            'first_name'      => $staff_data['first_name'],
            'last_name'       => $staff_data['last_name'],
            'label_id'        => $staff_data['label_id'],
            'genders'         => $staff_data['gender'],
            'email'           => $staff_data['email'],
            'phone_number'    => $staff_data['phone_number'] ?? null,
            'position_id'     => $position_id,
            'department_id'   => $department_id,
            'date_of_joining' => $staff_data['date_of_joining'],
            'date_of_birth'   => $staff_data['date_of_birth'],
        ]);


        if (isset($staff_data['profile_picture'])) {
            $file = $staff_data['profile_picture'];

            // Get safe extension
            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg';

            // Create safe filename
            $filename = $staff_data['staff_id'] . '.' . $extension;

            // Store file
            $path = $file->storeAs(
                'staff/profile_pictures',
                $filename,
                'private'
            );

            // Save result
            $staff->profile_picture = 'staff/profile_pictures/' . $staff_data['staff_id'] . '.' . $extension;
        }


        $staff->save();

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

        $data = [
            'staff' => $staff,
            'user'  => $createdUser,
        ];

        return $data;
    }

    public function update_staff($staff_id, $staff_data, $department_id, $position_id)
    {
        try {
            $staff = Staff::where('staff_id', $staff_id)->first();

            if (! $staff) {
                return false;
            }

            //Handle profile picture FIRST (if exists)
            if (isset($staff_data['profile_picture'])) {

                $file = $staff_data['profile_picture'];

                if ($staff->profile_picture) {
                    Storage::disk('private')->delete($staff->profile_picture);
                }

                $extension = $file->getClientOriginalExtension()
                    ?: $file->guessExtension()
                    ?: 'jpg';

                $filename = $staff->staff_id . '.' . $extension;

                $path = $file->storeAs(
                    'staff/profile_pictures',
                    $filename,
                    'private'
                );

                $staff->profile_picture = $path;
            }


            $staff->update([
                'first_name' => $staff_data['first_name'] ?? $staff->first_name,
                'last_name'  => $staff_data['last_name'] ?? $staff->last_name,
                'label_id'   => $staff_data['label_id'] ?? $staff->label_id,
                'genders'    => $staff_data['gender'] ?? $staff->genders,
                'email'      => $staff_data['email'] ?? $staff->email,
                'phone_number' => $staff_data['phone_number'] ?? $staff->phone_number,
                'position_id'  => $position_id ?? $staff->position_id,
                'department_id' => $department_id ?? $staff->department_id,
                'status'       => $staff_data['status'] ?? $staff->status,
                'date_of_joining' => $staff_data['date_of_joining'] ?? $staff->date_of_joining,
                'date_of_birth'   => $staff_data['date_of_birth'] ?? $staff->date_of_birth,
            ]);

            $isCreatedUser = !empty($staff_data['isCreatedUser']);

            if ($isCreatedUser && isset($staff_data['role_name'])) {
                $role = Role::where('role_name', $staff_data['role_name'])->first();
                if (!$role) {
                    throw new \Exception('Role Not Found');
                }

                if (!$staff->user) {
                    // Create user if not exists
                    $this->authRepo->createUser([
                        'name'          => ($staff_data['first_name'] ?? $staff->first_name) . ' ' . ($staff_data['last_name'] ?? $staff->last_name),
                        'staff_id'      => $staff->id,
                        'email'         => $staff_data['email'] ?? $staff->email,
                        'role_id'       => $role->id,
                        'password'      => $staff_data['password'],
                        'profile_image' => $staff_data['profile_picture'] ?? $staff->profile_picture,
                    ]);
                } else {
                    // Update user if exists
                    $userData = [
                        "name" => ($staff_data['first_name'] ?? $staff->first_name) . ' ' . ($staff_data['last_name'] ?? $staff->last_name),
                        "email" => $staff_data['email'] ?? $staff->email,
                        "role_id" => $role->id,
                    ];

                    if (!empty($staff_data['password'])) {
                        $userData['password'] = bcrypt($staff_data['password']);
                    }

                    if (isset($staff_data['profile_picture'])) {
                        // Use the path stored for staff if matching or handle separately
                        $userData['profile_image'] = $staff->profile_picture;
                    }

                    $staff->user->update($userData);
                }

                $staff->update([
                    'user_role_id' => $role->id,
                ]);

                if ($staff->user) {
                    $staff->user->update(['account_status' => 'active']);
                }
            } else {
                if ($staff->user) {
                    $staff->user->update(['account_status' => 'inactive']);
                }
            }


            $staff = $staff->fresh();
            $staff->load([
                'department:id,department_name',
                'position:id,position_name',
                'user'
            ]);

            return $staff;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
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

    public function searchStaffByID($staff_id)
    {
        try {
            $staff = Staff::where('staff_id', $staff_id)->first();
            if ($staff) {
                $staff->load([
                    'department:id,department_name',
                    'position:id,position_name'
                ]);
                return $staff;
            }
            return null;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function deleteStaffs($staff_id)
    {
        try {
            $staffs = Staff::where('staff_id', $staff_id)->first();

            if (!$staffs) {
                return false;
            }

            if ($staffs->profile_picture && Storage::disk('private')->exists($staffs->profile_picture)) {
                Storage::disk('private')->delete($staffs->profile_picture);
            }
            if ($staffs->user) {
                // Also optionally delete the user's profile image if it exists and is separate from staff
                $staffs->user->delete();
            }
            $staffs->delete();
            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
