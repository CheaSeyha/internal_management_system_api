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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name', 100);
            $table->unsignedBigInteger('building_id')->index(); // NOT nullable
            $table->unique(['building_id', 'room_name']);
            $table->foreign('building_id')
                ->references('id')->on('buildings')
                ->onDelete('cascade'); // or 'restrict', depending on your need
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
