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
        Schema::create('bk_kasus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_bk_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('judul_kasus');
            $table->text('deskripsi')->nullable();
            $table->enum('kategori', ['pribadi', 'sosial', 'belajar', 'karir', 'disiplin'])->default('pribadi');
            $table->enum('tingkat_keparahan', ['ringan', 'sedang', 'berat', 'sangat_berat'])->default('ringan');
            $table->enum('status', ['terbuka', 'dalam_proses', 'selesai', 'dirujuk'])->default('terbuka');
            $table->date('tanggal_lapor');
            $table->date('tanggal_selesai')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index(['guru_bk_id', 'status']);
            $table->index('tanggal_lapor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bk_kasus');
    }
};
