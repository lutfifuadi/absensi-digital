<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kenaikan_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kelas_asal_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('kelas_tujuan_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('tahun_akademik_asal_id')->constrained('tahun_akademik');
            $table->foreignId('tahun_akademik_tujuan_id')->nullable()->constrained('tahun_akademik');
            $table->string('status_awal');
            $table->string('status_akhir');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kenaikan_kelas');
    }
};
