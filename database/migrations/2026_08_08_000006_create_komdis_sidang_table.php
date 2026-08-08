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
        Schema::create('komdis_sidang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('bk_kasus_id')->nullable()->constrained('bk_kasus')->nullOnDelete();
            $table->date('tanggal_sidang');
            $table->time('waktu_sidang')->nullable();
            $table->string('lokasi_sidang')->nullable();
            $table->string('agenda');
            $table->text('deskripsi_pelanggaran');
            $table->text('keputusan_sidang')->nullable();
            $table->enum('status', ['terjadwal', 'berjalan', 'ditunda', 'selesai'])->default('terjadwal');
            $table->foreignId('pimpinan_sidang_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index('tanggal_sidang');
            $table->index('pimpinan_sidang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komdis_sidang');
    }
};
