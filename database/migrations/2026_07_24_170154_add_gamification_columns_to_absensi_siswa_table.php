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
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->boolean('is_early_bird')->default(false)->after('keterangan');
            $table->integer('points_earned')->default(0)->after('is_early_bird');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->dropColumn(['is_early_bird', 'points_earned']);
        });
    }
};
