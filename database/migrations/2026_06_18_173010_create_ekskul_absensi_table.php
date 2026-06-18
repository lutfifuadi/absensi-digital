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
        Schema::create('ekskul_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekskul_id')->constrained('ekskul')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha', 'terlambat']);
            $table->time('jam_absen')->nullable();
            $table->foreignId('pembina_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['ekskul_id', 'siswa_id', 'tanggal']);
            $table->index(['ekskul_id', 'tanggal']);
            $table->index(['siswa_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_absensi');
    }
};
