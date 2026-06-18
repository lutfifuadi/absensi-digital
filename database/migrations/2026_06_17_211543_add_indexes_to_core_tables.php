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
        // 1. absensi_siswa: tanggal, status (sering di-filter bersamaan)
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('status');
        });

        // 2. siswa: status (qr_code sudah ada index)
        Schema::table('siswa', function (Blueprint $table) {
            $table->index('status');
        });

        // 3. izin_sakit: reference_id, tipe, tanggal_mulai, tanggal_selesai, status
        Schema::table('izin_sakit', function (Blueprint $table) {
            $table->index(['reference_id', 'tipe']);
            $table->index('tanggal_mulai');
            $table->index('tanggal_selesai');
            $table->index('status');
        });

        // 4. activity_attendance: status
        Schema::table('activity_attendance', function (Blueprint $table) {
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('izin_sakit', function (Blueprint $table) {
            $table->dropIndex(['reference_id', 'tipe']);
            $table->dropIndex(['tanggal_mulai']);
            $table->dropIndex(['tanggal_selesai']);
            $table->dropIndex(['status']);
        });

        Schema::table('activity_attendance', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
