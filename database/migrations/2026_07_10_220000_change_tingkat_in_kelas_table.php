<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Mengubah kolom tingkat dari ENUM menjadi VARCHAR(50)
            $table->string('tingkat', 50)->change();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Kembalikan ke ENUM jika rollback
            $table->enum('tingkat', ['X', 'XI', 'XII'])->change();
        });
    }
};
