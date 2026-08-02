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
        Schema::table('pengaduan', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('kode_unik')->constrained('users')->nullOnDelete();
            $table->foreignId('siswa_id')->nullable()->after('user_id')->constrained('siswa')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->after('siswa_id')->constrained('kelas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaduan', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['kelas_id']);
            $table->dropColumn(['user_id', 'siswa_id', 'kelas_id']);
        });
    }
};
