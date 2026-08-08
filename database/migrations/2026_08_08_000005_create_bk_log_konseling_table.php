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
        Schema::create('bk_log_konseling', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bk_kasus_id')->nullable()->constrained('bk_kasus')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_bk_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal_konseling');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->enum('jenis_konseling', ['individu', 'kelompok', 'karir', 'kunjungan_rumah'])->default('individu');
            $table->text('ringkasan_masalah');
            $table->text('hasil_konseling')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->enum('status_tindak_lanjut', ['belum', 'proses', 'selesai'])->default('belum');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal_konseling']);
            $table->index(['guru_bk_id', 'tanggal_konseling']);
            $table->index('bk_kasus_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_log_konseling');
    }
};
