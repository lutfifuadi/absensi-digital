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
        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_siswa', 'dicatat_oleh')) {
                    $table->foreignId('dicatat_oleh')
                        ->nullable()
                        ->after('guru_id')
                        ->constrained('users')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                if (Schema::hasColumn('absensi_siswa', 'dicatat_oleh')) {
                    $table->dropForeign(['dicatat_oleh']);
                    $table->dropColumn('dicatat_oleh');
                }
            });
        }
    }
};
