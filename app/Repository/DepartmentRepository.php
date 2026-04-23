<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;

class DepartmentRepository
{
    public function getAllDepartments()
    {
        $departments = Department::with([
            'positions.staff'
        ])->get();

        $data = [];
        foreach ($departments as $department) {
            $data[] = [
                'department_id' => $department->id,
                'department'    => $department->department_name,
                'positions'     => $department->positions->map(fn($position) => [
                    'position_name' => $position->position_name,
                    'staff_count'   => $position->staff->count(),
                ]),
            ];
        }

        return $data;
    }

    public function addDepartment($department_name)
    {
        return Department::create([
            'department_name' => $department_name
        ]);
    }

    public function updateDepartment($id, $department_name)
    {
        $department = Department::find($id);
        if (!$department) {
            return false;
        }

        $department->department_name = $department_name;
        $department->save();

        return $department;
    }

    public function deleteDepartment($id)
    {
        $department = Department::find($id);
        if (!$department) {
            return false;
        }

        $department->delete();
        return true;
    }
}
