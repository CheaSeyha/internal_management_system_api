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
        Schema::create('dayoffs', function (Blueprint $table) {
            $table->id();
            $table->date('month');
            $table->integer('month_off_days');
            $table->integer('unpaid_leave');
            $table->integer('annual_leave');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dayoffs');
    }
};
