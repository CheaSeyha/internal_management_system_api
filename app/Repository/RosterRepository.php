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
}
