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
    }

    public function down(): void
    {
        Schema::dropIfExists('card_types');
    }
};
