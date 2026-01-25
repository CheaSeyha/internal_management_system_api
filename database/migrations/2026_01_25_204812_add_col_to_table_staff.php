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
        Schema::table('staff', function (Blueprint $table) {
            //
            $table->integer('staff_id')->unique()->after('id');
            $table->string('label_id', 50)->after('staff_id');
            $table->string('genders', 10)->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            //
            $table->dropColumn('staff_id');
            $table->dropColumn('label_id');
            $table->dropColumn('genders');
        });
    }
};
