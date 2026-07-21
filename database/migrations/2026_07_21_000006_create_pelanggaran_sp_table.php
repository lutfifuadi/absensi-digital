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
        Schema::create('pelanggaran_sp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik');
            $table->enum('level_sp', ['SP1', 'SP2', 'SP3']);
            $table->smallInteger('total_poin_saat_sp')->unsigned();
            $table->date('tanggal_sp');
            $table->text('catatan_tambahan')->nullable();
            $table->foreignId('diterbitkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'tahun_akademik_id'], 'idx_siswa_tahun_sp');
            $table->unique(['siswa_id', 'tahun_akademik_id', 'level_sp'], 'uk_sp_per_siswa_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_sp');
    }
};
