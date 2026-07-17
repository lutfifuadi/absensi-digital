<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan kolom nullable jurusan_id ke tabel kelas
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('jurusan_id')->nullable()->after('jurusan');
        });

        // 2. Baca data string jurusan lama dari tabel kelas dan mapping ke master jurusan
        $kelasItems = DB::table('kelas')->get();
        foreach ($kelasItems as $kelas) {
            $oldJurusan = trim($kelas->jurusan);
            
            // Mapping dan default value
            $kode = '';
            $nama = '';
            
            if (empty($oldJurusan)) {
                $kode = 'UMUM';
                $nama = 'Umum';
            } else {
                $upper = strtoupper($oldJurusan);
                if ($upper === 'TKJ' || str_contains($upper, 'TEKNIK KOMPUTER')) {
                    $kode = 'TKJ';
                    $nama = 'Teknik Komputer & Jaringan';
                } elseif ($upper === 'TBSM' || str_contains($upper, 'SEPEDA MOTOR') || str_contains($upper, 'TBSM')) {
                    $kode = 'TBSM';
                    $nama = 'Teknik & Bisnis Sepeda Motor';
                } elseif ($upper === 'TABUS' || str_contains($upper, 'BUSANA') || $upper === 'TATA BUSANA') {
                    $kode = 'TABUS';
                    $nama = 'Tata Busana';
                } elseif ($upper === 'UMUM' || $upper === 'GENERAL') {
                    $kode = 'UMUM';
                    $nama = 'Umum';
                } else {
                    // fallback jika ada string lain
                    $kode = substr($upper, 0, 20);
                    $nama = $oldJurusan;
                }
            }

            // Cari atau buat record di tabel jurusan
            $jurusanId = DB::table('jurusan')->where('kode', $kode)->value('id');
            if (!$jurusanId) {
                $jurusanId = DB::table('jurusan')->insertGetId([
                    'kode' => $kode,
                    'nama' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Update kelas dengan jurusan_id padanannya
            DB::table('kelas')->where('id', $kelas->id)->update([
                'jurusan_id' => $jurusanId
            ]);
        }

        // 3. Hapus kolom string jurusan lama dari tabel kelas
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });

        // 4. Tambahkan foreign key constraint
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('jurusan_id')->references('id')->on('jurusan')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->string('jurusan')->nullable()->after('jurusan_id');
        });

        // Restore data dari jurusan_id kembali ke string
        $kelasItems = DB::table('kelas')->get();
        foreach ($kelasItems as $kelas) {
            if ($kelas->jurusan_id) {
                $namaJurusan = DB::table('jurusan')->where('id', $kelas->jurusan_id)->value('nama');
                DB::table('kelas')->where('id', $kelas->id)->update([
                    'jurusan' => $namaJurusan
                ]);
            }
        }

        Schema::table('kelas', function (Blueprint $table) {
            $table->dropColumn('jurusan_id');
        });
    }
};
