<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PRD-007 (Status Kepegawaian Guru): tambah kategori kepegawaian
     * 'full_time' / 'part_time' pada tabel guru. Default 'full_time'
     * (NOT NULL dengan default) sehingga data lama otomatis full_time
     * tanpa perlu backfill. Tanpa tabel baru — cukup atribut di guru
     * (keputusan Mas Lutfi).
     *
     * Pola guarded Schema::hasColumn / Schema::hasIndex agar idempoten,
     * mengikuti 2026_07_28_000001_add_is_guru_bk_to_guru_table.php.
     */
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'tipe_kepegawaian')) {
                $table->string('tipe_kepegawaian', 20)->default('full_time')->after('jabatan');
            }
            if (!Schema::hasIndex('guru', 'idx_guru_tipe_kepegawaian')) {
                $table->index('tipe_kepegawaian', 'idx_guru_tipe_kepegawaian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasIndex('guru', 'idx_guru_tipe_kepegawaian')) {
                $table->dropIndex('idx_guru_tipe_kepegawaian');
            }
            if (Schema::hasColumn('guru', 'tipe_kepegawaian')) {
                $table->dropColumn('tipe_kepegawaian');
            }
        });
    }
};
