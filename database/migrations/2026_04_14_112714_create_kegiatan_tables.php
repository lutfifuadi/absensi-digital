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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->enum('jenis', ['Seminar', 'Ekstrakurikuler', 'Lomba', 'Acara Internal', 'Lainnya'])->default('Acara Internal');
            $table->date('tanggal_pelaksanaan');
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->string('lokasi')->nullable();
            $table->string('qr_code_kegiatan')->unique();
            $table->boolean('is_wajib')->default(false);
            $table->json('target_peserta')->nullable(); // array of kelas_ids, if null means all
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('absensi_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->time('jam_absen')->nullable();
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->string('keterangan')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_kegiatan');
        Schema::dropIfExists('kegiatan');
    }
};
