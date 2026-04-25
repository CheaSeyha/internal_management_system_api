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
        Schema::create('leave_balance', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staff_id')
                ->constrained('staff')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            $table->integer('total_days');//balance remain of this month
            $table->integer('used_days');//balacne that use of this month

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance');
    }
};
