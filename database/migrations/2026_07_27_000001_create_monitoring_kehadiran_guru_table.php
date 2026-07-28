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
        Schema::create('monitoring_kehadiran_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'tidak_hadir', 'terlambat']);
            $table->enum('keterangan', ['sakit', 'izin', 'dinas_luar', 'alfa'])->nullable();
            $table->text('keterangan_lain')->nullable();
            $table->unsignedTinyInteger('lama_terlambat')->nullable();
            $table->boolean('ada_pengganti')->default(false);
            $table->foreignId('guru_pengganti_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('guru_pengganti_nama', 191)->nullable();
            $table->foreignId('dicatat_oleh')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jadwal_pelajaran_id', 'tanggal'], 'monitoring_jadwal_tanggal_unique');
            $table->index('tanggal', 'idx_monitoring_tanggal');
            $table->index('dicatat_oleh', 'idx_monitoring_dicatat_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_kehadiran_guru');
    }
};
