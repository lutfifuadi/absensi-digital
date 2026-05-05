<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $tables = [
        'users', 'siswa', 'guru', 'staff_tata_usaha', 'kelas',
        'absensi_siswa', 'absensi_guru', 'absensi_staff', 'izin_sakit',
        'kelas', 'kegiatan', 'jadwal_pelajaran', 'tahun_akademik',
        'pengaturan', 'reminder_settings', 'notification_templates',
        'holidays', 'authorized_devices', 'badges'
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'school_id')) {
                // Drop foreign key first
                DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY IF EXISTS {$tableName}_school_id_foreign");
                // Drop index
                DB::statement("DROP INDEX IF EXISTS {$tableName}_school_id_index ON {$tableName}");
                // Drop column
                Schema::table($tableName, function ($table) {
                    $table->dropColumn('school_id');
                });
            }
        }

        // Also drop schools table if exists
        if (Schema::hasTable('schools')) {
            Schema::dropIfExists('schools');
        }
    }

    public function down(): void
    {
        // Skip rollback - this is a one-way migration
    }
};