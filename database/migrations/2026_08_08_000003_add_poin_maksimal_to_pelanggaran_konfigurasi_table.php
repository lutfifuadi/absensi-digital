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
        Schema::table('pelanggaran_konfigurasi', function (Blueprint $table) {
            if (!Schema::hasColumn('pelanggaran_konfigurasi', 'poin_maksimal')) {
                $table->smallInteger('poin_maksimal')->unsigned()->default(100)->after('batas_sp3');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelanggaran_konfigurasi', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggaran_konfigurasi', 'poin_maksimal')) {
                $table->dropColumn('poin_maksimal');
            }
        });
    }
};
