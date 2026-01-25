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
        Schema::table('positions', function (Blueprint $table) {
            // 1) Add column only if it does not exist
            if (!Schema::hasColumn('positions', 'department_id')) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('id'); // change position if you want
            }

            // 2) Add FK constraint (Laravel will generate the constraint name)
            // Only do this if the column exists
            // (We just ensured it exists above)
            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            //
        });
    }
};
