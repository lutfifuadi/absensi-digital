<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambah index status dan composite index (tanggal, status) pada:
     * - absensi_guru
     * - absensi_staff
     *
     * Kedua tabel ini tidak memiliki index pada kolom status sama sekali
     * (tabel absensi_siswa sudah ditangani di migration terpisah).
     */
    public function up(): void
    {
        // absensi_guru: tambah index status dan composite (tanggal, status)
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->index('status', 'absensi_guru_status_index');
            $table->index(['tanggal', 'status'], 'absensi_guru_tanggal_status_index');
        });

        // absensi_staff: tambah index status dan composite (tanggal, status)
        Schema::table('absensi_staff', function (Blueprint $table) {
            $table->index('status', 'absensi_staff_status_index');
            $table->index(['tanggal', 'status'], 'absensi_staff_tanggal_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropIndex('absensi_guru_status_index');
            $table->dropIndex('absensi_guru_tanggal_status_index');
        });

        Schema::table('absensi_staff', function (Blueprint $table) {
            $table->dropIndex('absensi_staff_status_index');
            $table->dropIndex('absensi_staff_tanggal_status_index');
        });
    }
};
