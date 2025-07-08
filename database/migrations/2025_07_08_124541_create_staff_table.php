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
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('email')->nullable()->unique();
            $table->string('phone_number', 15)->nullable();
            $table->unsignedBigInteger('position_id')->nullable()->index();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->string('status', 20)->default('active'); // e.g., active, inactive, on leave
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedBigInteger('dayoff_id')->nullable()->index();
            $table->foreign('dayoff_id')->references('id')->on('dayoffs')->onDelete('set null');
            $table->string('profile_picture')->nullable(); // Path to profile picture
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
