<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;

class DepartmentRepository
{
    public function getAllDepartments()
    {
        $departments = Department::all();
        $positions = Position::all();

        $data = [];
        foreach ($departments as $department) {
            $data[] = [
                'department' => $department->department_name,
                'positions' => $positions->where('department_id', $department->id)->pluck('position_name'),
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
