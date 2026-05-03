<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RosterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = \App\Models\Staff::all();

        foreach ($staffMembers as $staff) {

            if (!$staff || !$staff->id) {
                dd('Invalid staff found', $staff);
            }

            for ($i = 0; $i < 7; $i++) {

                $date = now()->addDays($i)->format('Y-m-d');
                $isWeekend = now()->addDays($i)->isWeekend();

                \App\Models\Roster::updateOrCreate(
                    [
                        'staff_id' => $staff->id,
                        'work_date' => $date,
                    ],
                    [
                        'shift_id' => $isWeekend ? 1 : 2, // temporary test
                    ]
                );
            }
        }
    }
}
