<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Off Day',
                'description' => 'Paid monthly off time.',
            ],
            [
                'name' => 'Annual Leave',
                'description' => 'Paid time off for holidays or personal use.',
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Paid time off for medical reasons.',
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for child birth.',
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers.',
            ],
            [
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay.',
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            \App\Models\LeaveType::updateOrCreate(['name' => $leaveType['name']], $leaveType);
        }
    }
}
