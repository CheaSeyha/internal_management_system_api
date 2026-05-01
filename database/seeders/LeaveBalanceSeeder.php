<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = \App\Models\Staff::all();
        $leaveTypes = \App\Models\LeaveType::all();

        foreach ($staffMembers as $staff) {
            foreach ($leaveTypes as $type) {
                \App\Models\LeaveBalance::updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'leave_type_id' => $type->id,
                    ],
                    [
                        'total_days' => ($type->name === 'Monthly Off') ? 4 : 0,
                        'used_days' => 0,
                    ]
                );
            }
        }
    }
}
