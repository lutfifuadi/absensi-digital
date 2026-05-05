<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cache hasil validasi nomor WA di tabel siswa
        if (Schema::hasTable('siswa') && !Schema::hasColumn('siswa', 'wa_number_valid')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->tinyInteger('wa_number_valid')->nullable()->after('no_hp_ortu')
                    ->comment('null=belum dicek, 1=valid WA, 0=tidak valid');
                $table->timestamp('wa_number_checked_at')->nullable()->after('wa_number_valid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('siswa')) {
            Schema::table('siswa', function (Blueprint $table) {
                if (Schema::hasColumn('siswa', 'wa_number_valid')) {
                    $table->dropColumn('wa_number_valid');
                }
                if (Schema::hasColumn('siswa', 'wa_number_checked_at')) {
                    $table->dropColumn('wa_number_checked_at');
                }
            });
        }
    }
};
