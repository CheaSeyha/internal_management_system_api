<?php

namespace App\Repository;

use App\Models\Department;

class DepartmentRepository
{
    public function getAllDepartments()
    {
        return Department::all();
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
