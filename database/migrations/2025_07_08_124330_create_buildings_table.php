<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('building_name', 100)->unique();
            $table->timestamps();
        });

        // Default buildings (all uppercase)
        $defaultBuildings = [
            'N','L','M','O','P','P3','H','I','J',
            'S1','S2','S3','S5','X1','X2','X3',
            'U1','U2','W1','W2','T','Q','LY','Y1','Y2'
        ];

        foreach ($defaultBuildings as $building) {
            DB::table('buildings')->insert([
                'building_name' => strtoupper($building),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buildings');
    }
};
