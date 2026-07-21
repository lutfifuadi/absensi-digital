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
        Schema::create('pelanggaran_notif_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggaran_id')->nullable()->constrained('pelanggaran_siswa')->onDelete('cascade');
            $table->foreignId('sp_id')->nullable()->constrained('pelanggaran_sp')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->string('penerima_no_hp', 20);
            $table->enum('tipe_notif', ['pelanggaran_baru', 'sp_terbit']);
            $table->enum('status', ['sukses', 'gagal', 'pending'])->default('pending');
            $table->text('pesan')->nullable();
            $table->text('respons_gateway')->nullable();
            $table->timestamp('dikirim_pada')->nullable();
            $table->timestamps();

            $table->index('siswa_id', 'idx_siswa_notif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_notif_log');
    }
};
