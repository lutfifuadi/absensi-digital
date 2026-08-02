<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Log notifikasi absensi per jam (PRD-006, P1 — F-6).
     * Mencatat setiap pengiriman notifikasi (WA / in-app) per catatan absensi.
     * UNIQUE (absensi_per_jadwal_id, jenis) menjamin anti-spam:
     * sekali WA dan sekali in-app per catatan kehadiran siswa.
     *
     * Tabel ini hanya menyimpan created_at (tanpa updated_at) karena
     * log tidak pernah diubah setelah dibuat.
     */
    public function up(): void
    {
        Schema::create('absensi_per_jam_notif_log', function (Blueprint $table) {
            // Primary key
            $table->id();

            // FK ke catatan absensi yang memicu notifikasi
            $table->foreignId('absensi_per_jadwal_id')
                  ->constrained('absensi_siswa_per_jadwal')
                  ->cascadeOnDelete();

            // Redundansi siswa_id untuk query log per siswa tanpa JOIN
            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            $table->date('tanggal');

            // Jenis channel: wa | in_app
            $table->string('jenis', 20);

            // Status kehadiran saat notifikasi dikirim (snapshot)
            $table->string('status', 20);

            // Hasil pengiriman: sent | skipped | failed
            $table->string('status_kirim', 20);

            // Alasan skip/fail, mis. "nomor tidak tersedia", "toggle nonaktif"
            $table->string('alasan', 191)->nullable();

            // Hanya created_at — log bersifat immutable
            $table->timestamp('created_at')->nullable();

            // ---------------------------------------------------------------
            // UNIQUE constraint — anti-spam notifikasi (F-6)
            // Satu WA dan satu in-app per catatan absensi
            // ---------------------------------------------------------------
            $table->unique(
                ['absensi_per_jadwal_id', 'jenis'],
                'unique_notif_log'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_per_jam_notif_log');
    }
};
