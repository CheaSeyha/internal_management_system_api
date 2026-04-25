<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rosters', function (Blueprint $table) {
            // 1. Add new columns safely3
            $table->dropColumn('work_shift');
            $table->dropColumn('date_of_shift');
            
            $table->date('work_date')->nullable();

            $table->foreignId('shift_id')
                ->nullable() // VERY IMPORTANT
                ->constrained('shifts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        // ⚠️ OPTIONAL: migrate old data here before dropping columns
    }

    public function down(): void
    {
        Schema::table('rosters', function (Blueprint $table) {

        });
    }
};
