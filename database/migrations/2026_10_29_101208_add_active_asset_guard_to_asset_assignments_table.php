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
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_asset_id')
                ->nullable()
                ->virtualAs("case when status = 'active' then asset_id end")
                ->unique();
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
