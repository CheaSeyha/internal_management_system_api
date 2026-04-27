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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_type_id')->constrained('card_types')->onDelete('restrict'); // link to type
            $table->unsignedInteger('card_number');
            $table->string('card_name', 50);
            $table->string('profile_image', 200)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('isp_id')->nullable()->constrained('isps')->nullOnDelete()->cascadeOnUpdate();
            $table->string('isp_position')->nullable();
            $table->string('rolling_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
