<?php

namespace App\Repository;

use App\Models\Roster;

class RosterRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function findByStaffAndDate($staffId, $workDate)
    {
        return Roster::where('staff_id', $staffId)
            ->where('work_date', $workDate)
            ->first();
    }

    public function create(array $data)
    {
        return Roster::create($data);
    }

    public function update(Roster $roster, array $data)
    {
        return $roster->update($data);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return Roster::updateOrCreate($attributes, $values);
    }

    public function getAllRoster($month, $year, $departmentId = null)
    {

        return Roster::with(['staff.position', 'staff.department', 'staff.user.role', 'staff.leaveBalances.leaveType', 'shift'])
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->whereHas('staff', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->get();
    }
}
