<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = \App\Models\Staff::first();
        $leaveType = \App\Models\LeaveType::where('name', 'Annual Leave')->first();

        if ($staff && $leaveType) {
            \App\Models\LeaveRequest::create([
                'staff_id' => $staff->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDays(5)->format('Y-m-d'),
                'end_date' => now()->addDays(7)->format('Y-m-d'),
                'status' => 'pending',
            ]);

            \App\Models\LeaveRequest::create([
                'staff_id' => $staff->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->subDays(10)->format('Y-m-d'),
                'end_date' => now()->subDays(8)->format('Y-m-d'),
                'status' => 'approved',
            ]);
        }
    }
}
