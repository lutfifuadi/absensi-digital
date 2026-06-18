<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EkskulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ========== 1. Pastikan data referensi tersedia ==========

        // Tahun Akademik
        $tahunAkademik = TahunAkademik::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'semester'        => 'genap',
                'tanggal_mulai'   => '2026-01-05',
                'tanggal_selesai' => '2026-06-20',
                'is_aktif'        => true,
            ]
        );

        // Kelas
        $kelas = Kelas::firstOrCreate(
            ['nama' => 'XII RPL'],
            [
                'tingkat'           => 'XII',
                'jurusan'           => 'RPL',
                'tahun_akademik_id' => $tahunAkademik->id,
            ]
        );

        $kelas2 = Kelas::firstOrCreate(
            ['nama' => 'XI TKJ'],
            [
                'tingkat'           => 'XI',
                'jurusan'           => 'TKJ',
                'tahun_akademik_id' => $tahunAkademik->id,
            ]
        );

        // Guru (pembina) — buat user + guru jika belum ada
        $pembinaData = [
            ['nip' => '198501012010011001', 'nama' => 'Budi Santoso, S.Pd.',     'mapel' => 'Olahraga'],
            ['nip' => '198502022010012002', 'nama' => 'Siti Aminah, S.Pd.',      'mapel' => 'Seni Budaya'],
            ['nip' => '198503032010013003', 'nama' => 'Agus Wijaya, S.Pd.',      'mapel' => 'PPKN'],
            ['nip' => '198504042010014004', 'nama' => 'Dewi Lestari, S.Pd.',     'mapel' => 'Biologi'],
            ['nip' => '198505052010015005', 'nama' => 'Rudi Hartono, S.Pd.',     'mapel' => 'Penjaskes'],
        ];

        $gurus = [];
        foreach ($pembinaData as $data) {
            $user = User::firstOrCreate(
                ['email' => strtolower(str_replace([' ', ',', '.'], '', $data['nama'])) . '@sekolah.sch.id'],
                [
                    'name'     => $data['nama'],
                    'username' => strtolower(explode(' ', $data['nama'])[0]) . rand(100, 999),
                    'password' => Hash::make('password'),
                    'role'     => User::ROLE_GURU,
                ]
            );

            $gurus[] = Guru::firstOrCreate(
                ['nip' => $data['nip']],
                [
                    'user_id'       => $user->id,
                    'nama_lengkap'  => $data['nama'],
                    'jenis_kelamin' => (str_contains($data['nama'], 'Siti') || str_contains($data['nama'], 'Dewi')) ? 'P' : 'L',
                    'mata_pelajaran'=> $data['mapel'],
                    'status'        => 'aktif',
                ]
            );
        }

        // Siswa — buat user + siswa jika belum ada
        $siswaData = [
            ['nis' => '2024001', 'nisn' => '0012345678', 'nama' => 'Ahmad Fauzi',     'jk' => 'L', 'kelas_id' => $kelas->id],
            ['nis' => '2024002', 'nisn' => '0012345679', 'nama' => 'Rina Marlina',    'jk' => 'P', 'kelas_id' => $kelas->id],
            ['nis' => '2024003', 'nisn' => '0012345680', 'nama' => 'Doni Pratama',    'jk' => 'L', 'kelas_id' => $kelas->id],
            ['nis' => '2024004', 'nisn' => '0012345681', 'nama' => 'Sari Indah',      'jk' => 'P', 'kelas_id' => $kelas2->id],
            ['nis' => '2024005', 'nisn' => '0012345682', 'nama' => 'Bayu Setiawan',   'jk' => 'L', 'kelas_id' => $kelas2->id],
            ['nis' => '2024006', 'nisn' => '0012345683', 'nama' => 'Nita Anggraini',  'jk' => 'P', 'kelas_id' => $kelas2->id],
            ['nis' => '2024007', 'nisn' => '0012345684', 'nama' => 'Rizky Ramadhan',  'jk' => 'L', 'kelas_id' => $kelas->id],
            ['nis' => '2024008', 'nisn' => '0012345685', 'nama' => 'Putri Rahayu',    'jk' => 'P', 'kelas_id' => $kelas2->id],
            ['nis' => '2024009', 'nisn' => '0012345686', 'nama' => 'Eko Prasetyo',    'jk' => 'L', 'kelas_id' => $kelas->id],
            ['nis' => '2024010', 'nisn' => '0012345687', 'nama' => 'Maya Sari',       'jk' => 'P', 'kelas_id' => $kelas2->id],
        ];

        $siswas = [];
        foreach ($siswaData as $data) {
            $user = User::firstOrCreate(
                ['email' => strtolower(explode(' ', $data['nama'])[0]) . '.siswa@sekolah.sch.id'],
                [
                    'name'     => $data['nama'],
                    'username' => $data['nis'],
                    'password' => Hash::make('password'),
                    'role'     => User::ROLE_SISWA,
                ]
            );

            $siswas[] = Siswa::firstOrCreate(
                ['nis' => $data['nis']],
                [
                    'nisn'              => $data['nisn'],
                    'nama_lengkap'      => $data['nama'],
                    'jenis_kelamin'     => $data['jk'],
                    'tempat_lahir'      => 'Jakarta',
                    'tanggal_lahir'     => '2008-01-01',
                    'kelas_id'          => $data['kelas_id'],
                    'tahun_akademik_id' => $tahunAkademik->id,
                    'status'            => 'aktif',
                ]
            );
        }

        // ========== 2. Buat 5 Ekskul ==========
        $ekskuls = [
            [
                'nama'      => 'Pramuka',
                'kategori'  => 'wajib',
                'deskripsi' => 'Kegiatan pramuka untuk membentuk karakter disiplin, mandiri, dan cinta tanah air.',
                'kuota'     => 50,
                'status'    => true,
                'icon'      => 'pramuka',
            ],
            [
                'nama'      => 'Paskibra',
                'kategori'  => 'wajib',
                'deskripsi' => 'Pasukan pengibar bendera yang melatih kedisiplinan dan kekompakan.',
                'kuota'     => 40,
                'status'    => true,
                'icon'      => 'paskibra',
            ],
            [
                'nama'      => 'PMR',
                'kategori'  => 'pilihan',
                'deskripsi' => 'Palang Merah Remaja — belajar pertolongan pertama dan kesehatan.',
                'kuota'     => 30,
                'status'    => true,
                'icon'      => 'pmr',
            ],
            [
                'nama'      => 'Futsal',
                'kategori'  => 'olahraga',
                'deskripsi' => 'Ekstrakurikuler olahraga futsal untuk mengembangkan bakat dan sportivitas.',
                'kuota'     => 25,
                'status'    => true,
                'icon'      => 'futsal',
            ],
            [
                'nama'      => 'Seni Tari',
                'kategori'  => 'seni',
                'deskripsi' => 'Mengembangkan bakat seni tari tradisional maupun modern.',
                'kuota'     => 35,
                'status'    => true,
                'icon'      => 'seni-tari',
            ],
        ];

        $ekskulIds = [];
        foreach ($ekskuls as $data) {
            $ekskulIds[] = DB::table('ekskul')->insertGetId([
                'nama'       => $data['nama'],
                'kategori'   => $data['kategori'],
                'deskripsi'  => $data['deskripsi'],
                'kuota'      => $data['kuota'],
                'status'     => $data['status'],
                'icon'       => $data['icon'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ========== 3. Buat Jadwal (2-3 per ekskul) ==========
        $jadwalData = [
            // Pramuka (index 0)
            ['ekskul_id' => $ekskulIds[0], 'hari' => 'rabu',   'jam_mulai' => '14:00', 'jam_selesai' => '16:00', 'lokasi' => 'Lapangan Utama'],
            ['ekskul_id' => $ekskulIds[0], 'hari' => 'sabtu',  'jam_mulai' => '08:00', 'jam_selesai' => '11:00', 'lokasi' => 'Lapangan Utama'],
            // Paskibra (index 1)
            ['ekskul_id' => $ekskulIds[1], 'hari' => 'selasa', 'jam_mulai' => '15:00', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Upacara'],
            ['ekskul_id' => $ekskulIds[1], 'hari' => 'kamis',  'jam_mulai' => '15:00', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Upacara'],
            ['ekskul_id' => $ekskulIds[1], 'hari' => 'sabtu',  'jam_mulai' => '07:30', 'jam_selesai' => '10:00', 'lokasi' => 'Lapangan Upacara'],
            // PMR (index 2)
            ['ekskul_id' => $ekskulIds[2], 'hari' => 'senin',  'jam_mulai' => '14:30', 'jam_selesai' => '16:00', 'lokasi' => 'Ruang UKS'],
            ['ekskul_id' => $ekskulIds[2], 'hari' => 'jumat',  'jam_mulai' => '14:30', 'jam_selesai' => '16:00', 'lokasi' => 'Ruang UKS'],
            // Futsal (index 3)
            ['ekskul_id' => $ekskulIds[3], 'hari' => 'selasa', 'jam_mulai' => '15:30', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Futsal'],
            ['ekskul_id' => $ekskulIds[3], 'hari' => 'kamis',  'jam_mulai' => '15:30', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Futsal'],
            // Seni Tari (index 4)
            ['ekskul_id' => $ekskulIds[4], 'hari' => 'rabu',   'jam_mulai' => '14:00', 'jam_selesai' => '16:00', 'lokasi' => 'Aula Seni'],
            ['ekskul_id' => $ekskulIds[4], 'hari' => 'sabtu',  'jam_mulai' => '09:00', 'jam_selesai' => '12:00', 'lokasi' => 'Aula Seni'],
        ];

        foreach ($jadwalData as $data) {
            DB::table('ekskul_jadwal')->insert([
                'ekskul_id'    => $data['ekskul_id'],
                'hari'         => $data['hari'],
                'jam_mulai'    => $data['jam_mulai'],
                'jam_selesai'  => $data['jam_selesai'],
                'lokasi'       => $data['lokasi'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ========== 4. Buat Pembina (1-2 per ekskul) ==========
        $pembinaMapping = [
            // Pramuka: guru[0] Budi + guru[2] Agus
            ['ekskul_id' => $ekskulIds[0], 'guru_id' => $gurus[0]->id, 'jabatan' => 'Pembina Utama'],
            ['ekskul_id' => $ekskulIds[0], 'guru_id' => $gurus[2]->id, 'jabatan' => 'Asisten Pembina'],
            // Paskibra: guru[1] Siti + guru[3] Dewi
            ['ekskul_id' => $ekskulIds[1], 'guru_id' => $gurus[1]->id, 'jabatan' => 'Pembina Utama'],
            ['ekskul_id' => $ekskulIds[1], 'guru_id' => $gurus[3]->id, 'jabatan' => 'Asisten Pembina'],
            // PMR: guru[3] Dewi
            ['ekskul_id' => $ekskulIds[2], 'guru_id' => $gurus[3]->id, 'jabatan' => 'Pembina Utama'],
            // Futsal: guru[0] Budi + guru[4] Rudi
            ['ekskul_id' => $ekskulIds[3], 'guru_id' => $gurus[0]->id, 'jabatan' => 'Pembina Utama'],
            ['ekskul_id' => $ekskulIds[3], 'guru_id' => $gurus[4]->id, 'jabatan' => 'Asisten Pembina'],
            // Seni Tari: guru[1] Siti
            ['ekskul_id' => $ekskulIds[4], 'guru_id' => $gurus[1]->id, 'jabatan' => 'Pembina Utama'],
        ];

        foreach ($pembinaMapping as $data) {
            DB::table('ekskul_pembina')->insert([
                'ekskul_id'  => $data['ekskul_id'],
                'guru_id'    => $data['guru_id'],
                'jabatan'    => $data['jabatan'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ========== 5. Buat Anggota (beberapa siswa per ekskul) ==========
        $anggotaMapping = [
            // Pramuka: 5 anggota
            ['ekskul_id' => $ekskulIds[0], 'siswa_id' => $siswas[0]->id],
            ['ekskul_id' => $ekskulIds[0], 'siswa_id' => $siswas[1]->id],
            ['ekskul_id' => $ekskulIds[0], 'siswa_id' => $siswas[2]->id],
            ['ekskul_id' => $ekskulIds[0], 'siswa_id' => $siswas[6]->id],
            ['ekskul_id' => $ekskulIds[0], 'siswa_id' => $siswas[8]->id],
            // Paskibra: 4 anggota
            ['ekskul_id' => $ekskulIds[1], 'siswa_id' => $siswas[3]->id],
            ['ekskul_id' => $ekskulIds[1], 'siswa_id' => $siswas[4]->id],
            ['ekskul_id' => $ekskulIds[1], 'siswa_id' => $siswas[5]->id],
            ['ekskul_id' => $ekskulIds[1], 'siswa_id' => $siswas[7]->id],
            // PMR: 3 anggota
            ['ekskul_id' => $ekskulIds[2], 'siswa_id' => $siswas[1]->id],
            ['ekskul_id' => $ekskulIds[2], 'siswa_id' => $siswas[5]->id],
            ['ekskul_id' => $ekskulIds[2], 'siswa_id' => $siswas[7]->id],
            // Futsal: 4 anggota
            ['ekskul_id' => $ekskulIds[3], 'siswa_id' => $siswas[0]->id],
            ['ekskul_id' => $ekskulIds[3], 'siswa_id' => $siswas[2]->id],
            ['ekskul_id' => $ekskulIds[3], 'siswa_id' => $siswas[4]->id],
            ['ekskul_id' => $ekskulIds[3], 'siswa_id' => $siswas[6]->id],
            // Seni Tari: 3 anggota
            ['ekskul_id' => $ekskulIds[4], 'siswa_id' => $siswas[1]->id],
            ['ekskul_id' => $ekskulIds[4], 'siswa_id' => $siswas[3]->id],
            ['ekskul_id' => $ekskulIds[4], 'siswa_id' => $siswas[9]->id],
        ];

        foreach ($anggotaMapping as $data) {
            DB::table('ekskul_anggota')->insert([
                'ekskul_id'     => $data['ekskul_id'],
                'siswa_id'      => $data['siswa_id'],
                'status'        => 'aktif',
                'tanggal_masuk' => '2026-01-06',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        // ========== 6. Buat Absensi Contoh ==========
        $absensiData = [];
        $tanggalMulai = now()->subDays(7)->startOfDay();
        for ($i = 0; $i < 5; $i++) {
            $tgl = $tanggalMulai->copy()->addDays($i);
            if ($tgl->isWeekend()) continue;

            foreach ($ekskulIds as $ekskulIdx => $ekskulId) {
                $anggotaEkskul = array_filter($anggotaMapping, fn($a) => $a['ekskul_id'] === $ekskulId);
                foreach ($anggotaEkskul as $anggota) {
                    $statuses = ['hadir', 'hadir', 'hadir', 'izin', 'sakit', 'terlambat'];
                    $absensiData[] = [
                        'ekskul_id'  => $ekskulId,
                        'siswa_id'   => $anggota['siswa_id'],
                        'tanggal'    => $tgl->format('Y-m-d'),
                        'status'     => $statuses[array_rand($statuses)],
                        'jam_absen'  => '14:'.str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT).':'.str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT),
                        'pembina_id' => $gurus[$ekskulIdx % count($gurus)]->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert absensi dalam batch
        foreach (array_chunk($absensiData, 100) as $chunk) {
            DB::table('ekskul_absensi')->insert($chunk);
        }

        // ========== 7. Buat Kegiatan Ekskul ==========
        $kegiatanData = [
            ['ekskul_id' => $ekskulIds[0], 'nama_kegiatan' => 'Persami Semester Genap',        'tanggal' => '2026-02-14', 'deskripsi' => 'Perkemahan Sabtu Minggu untuk anggota Pramuka.'],
            ['ekskul_id' => $ekskulIds[0], 'nama_kegiatan' => 'Lomba Pionering',                 'tanggal' => '2026-03-21', 'deskripsi' => 'Lomba membuat pionering antar regu.'],
            ['ekskul_id' => $ekskulIds[1], 'nama_kegiatan' => 'Latihan Gabungan Paskibraka',     'tanggal' => '2026-02-28', 'deskripsi' => 'Latihan gabungan dengan sekolah lain.'],
            ['ekskul_id' => $ekskulIds[2], 'nama_kegiatan' => 'Pelatihan Pertolongan Pertama',   'tanggal' => '2026-03-07', 'deskripsi' => 'Pelatihan P3K oleh tim PMI.'],
            ['ekskul_id' => $ekskulIds[3], 'nama_kegiatan' => 'Turnamen Futsal Antar Kelas',     'tanggal' => '2026-04-10', 'deskripsi' => 'Turnamen futsal tahunan antar kelas.'],
            ['ekskul_id' => $ekskulIds[4], 'nama_kegiatan' => 'Pentas Seni Akhir Tahun',         'tanggal' => '2026-05-15', 'deskripsi' => 'Pagelaran seni tari tradisional dan modern.'],
        ];

        foreach ($kegiatanData as $data) {
            DB::table('ekskul_kegiatan')->insert([
                'ekskul_id'     => $data['ekskul_id'],
                'nama_kegiatan' => $data['nama_kegiatan'],
                'tanggal'       => $data['tanggal'],
                'deskripsi'     => $data['deskripsi'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }
}
