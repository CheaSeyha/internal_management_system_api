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
        $shifts = \App\Models\Shift::where('name', '!=', 'OFF')->get();
        $offShift = \App\Models\Shift::where('name', 'OFF')->first();

        foreach ($staffMembers as $staff) {
            for ($i = 0; $i < 7; $i++) {
                $date = now()->addDays($i)->format('Y-m-d');
                $isWeekend = now()->addDays($i)->isWeekend();

                \App\Models\Roster::updateOrCreate(
                    [
                        'staff_id' => $staff->staff_id, // Roster uses staff_id (not id)
                        'work_date' => $date,
                    ],
                    [
                        'shift_id' => $isWeekend ? ($offShift->id ?? null) : $shifts->random()->id,
                    ]
                );
            }
        }
    }
}
