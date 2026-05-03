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

        $staff = Staff::where('id', $staff_data['id'])->first();

        if ($staff) {
            return $this->responseHelper->fail('Staff ID :' . $staff_data['id'] . ' already exists', null, 409);
        }


        if ($staff) {
            return $this->responseHelper->fail('Staff ID already exists', null, 404);
        }

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

    public function getAllStaff(array $filters = [], array $queryParams = [])
    {
        $staff_data = Staff::with([
            'department',
            'position',
            'user',
        ])
            ->filter($filters)
            ->latest()
            ->paginate(10)
            ->appends($queryParams);

        // Transform data
        $staff_data->getCollection()->transform(function ($staff) {
            return [
                // from user
                'id' => $staff->id ?? null,
                'label_id' => $staff->label_id ?? null,
                'role_name' => $staff->user->role->role_name ?? null,
                'account_status' => $staff->user->account_status ?? null,

                // from staff
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,

                // from relations
                'department_name' => $staff->department->department_name ?? null,
                'position_name' => $staff->position->position_name ?? null,

                'email' => $staff->email ?? null,
                'phone_number' => $staff->phone_number ?? null,
                'genders' => $staff->genders ?? null,
                'date_of_joining' => $staff->date_of_joining ?? null,
                'date_of_birth' => $staff->date_of_birth ?? null,
                'status' => $staff->status ?? null,
            ];
        });

        return $this->responseHelper->success(
            'Get All Staff and Users',
            $staff_data,
            200
        );
    }

    public function update_staff($staff_id, $staff_data)
    {

        $position_id = null;
        $department_id = null;
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
        try {
            $result = $this->staffRepository->update_staff($staff_id, $staff_data, $department_id, $position_id);
            if (! $result) {
                return $this->responseHelper->fail('Staff Not Found', null, 404);
            }
            return $this->responseHelper->success('Staff Updated Successfully', $result, 200);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('Failed to update staff' . $th->getMessage(), null, 500);
        }
    }

    public function searchStaff($query)
    {
        $result = $this->staffRepository->searchStaff($query);
        return $this->responseHelper->success('Search Result', $result, 200);
    }

    public function deleteStaffs($staff_id)
    {
        try {
            $result = $this->staffRepository->deleteStaffs($staff_id);
            return $result
                ? $this->responseHelper->success('Staff deleted successfully', null, 200)
                : $this->responseHelper->fail('Staff Not Found', $result, 404);
        } catch (\Throwable $th) {
            return $this->responseHelper->fail('Failed to delete staff(s)' . $th->getMessage(), null, 500);
        }
    }
}
