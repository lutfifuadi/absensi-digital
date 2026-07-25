<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Membuat tabel kelas_jadwal_absensi untuk menyimpan jadwal absensi
     * per kelas per hari dalam seminggu. Setiap kelas bisa memiliki
     * jadwal yang berbeda-beda untuk setiap hari (Senin-Minggu).
     *
     * Field yang NULL akan fallback ke pengaturan global:
     * - jam_mulai_absensi: 06:00
     * - jam_masuk: 07:00
     * - jam_pulang: 15:00
     * - jam_akhir_pulang: 17:00
     */
    public function up(): void
    {
        Schema::create('kelas_jadwal_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->enum('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);
            $table->time('jam_mulai_absensi')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->time('jam_akhir_pulang')->nullable();
            $table->boolean('is_libur')->default(false);
            $table->timestamps();

            // UNIQUE KEY: satu kelas hanya bisa punya satu jadwal per hari
            $table->unique(['kelas_id', 'hari']);

            // Index untuk query berdasarkan kelas
            $table->index('kelas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_jadwal_absensi');
    }
};
