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
        Schema::create('connection_types', function (Blueprint $table) {
            $table->id();
            $table->string('connection_type', 100);
            $table->string('username', 100)->nullable();
            $table->string('password', 100)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('mask', 17)->nullable();
            $table->string('getway', 17)->nullable();
            $table->boolean('is_active')->default(false);
            $table->string('package', 100)->nullable();
            $table->integer('speed')->nullable();
            $table->bigInteger('isp_id')->unsigned()->nullable()->index();
            $table->foreign('isp_id')->references('id')->on('isps')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connection_types');
    }
};
