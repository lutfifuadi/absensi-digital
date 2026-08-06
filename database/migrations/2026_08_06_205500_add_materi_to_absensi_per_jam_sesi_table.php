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
        Schema::table('absensi_per_jam_sesi', function (Blueprint $table) {
            if (!Schema::hasColumn('absensi_per_jam_sesi', 'materi')) {
                $table->text('materi')->nullable()->after('jumlah_alpha');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_per_jam_sesi', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_per_jam_sesi', 'materi')) {
                $table->dropColumn('materi');
            }
        });
    }
};
