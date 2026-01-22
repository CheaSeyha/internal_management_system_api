<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Models\Department;
use App\Models\Position;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Repository\StaffRepository;

class StaffService
{
    /**
     * Create a new class instance.
     */
    protected $responseHelper;

    protected $staffRepository;

    public function __construct(ResponseHelper $responseHelper, StaffRepository $staffRepository)
    {
        //
        $this->responseHelper = $responseHelper;
        $this->staffRepository = $staffRepository;
    }

    public function add_staff($staff_data)
    {
        // Convert NAME to ID for position
        $position_id = null;
        $department_id = null;
        $role_id = null;

        if (isset($staff_data['position_name'])) {
            $getId = Position::where('position_name', $staff_data['position_name'])->first();

            if (! $getId) {
                return $this->responseHelper->fail('Position Not Found', null, 404);
            }

            $position_id = $getId->id;  // assign to local variable
        }

        if (isset($staff_data['department_name'])) {
            $getDepartmentId = Department::where('department_name', $staff_data['department_name'])->first();

            if (! $getDepartmentId) {
                return $this->responseHelper->fail('Department Not Found', null, 404);
            }

            $department_id = $getDepartmentId->id;
        }

        if (isset($staff_data['role_name'])) {
            $getRoleId = Role::where('role_name', $staff_data['role_name'])->first();

            if (! $getRoleId) {
                return $this->responseHelper->fail('Role name Not Found', null, 404);
            }

            $role_id = $getRoleId->id;
        }

        $result = $this->staffRepository->add_staff($staff_data, $department_id, $position_id, $role_id);

        return $result
            ? $this->responseHelper->success('New Staff Added Successfully', $result, 200)
            : $this->responseHelper->fail('Failed to add mew staff add', null, 500);
    }

    public function getAllStaff()
    {
        // 2) All users + (optional) linked staff
        $user_data = User::with([
            'role',
            'staff.position',
            'staff.department',
        ])->paginate(17);

        return $this->responseHelper->success(
            'Get All Staff and Users',
            $user_data,
            200
        );
    }
}
