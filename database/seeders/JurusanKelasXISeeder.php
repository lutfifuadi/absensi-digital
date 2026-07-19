<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JurusanKelasXISeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Menambahkan jurusan baru dan meng-update jurusan_id kelas XI
     * untuk tahun ajaran 2026-2027.
     */
    public function run(): void
    {
        // Mapping jurusan baru (kode => nama)
        $jurusanBaru = [
            ['kode' => 'SAINSTEK',  'nama' => 'Sains Teknik'],
            ['kode' => 'SAINKES',   'nama' => 'Sains Kesehatan'],
            ['kode' => 'SOSPOL',    'nama' => 'Sosial Politik'],
            ['kode' => 'KEUANGAN',  'nama' => 'Keuangan'],
            ['kode' => 'ILMUAGAMA', 'nama' => 'Ilmu Keagamaan'],
        ];

        // Mapping kelas XI => kode jurusan
        $kelasJurusanMap = [
            'XI.F-1'  => 'SAINSTEK',
            'XI.F-2'  => 'SAINSTEK',
            'XI.F-3'  => 'SAINSTEK',
            'XI.F-4'  => 'SAINKES',
            'XI.F-5'  => 'SOSPOL',
            'XI.F-6'  => 'SOSPOL',
            'XI.F-7'  => 'KEUANGAN',
            'XI.F-8'  => 'KEUANGAN',
            'XI.F-9'  => 'ILMUAGAMA',
            'XI.F-10' => 'TKJ',
            'XI.F-11' => 'TBSM',
            'XI.F-12' => 'TABUS',
        ];

        // Mulai database transaction
        DB::transaction(function () use ($jurusanBaru, $kelasJurusanMap) {
            $jurusanDitambahkan = 0;
            $kelasDiupdate = 0;

            // 1. Insert jurusan baru jika belum ada
            foreach ($jurusanBaru as $item) {
                $existing = DB::table('jurusan')->where('kode', $item['kode'])->first();
                if (!$existing) {
                    DB::table('jurusan')->insert([
                        'kode'       => $item['kode'],
                        'nama'       => $item['nama'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $jurusanDitambahkan++;
                    $this->command->info("Jurusan '{$item['nama']}' ({$item['kode']}) ditambahkan.");
                } else {
                    $this->command->info("Jurusan '{$item['nama']}' ({$item['kode']}) sudah ada, dilewati.");
                }
            }

            // 2. Update jurusan_id untuk kelas XI TA 2026-2027
            $tahunAkademikId = 1; // 2026-2027
            foreach ($kelasJurusanMap as $namaKelas => $kodeJurusan) {
                // Cari jurusan_id berdasarkan kode
                $jurusan = DB::table('jurusan')->where('kode', $kodeJurusan)->first();
                if (!$jurusan) {
                    $this->command->warn("Jurusan dengan kode '{$kodeJurusan}' tidak ditemukan, kelas {$namaKelas} dilewati.");
                    continue;
                }

                // Cari kelas XI dengan nama dan tahun akademik tertentu
                $kelas = DB::table('kelas')
                    ->where('nama', $namaKelas)
                    ->where('tingkat', 'XI')
                    ->where('tahun_akademik_id', $tahunAkademikId)
                    ->first();

                if (!$kelas) {
                    $this->command->warn("Kelas '{$namaKelas}' (XI, TA {$tahunAkademikId}) tidak ditemukan.");
                    continue;
                }

                // Update jurusan_id
                DB::table('kelas')
                    ->where('id', $kelas->id)
                    ->update(['jurusan_id' => $jurusan->id]);

                $kelasDiupdate++;
                $this->command->info("Kelas {$namaKelas} -> {$jurusan->nama} (id: {$jurusan->id})");
            }

            // 3. Tampilkan ringkasan
            $this->command->info('');
            $this->command->info('========================================');
            $this->command->info('           RINGKASAN EKSEKUSI');
            $this->command->info('========================================');
            $this->command->info("Jurusan baru ditambahkan : {$jurusanDitambahkan}");
            $this->command->info("Kelas XI yang diupdate   : {$kelasDiupdate}");
            $this->command->info('========================================');
        });
    }
}
