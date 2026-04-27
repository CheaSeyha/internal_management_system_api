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
        Schema::create('card_building_room', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('card_id')->nullable()->index();
            $table->foreign('card_id')->references('id')->on('cards')->onDelete('set null')->cascadeOnUpdate();
            $table->unsignedBigInteger('building_id')->nullable()->index();
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null')->cascadeOnUpdate();
            $table->unsignedBigInteger('room_id')->nullable()->index();
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null')->cascadeOnUpdate();
            $table->timestamps();
            $table->unique(['card_id', 'building_id', 'room_id']); // prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_building_room');
    }
};
