<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use App\Models\Mapel;
use Illuminate\Support\Str;

class MigrateGuruMapelDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gurus = Guru::all();

        foreach ($gurus as $guru) {
            $oldMapelName = trim($guru->mata_pelajaran);
            if (empty($oldMapelName)) {
                continue;
            }

            // Cari mapel yang mirip berdasarkan nama_mapel atau kode_mapel
            $mapel = Mapel::where('nama_mapel', 'like', '%' . $oldMapelName . '%')
                ->orWhere('kode_mapel', 'like', '%' . $oldMapelName . '%')
                ->first();

            if (!$mapel) {
                // Jika tidak ada mapel yang cocok, buat data mapel baru.
                // Buat kode mapel secara otomatis dari singkatan nama mapel
                $words = explode(' ', $oldMapelName);
                $codePrefix = '';
                foreach ($words as $word) {
                    $codePrefix .= strtoupper(substr($word, 0, 1));
                }
                $codePrefix = preg_replace('/[^A-Z]/', '', $codePrefix);
                if (empty($codePrefix)) {
                    $codePrefix = 'MPL';
                }
                
                // Cari agar kode_mapel unik
                $kodeMapel = $codePrefix;
                $counter = 1;
                while (Mapel::where('kode_mapel', $kodeMapel)->exists()) {
                    $kodeMapel = $codePrefix . $counter;
                    $counter++;
                }

                $mapel = Mapel::create([
                    'kode_mapel' => $kodeMapel,
                    'nama_mapel' => $oldMapelName,
                    'kelompok' => 'umum',
                    'status' => true,
                ]);
            }

            // Hubungkan ke guru di tabel pivot guru_mapel (jika belum terhubung)
            if (!$guru->mapels()->where('mapels.id', $mapel->id)->exists()) {
                $guru->mapels()->attach($mapel->id);
            }
        }
    }
}
