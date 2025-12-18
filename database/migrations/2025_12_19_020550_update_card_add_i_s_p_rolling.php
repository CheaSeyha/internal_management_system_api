<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            // Now drop column
            $table->foreignId('isp_id')
                ->nullable()
                ->constrained('isps')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('isp_position')->nullable();

            // Add rolling card owner name
            $table->string('rolling_link')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
