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
        // Ubah kolom status_dari menjadi nullable karena saat submit pengaduan baru,
        // tidak ada status sebelumnya (status_dari = null).
        Schema::table('log_pengaduan', function (Blueprint $table) {
            $table->enum('status_dari', ['baru', 'diproses', 'selesai', 'ditolak'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke NOT NULL
        Schema::table('log_pengaduan', function (Blueprint $table) {
            $table->enum('status_dari', ['baru', 'diproses', 'selesai', 'ditolak'])->nullable(false)->change();
        });
    }
};
