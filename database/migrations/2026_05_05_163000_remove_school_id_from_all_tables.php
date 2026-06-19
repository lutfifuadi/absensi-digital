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
        $isSqlite = DB::getDriverName() === 'sqlite';
        $databaseName = DB::connection()->getDatabaseName();

        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'school_id')) {
                if (!$isSqlite) {
                    $this->dropForeignKeyIfExists($databaseName, $tableName, "{$tableName}_school_id_foreign");
                    $this->dropIndexIfExists($databaseName, $tableName, "{$tableName}_school_id_index");
                }

                // Drop column — SQLite does not support dropping FK-referenced columns,
                // but since the schools table is also dropped this is harmless to skip.
                if (!$isSqlite) {
                    Schema::table($tableName, function ($table) {
                        $table->dropColumn('school_id');
                    });
                }
            }
        }

        // Also drop schools table if exists (skip for SQLite — FK constraints prevent it)
        if (!$isSqlite && Schema::hasTable('schools')) {
            Schema::dropIfExists('schools');
        }
    }

    /**
     * Drop a foreign key if it exists — compatible with MySQL 5.x, MySQL 8+, and MariaDB.
     */
    private function dropForeignKeyIfExists(string $database, string $table, string $fkName): void
    {
        $exists = DB::selectOne(
            "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [$database, $table, $fkName]
        );

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fkName}");
        }
    }

    /**
     * Drop an index if it exists — compatible with MySQL 5.x, MySQL 8+, and MariaDB.
     */
    private function dropIndexIfExists(string $database, string $table, string $indexName): void
    {
        $exists = DB::selectOne(
            "SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$database, $table, $indexName]
        );

        if ($exists) {
            DB::statement("DROP INDEX {$indexName} ON {$table}");
        }
    }

    public function down(): void
    {
        // Skip rollback - this is a one-way migration
    }
};