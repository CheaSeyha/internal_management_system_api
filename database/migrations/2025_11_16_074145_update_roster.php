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
        Schema::table('rosters', function (Blueprint $table) {
            $table->dropColumn('shift_work');
            $table->string('work_shift',50);//7,8, OFF , UPL
            $table->date('date_of_shift');//7 -> '2023-11-07' OFF -> '2023-11-08'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
