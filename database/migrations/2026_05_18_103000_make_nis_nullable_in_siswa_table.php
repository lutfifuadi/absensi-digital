<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropUnique(['nis']);
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('nis')->nullable()->change();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->unique('nis');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropUnique(['nis']);
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->string('nis')->nullable(false)->change();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->unique('nis');
        });
    }
};
