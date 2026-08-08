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
        Schema::create('komdis_sanksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('komdis_sidang_id')->nullable()->constrained('komdis_sidang')->nullOnDelete();
            $table->string('nama_sanksi');
            $table->text('deskripsi_sanksi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['aktif', 'selesai', 'dibatalkan'])->default('aktif');
            $table->foreignId('diberikan_oleh')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index('komdis_sidang_id');
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komdis_sanksi');
    }
};
