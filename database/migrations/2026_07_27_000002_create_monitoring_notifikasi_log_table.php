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
        Schema::create('monitoring_notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('tipe', 50);
            $table->timestamp('dikirim_at')->useCurrent();
            $table->timestamps();

            $table->unique(['jadwal_pelajaran_id', 'tanggal', 'tipe'], 'notif_jadwal_tanggal_tipe_unique');
            $table->index('tanggal', 'idx_notif_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_notifikasi_log');
    }
};
