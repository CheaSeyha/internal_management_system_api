<?php

namespace App\Repository;

use App\Models\Department;
use App\Models\Position;

class PositionRepository
{
    public function getAllPositions()
    {
        $positions = Position::with('department')->get();

        $data = [];

        foreach ($positions as $position) {
            $deptId = $position->department_id;

            // If department not exist yet → create it
            if (!isset($data[$deptId])) {
                $data[$deptId] = [
                    'department_id' => $deptId,
                    'department_name' => $position->department->department_name,
                    'positions' => [],
                ];
            }

            // Push position into that department
            $data[$deptId]['positions'][] = [
                'position_id' => $position->id,
                'position_name' => $position->position_name,
            ];
        }

        // Reset array index (important for clean JSON)
        return array_values($data);
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

    public function deletePosition($id, $department_id)
    {
        $position = Position::where('id', $id)->where('department_id', $department_id)->first();
        if (!$position) {
            return false;
        } else {
            $position->delete();
            return true;
        }
    }
}
