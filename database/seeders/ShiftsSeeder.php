<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            // OFF Day
            [
                'name' => 'OFF',
                'start_time' => '00:00:00',
                'end_time'   => '00:00:00',
            ],

            // Morning Shifts
            [
                'name' => 'Morning (07:00 - 15:00)',
                'start_time' => '07:00:00',
                'end_time'   => '15:00:00',
            ],
            [
                'name' => 'Morning (09:00 - 17:00)',
                'start_time' => '09:00:00',
                'end_time'   => '17:00:00',
            ],
            [
                'name' => 'Morning (11:00 - 19:00)',
                'start_time' => '11:00:00',
                'end_time'   => '19:00:00',
            ],

            // Afternoon Shift
            [
                'name' => 'Afternoon (15:00 - 23:00)',
                'start_time' => '15:00:00',
                'end_time'   => '23:00:00',
            ],

            // Night Shift (cross-day)
            [
                'name' => 'Night (23:00 - 07:00)',
                'start_time' => '23:00:00',
                'end_time'   => '07:00:00',
            ],
        ];

        DB::table('shifts')->insert($shifts);
    }
}
