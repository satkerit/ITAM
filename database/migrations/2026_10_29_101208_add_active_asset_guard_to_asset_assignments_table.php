<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite hanya bisa menambah generated column VIRTUAL lewat ALTER,
        // sedangkan MariaDB hanya bisa memberi unique index pada column STORED.
        $usesVirtualColumn = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('asset_assignments', function (Blueprint $table) use ($usesVirtualColumn): void {
            $expression = "case when status = 'active' then asset_id end";

            $column = $table->unsignedBigInteger('active_asset_id')->nullable();

            $usesVirtualColumn
                ? $column->virtualAs($expression)
                : $column->storedAs($expression);

            $column->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropUnique(['active_asset_id']);
            $table->dropColumn('active_asset_id');
        });
    }
};
