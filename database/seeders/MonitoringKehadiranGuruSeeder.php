<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MonitoringKehadiranGuru;
use App\Models\JadwalPelajaran;
use App\Models\User;

class MonitoringKehadiranGuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::pluck('id')->toArray();
        if (empty($users)) {
            $users = [User::factory()->create()->id];
        }

        $jadwalPelajaran = JadwalPelajaran::pluck('id')->toArray();
        
        if (empty($jadwalPelajaran)) {
            // Assume there are valid classes and gurus if jadwal is empty.
            // In a real seeder setup, JadwalPelajaranFactory should exist or we manually create dummy data here.
            // For now, let's gracefully skip or create basic manual data if Factory is missing.
            $kelas = \App\Models\Kelas::first();
            $guru = \App\Models\Guru::first();
            
            if($kelas && $guru) {
                 $jadwal = JadwalPelajaran::create([
                     'kelas_id' => $kelas->id,
                     'guru_id' => $guru->id,
                     'mata_pelajaran' => 'Matematika',
                     'hari' => 'Senin',
                     'jam_mulai' => '07:00:00',
                     'jam_selesai' => '08:30:00',
                 ]);
                 $jadwalPelajaran = [$jadwal->id];
            } else {
                 return; // Skip if no dependencies met.
            }
        }

        $tanggal = date('Y-m-d');
        
        foreach ($jadwalPelajaran as $index => $jadwalId) {
            // Check if it already exists to avoid unique constraint violations
            $exists = MonitoringKehadiranGuru::where('jadwal_pelajaran_id', $jadwalId)
                ->where('tanggal', $tanggal)
                ->exists();

            if ($exists) {
                continue;
            }

            // Randomize status to ensure variety in test data
            $statusChoice = rand(1, 10);
            
            $status = 'hadir';
            $keterangan = null;
            $lamaTerlambat = null;
            
            if ($statusChoice <= 2) {
                $status = 'tidak_hadir';
                $keterangan = 'sakit';
            } elseif ($statusChoice == 3) {
                $status = 'terlambat';
                $lamaTerlambat = 15;
            }

            MonitoringKehadiranGuru::create([
                'jadwal_pelajaran_id' => $jadwalId,
                'tanggal' => $tanggal,
                'status' => $status,
                'keterangan' => $keterangan,
                'lama_terlambat' => $lamaTerlambat,
                'ada_pengganti' => false,
                'dicatat_oleh' => $users[array_rand($users)],
            ]);
            
            // Only seed some of them to test "belum dimonitor" logic
            if ($index >= count($jadwalPelajaran) / 2) {
                 // break;
            }
        }
    }
}
