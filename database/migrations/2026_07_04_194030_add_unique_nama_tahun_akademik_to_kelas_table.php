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
        // Bersihkan data duplikat jika ada untuk menghindari kegagalan migration.
        // Kita hanya menyisakan satu record untuk kombinasi (nama, tahun_akademik_id) yang sama.
        // Jika ada duplikasi, simpan data dengan id terkecil (atau terbesar) dan hapus yang lain.
        $duplicates = Illuminate\Support\Facades\DB::table('kelas')
            ->select('nama', 'tahun_akademik_id', Illuminate\Support\Facades\DB::raw('MIN(id) as keep_id'))
            ->groupBy('nama', 'tahun_akademik_id')
            ->having(Illuminate\Support\Facades\DB::raw('COUNT(*)'), '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Dapatkan semua ID duplikat kecuali yang ingin dipertahankan
            $idsToDelete = Illuminate\Support\Facades\DB::table('kelas')
                ->where('nama', $duplicate->nama)
                ->where('tahun_akademik_id', $duplicate->tahun_akademik_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->pluck('id')
                ->toArray();

            if (!empty($idsToDelete)) {
                // Update tabel relasi yang bergantung pada kelas_id yang akan dihapus ke keep_id
                // List tabel relasi: siswa, absensi_siswa, jadwal_pelajaran, class_leaderboards, attendance_analytics, assignments
                Illuminate\Support\Facades\DB::table('siswa')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                Illuminate\Support\Facades\DB::table('absensi_siswa')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                Illuminate\Support\Facades\DB::table('jadwal_pelajaran')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                Illuminate\Support\Facades\DB::table('class_leaderboards')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                Illuminate\Support\Facades\DB::table('attendance_analytics')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                
                // Cek apakah tabel assignments ada
                if (Schema::hasTable('assignments')) {
                    Illuminate\Support\Facades\DB::table('assignments')->whereIn('kelas_id', $idsToDelete)->update(['kelas_id' => $duplicate->keep_id]);
                }

                // Hapus data duplikat di tabel kelas
                Illuminate\Support\Facades\DB::table('kelas')->whereIn('id', $idsToDelete)->delete();
            }
        }

        Schema::table('kelas', function (Blueprint $table) {
            $table->unique(['nama', 'tahun_akademik_id'], 'kelas_nama_ta_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropUnique('kelas_nama_ta_unique');
        });
    }
};
