<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PRD-007: Fitur Izin Pulang Cepat (Early Departure System)
     */
    public function up(): void
    {
        Schema::create('izin_pulang_cepat', function (Blueprint $table) {
            $table->id();
            $table->string('kode_izin', 30)->unique();
            $table->enum('kategori', ['siswa', 'guru', 'staff'])->default('siswa');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_rencana_keluar');
            $table->time('jam_realisasi_keluar')->nullable();
            $table->text('alasan');
            $table->enum('jenis_alasan', ['sakit', 'urusan_keluarga', 'dinas_luar', 'dispensasi', 'lainnya']);
            $table->string('lampiran', 255)->nullable();
            $table->string('nama_penjemput', 100)->nullable();
            $table->string('no_hp_penjemput', 20)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('disetujui_pada')->nullable();
            $table->text('catatan_approver')->nullable();

            $table->foreignId('diverifikasi_satpam_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diverifikasi_satpam_pada')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['kategori', 'reference_id'], 'idx_kategori_ref');
            $table->index(['tanggal', 'status'], 'idx_tanggal_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin_pulang_cepat');
    }
};
