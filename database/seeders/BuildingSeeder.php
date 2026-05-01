<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Building;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultBuildings = [
            'N','L','M','O','P','P3','H','I','J',
            'S1','S2','S3','S5','X1','X2','X3',
            'U1','U2','W1','W2','T','Q','LY','Y1','Y2'
        ];

        foreach ($defaultBuildings as $building) {
            Building::updateOrCreate(
                ['building_name' => strtoupper($building)],
                ['updated_at' => now()]
            );
        }
    }
}
