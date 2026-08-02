<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel inti absensi siswa per jam pelajaran (PRD-006, P0).
     * Setiap baris = 1 catatan kehadiran siswa pada 1 sesi (jadwal_pelajaran + tanggal).
     */
    public function up(): void
    {
        Schema::create('absensi_siswa_per_jadwal', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign keys — parent tables
            $table->foreignId('jadwal_pelajaran_id')
                  ->constrained('jadwal_pelajaran')
                  ->cascadeOnDelete();

            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            // Denormalisasi kelas_id untuk efisiensi query rekap per kelas
            // Tidak pakai cascade agar data historis tetap ada bila kelas dihapus
            $table->foreignId('kelas_id')
                  ->constrained('kelas');

            // Data kolom
            $table->date('tanggal');

            $table->enum('status', [
                'hadir',
                'terlambat',
                'sakit',
                'izin',
                'alpha',
                'dispen',
            ]);

            // Wajib diisi jika status = terlambat (validasi di application layer)
            $table->unsignedTinyInteger('lama_terlambat')->nullable();

            $table->text('keterangan')->nullable();

            // manual | cek_semua_hadir | pengganti
            $table->string('metode', 20)->default('manual');

            // Jejak audit: siapa yang mencatat
            $table->foreignId('dicatat_oleh')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // ---------------------------------------------------------------
            // UNIQUE constraint — anti-duplikat BR-01
            // Satu siswa hanya boleh punya 1 catatan per (jadwal + tanggal)
            // ---------------------------------------------------------------
            $table->unique(
                ['jadwal_pelajaran_id', 'siswa_id', 'tanggal'],
                'unique_absensi_per_jadwal'
            );

            // ---------------------------------------------------------------
            // Indexes untuk query performa (§6 NFR PRD-006)
            // ---------------------------------------------------------------

            // Rekap per siswa (profil/portal siswa & BK)
            $table->index(['siswa_id', 'tanggal'], 'idx_apj_siswa_tanggal');

            // Rekap per kelas (wali kelas & dashboard kelas)
            $table->index(['kelas_id', 'tanggal'], 'idx_apj_kelas_tanggal');

            // Filter rentang tanggal global
            $table->index('tanggal', 'idx_apj_tanggal');

            // Pencarian sesi & kelengkapan (F-9)
            $table->index(['jadwal_pelajaran_id', 'tanggal'], 'idx_apj_jadwal_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa_per_jadwal');
    }
};
