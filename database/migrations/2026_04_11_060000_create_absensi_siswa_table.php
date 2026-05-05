<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('status')->default('hadir');
            $table->text('keterangan')->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('metode')->default('manual');
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal'], 'absensi_siswa_unique_siswa_tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa');
    }
};
