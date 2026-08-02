<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Header sesi absensi per jam (PRD-006, P1 — F-9).
     * Satu baris = 1 sesi (jadwal_pelajaran + tanggal).
     * Menyimpan ringkasan & status kelengkapan agar dashboard piket/admin
     * dapat mendeteksi "jam mana yang belum diisi" hari ini secara efisien.
     */
    public function up(): void
    {
        Schema::create('absensi_per_jam_sesi', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Sesi selalu mengacu pada jadwal_pelajaran + tanggal
            $table->foreignId('jadwal_pelajaran_id')
                  ->constrained('jadwal_pelajaran')
                  ->cascadeOnDelete();

            $table->date('tanggal');

            // Denormalisasi untuk filter per kelas tanpa JOIN ke jadwal_pelajaran
            $table->foreignId('kelas_id')
                  ->constrained('kelas');

            // Guru pengampu saat sesi berlangsung (bisa pengganti)
            // nullable + nullOnDelete: sesi tetap ada walaupun guru dihapus
            $table->foreignId('guru_id')
                  ->nullable()
                  ->constrained('guru')
                  ->nullOnDelete();

            // Pengisi pertama (siapa yang membuat header sesi)
            $table->foreignId('dicatat_oleh')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Snapshot ringkasan — diperbarui setiap bulk upsert absensi
            $table->unsignedSmallInteger('jumlah_siswa')->default(0);
            $table->unsignedSmallInteger('jumlah_hadir')->default(0);
            $table->unsignedSmallInteger('jumlah_alpha')->default(0);

            // Catatan sesi, mis. "diisi oleh guru pengganti"
            $table->text('catatan')->nullable();

            $table->timestamps();

            // ---------------------------------------------------------------
            // UNIQUE constraint
            // Satu sesi (jadwal + tanggal) hanya boleh punya 1 header
            // ---------------------------------------------------------------
            $table->unique(
                ['jadwal_pelajaran_id', 'tanggal'],
                'unique_sesi_jadwal_tanggal'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_per_jam_sesi');
    }
};
