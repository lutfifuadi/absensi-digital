<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Catatan: index('status') dan index('tanggal') pada tabel absensi_siswa
     * sudah ada di migration 2026_06_17_211543_add_indexes_to_core_tables.php.
     * Migration ini hanya menambah composite index (tanggal, status) yang belum ada.
     */
    public function up(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            // Composite index (tanggal, status) — belum ada, baru ditambahkan
            $table->index(['tanggal', 'status'], 'absensi_siswa_tanggal_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_siswa', function (Blueprint $table) {
            $table->dropIndex('absensi_siswa_tanggal_status_index');
        });
    }
};
