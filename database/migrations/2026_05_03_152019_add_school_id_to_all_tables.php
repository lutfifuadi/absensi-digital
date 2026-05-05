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
        'kegiatan', 'jadwal_pelajaran', 'tahun_akademik', 'pengaturan',
        'reminder_settings', 'notification_templates', 'holidays',
        'authorized_devices', 'badges'
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Phase 1: Add nullable school_id
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('school_id')->nullable()->after('id');
                });
            }
        }

        // Phase 2: Insert first school and update all tables
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Sekolah Pertama',
            'subdomain' => 'sekolah1',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                DB::table($tableName)->update(['school_id' => $schoolId]);
            }
        }

        // Phase 3 & 4: Enforce NOT NULL, add index and foreign key
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                DB::statement("ALTER TABLE {$tableName} MODIFY school_id BIGINT UNSIGNED NOT NULL");
                
                Schema::table($tableName, function (Blueprint $table) {
                    $table->index('school_id');
                    $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['school_id']);
                    $table->dropIndex(['school_id']);
                    $table->dropColumn('school_id');
                });
            }
        }
    }
};
