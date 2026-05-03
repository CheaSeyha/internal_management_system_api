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
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->date('work_date');
            $table->timestamps();
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('shift_id');

            $table->foreign('staff_id')
                ->references('id')
                ->on('staff')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('shift_id')
                ->references('id')
                ->on('shifts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unique(['staff_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
