<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique(); // e.g., VIP, Standard
            $table->timestamps();
        });

        // Insert default card types
        DB::table('card_types')->insert([
            ['name' => 'STAFF', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CAR CARD', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'VIP CARD', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'CONSTRUCTION', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'TUKTUK', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'DELIVERY', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
