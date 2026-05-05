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
        Schema::table('kelas', function (Blueprint $table) {
            $table->boolean('is_aktif_absensi')->default(true)->after('tahun_akademik_id');
            $table->boolean('kustomisasi_jam')->default(false)->after('is_aktif_absensi');
            $table->time('jam_masuk')->nullable()->after('kustomisasi_jam');
            $table->time('jam_pulang')->nullable()->after('jam_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn(['is_aktif_absensi', 'kustomisasi_jam', 'jam_masuk', 'jam_pulang']);
        });
    }
};
