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
        Schema::create('jadwal_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->enum('jenis', ['Seminar', 'Ekstrakurikuler', 'Lomba', 'Acara Internal', 'Lainnya'])->default('Acara Internal');
            $table->enum('tipe_jadwal', ['mingguan_1_hari', 'mingguan_multi_hari', 'tanggal_kalender']);
            $table->json('hari')->nullable();
            $table->json('tanggal_kalender')->nullable();
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('keterangan')->nullable();
            $table->boolean('is_wajib')->default(false);
            $table->json('target_peserta')->nullable();
            $table->json('target_tingkat')->nullable();
            $table->json('target_jurusan')->nullable();
            $table->string('target_gender', 10)->nullable();
            $table->json('target_siswa')->nullable();
            $table->foreignId('ekskul_id')->nullable()->constrained('ekskul')->nullOnDelete();
            $table->foreignId('ekskul_jadwal_id')->nullable()->constrained('ekskul_jadwal')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('qr_code_prefix', 50);
            $table->boolean('is_aktif')->default(true);
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_aktif', 'tahun_akademik_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kegiatan');
    }
};
