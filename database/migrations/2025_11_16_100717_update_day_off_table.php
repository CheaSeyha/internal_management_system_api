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
        Schema::table('dayoffs', function (Blueprint $table) {
            $table->dropColumn('month');
            $table->dropColumn('month_off_days');
            $table->dropColumn('unpaid_leave');
            $table->dropColumn('annual_leave');

            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_type')->onDelete('cascade')->onUpdate('cascade');
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
