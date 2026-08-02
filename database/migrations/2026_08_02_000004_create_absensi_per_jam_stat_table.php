<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Agregat harian absensi per jam per siswa (PRD-006, P2 — F-10).
     * Satu baris = akumulasi seluruh sesi dalam 1 hari untuk 1 siswa.
     * Dipakai untuk statistik/leaderboard gamifikasi tanpa mengubah
     * poin & streak absensi harian (StudentGamificationStat tetap utuh).
     * Diperbarui oleh observer/service setiap kali ada perubahan absensi per jam.
     */
    public function up(): void
    {
        Schema::create('absensi_per_jam_stat', function (Blueprint $table) {
            // Primary key
            $table->id();

            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->cascadeOnDelete();

            $table->date('tanggal');

            // Akumulasi status per hari
            $table->unsignedSmallInteger('total_sesi')->default(0);
            $table->unsignedSmallInteger('hadir')->default(0);
            $table->unsignedSmallInteger('terlambat')->default(0);
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('alpha')->default(0);
            $table->unsignedSmallInteger('dispen')->default(0);

            $table->timestamps();

            // ---------------------------------------------------------------
            // UNIQUE constraint
            // Satu baris agregat per siswa per tanggal
            // ---------------------------------------------------------------
            $table->unique(
                ['siswa_id', 'tanggal'],
                'unique_stat_siswa_tanggal'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_per_jam_stat');
    }
};
