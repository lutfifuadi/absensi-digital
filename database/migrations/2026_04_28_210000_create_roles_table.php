<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            ['slug' => 'super_admin', 'name' => 'Super Admin', 'description' => 'Hak akses penuh ke seluruh sistem.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'admin_sekolah', 'name' => 'Admin Sekolah', 'description' => 'Kelola operasional sekolah dan data master.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'operator', 'name' => 'Operator', 'description' => 'Kelola proses operasional dan pengalaman pengguna.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'guru', 'name' => 'Guru', 'description' => 'Akses portal guru dan data mengajar.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'wali_kelas', 'name' => 'Wali Kelas', 'description' => 'Akses data wali kelas dan siswa yang diasuh.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'staff_tu', 'name' => 'Staff TU', 'description' => 'Akses administrasi tata usaha.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'siswa', 'name' => 'Siswa', 'description' => 'Akses portal siswa untuk absensi dan informasi.', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'orang_tua', 'name' => 'Orang Tua', 'description' => 'Akses portal orang tua untuk memantau anak.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
