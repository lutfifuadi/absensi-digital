<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Siswa dari PMBM (pendaftar baru) belum memiliki kelas dan tahun akademik.
     */
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->change();
            $table->foreignId('tahun_akademik_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable(false)->change();
            $table->foreignId('tahun_akademik_id')->nullable(false)->change();
        });
    }
};
