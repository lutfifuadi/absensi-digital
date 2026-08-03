<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambahkan status 'bolos' pada absensi per jam pelajaran (PRD-006).
     */
    public function up(): void
    {
        if (Schema::hasTable('absensi_siswa_per_jadwal')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE `absensi_siswa_per_jadwal` MODIFY COLUMN `status` ENUM('hadir', 'terlambat', 'sakit', 'izin', 'alpha', 'dispen', 'bolos') NOT NULL");
            }
        }

        if (Schema::hasTable('absensi_per_jam_stat')) {
            Schema::table('absensi_per_jam_stat', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_per_jam_stat', 'bolos')) {
                    $table->unsignedSmallInteger('bolos')->default(0)->after('dispen');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('absensi_per_jam_stat')) {
            Schema::table('absensi_per_jam_stat', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_per_jam_stat', 'bolos')) {
                    $table->dropColumn('bolos');
                }
            });
        }

        if (Schema::hasTable('absensi_siswa_per_jadwal')) {
            if (DB::getDriverName() !== 'sqlite') {
                DB::statement("ALTER TABLE `absensi_siswa_per_jadwal` MODIFY COLUMN `status` ENUM('hadir', 'terlambat', 'sakit', 'izin', 'alpha', 'dispen') NOT NULL");
            }
        }
    }
};
