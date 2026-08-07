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
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->string('slug', 255)->unique();
            $table->longText('konten');
            $table->string('kategori', 50)->default('informasi'); // informasi, penting, kegiatan, mendesak, libur
            $table->string('target', 50)->default('semua'); // semua, guru, siswa, orang_tua, staff, kelas
            $table->foreignId('target_kelas_id')->nullable()->constrained('kelas')->onDelete('cascade');
            $table->string('lampiran', 255)->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_aktif')->default(true);
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
