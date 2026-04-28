<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            [
                'position_name' => 'Manager',
                'department_id' => 1,
            ],
            [
                'position_name' => 'Supervisor',
                'department_id' => 1,
            ],
            [
                'position_name' => 'Technician',
                'department_id' => 1,
            ],
            [
                'position_name' => 'Officer',
                'department_id' => 2,
            ],
        ];

        foreach ($positions as $position) {
            Position::create([
                'position_name' => $position['position_name'],
                'department_id' => $position['department_id'],
            ]);
        }
    }
}
