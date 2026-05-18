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
        Schema::table('google_sheet_settings', function (Blueprint $table) {
            $table->integer('sync_total_rows')->nullable()->after('last_sync_message');
            $table->integer('sync_processed_rows')->nullable()->after('sync_total_rows');
        });
    }

    public function down(): void
    {
        Schema::table('google_sheet_settings', function (Blueprint $table) {
            $table->dropColumn(['sync_total_rows', 'sync_processed_rows']);
        });
    }
};
