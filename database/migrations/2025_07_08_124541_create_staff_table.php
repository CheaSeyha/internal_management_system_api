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
            $table->unsignedBigInteger('staff_id')->unique();
            $table->string('label_id', 50);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('genders', 10);
            $table->string('email')->nullable()->unique();
            $table->string('phone_number', 50)->nullable();
            $table->unsignedBigInteger('position_id')->nullable()->index();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('profile_picture')->nullable();
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
