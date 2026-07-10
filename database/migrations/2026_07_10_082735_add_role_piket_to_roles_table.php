<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['slug' => 'piket'],
            [
                'name' => 'Guru Piket',
                'description' => 'Akses piket harian sekolah untuk mengelola absensi kelas dan izin.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('roles')->where('slug', 'piket')->delete();
    }
};
