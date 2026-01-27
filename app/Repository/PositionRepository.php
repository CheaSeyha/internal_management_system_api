<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;

class PositionRepository
{
    public function getAllPositions()
    {
        return Position::with('department')->get();
    }

    public function addPosition($data)
    {
        // Resolve department_name to department_id
        if (isset($data['department_name'])) {
            $department = Department::where('department_name', $data['department_name'])->first();
            if ($department) {
                $data['department_id'] = $department->id;
                unset($data['department_name']);
            }
        }

        return Position::create($data);
    }

    public function updatePosition($id, $data)
    {
        $position = Position::find($id);
        if (!$position) {
            return false;
        }

        // Resolve department_name to department_id
        if (isset($data['department_name'])) {
            $department = Department::where('department_name', $data['department_name'])->first();
            if ($department) {
                $data['department_id'] = $department->id;
                unset($data['department_name']);
            }
        }

        $position->update($data);

        return $position;
    }

    public function deletePosition($id)
    {
        $position = Position::find($id);
        if (!$position) {
            return false;
        }

        $position->delete();
        return true;
    }
}
