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
        Schema::create('pelanggaran_konfigurasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->unique('uk_tahun_akademik')->constrained('tahun_akademik')->onDelete('cascade');
            $table->tinyInteger('batas_sp1')->unsigned()->default(25);
            $table->tinyInteger('batas_sp2')->unsigned()->default(50);
            $table->tinyInteger('batas_sp3')->unsigned()->default(75);
            $table->boolean('notif_wa_aktif')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_konfigurasi');
    }
};
