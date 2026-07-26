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
        Schema::table('kelas_jadwal_absensi', function (Blueprint $table) {
            $table->time('batas_jam_masuk')->nullable()->after('jam_masuk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_jadwal_absensi', function (Blueprint $table) {
            $table->dropColumn('batas_jam_masuk');
        });
    }
};
