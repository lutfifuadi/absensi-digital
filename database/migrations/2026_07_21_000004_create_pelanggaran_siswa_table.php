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
        Schema::create('pelanggaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('jenis_id')->constrained('pelanggaran_jenis');
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik');
            $table->date('tanggal_kejadian');
            $table->text('keterangan');
            $table->tinyInteger('poin_saat_itu')->unsigned();
            $table->foreignId('dicatat_oleh')->constrained('users');
            $table->boolean('is_diarsipkan')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['siswa_id', 'tahun_akademik_id'], 'idx_siswa_tahun');
            $table->index('tanggal_kejadian', 'idx_tanggal');
            $table->index('jenis_id', 'idx_jenis');
            $table->index('dicatat_oleh', 'idx_dicatat_oleh');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_siswa');
    }
};
