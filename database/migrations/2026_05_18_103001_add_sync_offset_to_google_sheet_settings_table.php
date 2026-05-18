<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_sheet_settings', function (Blueprint $table) {
            $table->integer('sync_offset')->nullable()->after('sync_processed_rows');
        });
    }

    public function down(): void
    {
        Schema::table('google_sheet_settings', function (Blueprint $table) {
            $table->dropColumn('sync_offset');
        });
    }
};
