<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PRD-007: Modifikasi tabel absensi untuk penandaan Izin Pulang Cepat
     */
    public function up(): void
    {
        // 1. absensi_siswa
        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_siswa', 'is_pulang_cepat')) {
                    $table->boolean('is_pulang_cepat')->default(false)->after('metode');
                }
                if (!Schema::hasColumn('absensi_siswa', 'izin_pulang_cepat_id')) {
                    $table->foreignId('izin_pulang_cepat_id')
                        ->nullable()
                        ->after('is_pulang_cepat')
                        ->constrained('izin_pulang_cepat')
                        ->nullOnDelete();
                }
            });
        }

        // 2. absensi_guru
        if (Schema::hasTable('absensi_guru')) {
            Schema::table('absensi_guru', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_guru', 'is_pulang_cepat')) {
                    $table->boolean('is_pulang_cepat')->default(false)->after('metode');
                }
                if (!Schema::hasColumn('absensi_guru', 'izin_pulang_cepat_id')) {
                    $table->foreignId('izin_pulang_cepat_id')
                        ->nullable()
                        ->after('is_pulang_cepat')
                        ->constrained('izin_pulang_cepat')
                        ->nullOnDelete();
                }
            });
        }

        // 3. absensi_staff
        if (Schema::hasTable('absensi_staff')) {
            Schema::table('absensi_staff', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_staff', 'is_pulang_cepat')) {
                    $table->boolean('is_pulang_cepat')->default(false)->after('metode');
                }
                if (!Schema::hasColumn('absensi_staff', 'izin_pulang_cepat_id')) {
                    $table->foreignId('izin_pulang_cepat_id')
                        ->nullable()
                        ->after('is_pulang_cepat')
                        ->constrained('izin_pulang_cepat')
                        ->nullOnDelete();
                }
            });
        }

        // 4. absensi_siswa_per_jadwal
        if (Schema::hasTable('absensi_siswa_per_jadwal')) {
            Schema::table('absensi_siswa_per_jadwal', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_siswa_per_jadwal', 'is_pulang_cepat')) {
                    $table->boolean('is_pulang_cepat')->default(false)->after('dicatat_oleh');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('absensi_siswa_per_jadwal')) {
            Schema::table('absensi_siswa_per_jadwal', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_siswa_per_jadwal', 'is_pulang_cepat')) {
                    $table->dropColumn('is_pulang_cepat');
                }
            });
        }

        if (Schema::hasTable('absensi_staff')) {
            Schema::table('absensi_staff', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_staff', 'izin_pulang_cepat_id')) {
                    $table->dropForeign(['izin_pulang_cepat_id']);
                    $table->dropColumn('izin_pulang_cepat_id');
                }
                if (Schema::hasColumn('absensi_staff', 'is_pulang_cepat')) {
                    $table->dropColumn('is_pulang_cepat');
                }
            });
        }

        if (Schema::hasTable('absensi_guru')) {
            Schema::table('absensi_guru', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_guru', 'izin_pulang_cepat_id')) {
                    $table->dropForeign(['izin_pulang_cepat_id']);
                    $table->dropColumn('izin_pulang_cepat_id');
                }
                if (Schema::hasColumn('absensi_guru', 'is_pulang_cepat')) {
                    $table->dropColumn('is_pulang_cepat');
                }
            });
        }

        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_siswa', 'izin_pulang_cepat_id')) {
                    $table->dropForeign(['izin_pulang_cepat_id']);
                    $table->dropColumn('izin_pulang_cepat_id');
                }
                if (Schema::hasColumn('absensi_siswa', 'is_pulang_cepat')) {
                    $table->dropColumn('is_pulang_cepat');
                }
            });
        }
    }
};
