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
        Schema::create('pengaduan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_unik', 25)->unique()->comment('Format: PGN-YYYYMMDD-NNN');
            $table->string('nama_lengkap', 100);
            $table->enum('status_pelapor', ['siswa', 'orang_tua']);
            $table->string('kategori', 100);
            $table->text('deskripsi');
            $table->string('nomor_wa', 20);
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('catatan_admin')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduan');
    }
};
