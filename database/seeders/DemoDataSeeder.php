<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use App\Models\Jurusan;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\IzinSakit;
use App\Models\Pengaturan;
use App\Models\StaffTataUsaha;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Data dummy realistis untuk presentasi klien.
     * Menghasilkan: TA, jurusan, kelas, user (semua role), guru, siswa,
     * staff TU, mapel, jadwal, absensi 1 bulan, izin/sakit, pengaturan.
     */
    public function run(): void
    {
        $this->command?->warn('⏳ Memulai DemoDataSeeder...');

        // ================================================================
        //  1. TAHUN AKADEMIK
        // ================================================================
        $taAktif = TahunAkademik::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'semester'        => 'genap',
                'tanggal_mulai'   => '2026-01-05',
                'tanggal_selesai' => '2026-06-20',
                'is_aktif'        => true,
            ]
        );

        $taSebelumnya = TahunAkademik::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'semester'        => 'ganjil',
                'tanggal_mulai'   => '2025-07-14',
                'tanggal_selesai' => '2025-12-20',
                'is_aktif'        => false,
            ]
        );

        // ================================================================
        //  2. JURUSAN  (sekaligus, karena JurusanSeeder hanya 4)
        // ================================================================
        $jurusanData = [
            ['kode' => 'UMUM',  'nama' => 'Umum'],
            ['kode' => 'TKJ',   'nama' => 'Teknik Komputer & Jaringan'],
            ['kode' => 'TBSM',  'nama' => 'Teknik & Bisnis Sepeda Motor'],
            ['kode' => 'TABUS', 'nama' => 'Tata Busana'],
            ['kode' => 'AKL',   'nama' => 'Akuntansi & Keuangan Lembaga'],
            ['kode' => 'OTKP',  'nama' => 'Otomatisasi & Tata Kelola Perkantoran'],
            ['kode' => 'BDP',   'nama' => 'Bisnis Daring & Pemasaran'],
            ['kode' => 'MM',    'nama' => 'Multimedia'],
        ];
        $jurusanMap = [];
        foreach ($jurusanData as $d) {
            $jurusanMap[$d['kode']] = Jurusan::firstOrCreate(
                ['kode' => $d['kode']],
                ['nama' => $d['nama']]
            )->id;
        }

        // ================================================================
        //  3. KELAS  — 3 tingkat × beberapa jurusan
        // ================================================================
        $kelasTemplate = [
            // (tingkat, jurusanKode, jumlahRombel)
            ['X',  'UMUM', 2],  // kelas 10 belum ada jurusan → UMUM
            ['X',  'TKJ',  2],
            ['X',  'TBSM', 1],
            ['X',  'AKL',  1],
            ['XI', 'TKJ',  2],
            ['XI', 'TBSM', 1],
            ['XI', 'AKL',  1],
            ['XI', 'OTKP', 1],
            ['XII', 'TKJ',  2],
            ['XII', 'TBSM', 1],
            ['XII', 'TABUS',1],
            ['XII', 'MM',   1],
        ];

        $kelasList = [];
        foreach ($kelasTemplate as $tmpl) {
            [$tingkat, $jurKode, $jml] = $tmpl;
            for ($i = 1; $i <= $jml; $i++) {
                $nama = "{$tingkat} {$jurKode} {$i}";
                $kelas = Kelas::firstOrCreate(
                    ['nama' => $nama, 'tahun_akademik_id' => $taAktif->id],
                    [
                        'tingkat'           => $tingkat,
                        'jurusan_id'        => $jurusanMap[$jurKode],
                        'tahun_akademik_id' => $taAktif->id,
                        'is_aktif_absensi'  => true,
                        'kustomisasi_jam'   => false,
                    ]
                );
                $kelasList[] = $kelas;
            }
        }
        $this->command?->info('✓ ' . count($kelasList) . ' kelas dibuat');

        // ================================================================
        //  4. USER & GURU  (10 guru + wali kelas)
        // ================================================================
        $guruData = [
            // [nip, nama, jk, mapel, jabatan, role (guru/wali_kelas)]
            ['197808122005011003', 'Drs. H. Ahmad Syukri, M.Pd.',    'L', 'Matematika',        'Wakil Kepala Sekolah',       User::ROLE_GURU],
            ['198103212006042005', 'Dra. Hj. Siti Nurjanah, S.Pd.',  'P', 'Bahasa Indonesia',  'Guru Senior',                User::ROLE_WALI_KELAS],
            ['198507152010011008', 'Bambang Supriyadi, S.Kom.',      'L', 'Informatika',       'Kepala Jurusan TKJ',         User::ROLE_GURU],
            ['198612242011012003', 'Rina Marlina, S.Pd.',            'P', 'Bahasa Inggris',    'Guru Bahasa',                User::ROLE_WALI_KELAS],
            ['199001052015011004', 'Doni Prasetyo, S.Pd.',           'L', 'Penjaskes',         'Pembina Olahraga',           User::ROLE_GURU],
            ['199102162015012005', 'Dewi Sartika, S.Pd.',            'P', 'Seni Budaya',       'Pembina Seni',               User::ROLE_GURU],
            ['198907232014021006', 'Eko Wahyudi, S.T.',              'L', 'Teknik Komputer',   'Guru Produktif TKJ',         User::ROLE_WALI_KELAS],
            ['199203112015031007', 'Fajar Nugroho, S.Pd.',           'L', 'Matematika',        'Guru Matematika',            User::ROLE_GURU],
            ['198811052013012008', 'Fitri Handayani, S.Pd.',         'P', 'PPKN',              'Guru PPKN',                  User::ROLE_WALI_KELAS],
            ['199307202016041009', 'Gilang Pratama, S.Pd.',          'L', 'Sejarah',           'Guru Sejarah',               User::ROLE_GURU],
            ['198412152010011010', 'Hendra Gunawan, S.Ag.',          'L', 'Pendidikan Agama',  'Guru PAI',                   User::ROLE_GURU],
            ['199508122017052011', 'Indah Permata Sari, S.Pd.',      'P', 'Biologi',           'Guru IPA',                   User::ROLE_WALI_KELAS],
        ];

        $guruModels = [];
        foreach ($guruData as $i => $data) {
            [$nip, $nama, $jk, $mapel, $jabatan, $role] = $data;
            $username = Str::slug(explode(' ', $nama)[0]) . $i;

            $user = User::firstOrCreate(
                ['email' => strtolower(explode(' ', $nama)[0]) . $i . '@madrasah.test'],
                [
                    'name'     => $nama,
                    'username' => $username,
                    'password' => Hash::make('password'),
                    'role'     => $role,
                    'status'   => 'aktif',
                ]
            );

            $guru = Guru::firstOrCreate(
                ['nip' => $nip],
                [
                    'user_id'        => $user->id,
                    'nama_lengkap'   => $nama,
                    'jenis_kelamin'  => $jk,
                    'mata_pelajaran' => $mapel,
                    'jabatan'        => $jabatan,
                    'no_hp'          => '0812' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'status'         => 'aktif',
                ]
            );
            $guruModels[] = $guru;
        }

        // Assign wali kelas ke kelas pertama yang match
        $waliCandidates = array_filter($guruModels, fn($g) => $g->user->role === User::ROLE_WALI_KELAS);
        $waliIds = array_values(array_map(fn($g) => $g->id, $waliCandidates));
        foreach ($kelasList as $idx => $kelas) {
            if (isset($waliIds[$idx % count($waliIds)])) {
                $kelas->wali_kelas_id = $waliIds[$idx % count($waliIds)];
                $kelas->save();
            }
        }
        $this->command?->info('✓ ' . count($guruModels) . ' guru dibuat');

        // ================================================================
        //  5. STAFF TATA USAHA
        // ================================================================
        $staffData = [
            ['197501012005012001', 'Tati Sumiati, S.E.',     'P', 'Kepala TU'],
            ['198002152006031002', 'Asep Rudi, S.Kom.',      'L', 'Staf Administrasi'],
            ['198503202010042003', 'Rina Marlina, A.Md.',    'P', 'Staf Kepegawaian'],
        ];
        $staffModels = [];
        foreach ($staffData as $i => $d) {
            [$nip, $nama, $jk, $jabatan] = $d;
            $user = User::firstOrCreate(
                ['email' => 'staff' . $i . '@madrasah.test'],
                [
                    'name'     => $nama,
                    'username' => 'staff' . $i,
                    'password' => Hash::make('password'),
                    'role'     => User::ROLE_STAFF_TU,
                    'status'   => 'aktif',
                ]
            );
            $staffModels[] = StaffTataUsaha::firstOrCreate(
                ['nip' => $nip],
                [
                    'user_id'      => $user->id,
                    'nama_lengkap' => $nama,
                    'jenis_kelamin'=> $jk,
                    'jabatan'      => $jabatan,
                    'no_hp'        => '0878' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'status'       => 'aktif',
                ]
            );
        }
        $this->command?->info('✓ ' . count($staffModels) . ' staff TU dibuat');

        // ================================================================
        //  6. USER ADMIN / OPERATOR / SUPER ADMIN
        // ================================================================
        $adminUsers = [
            ['name' => 'Super Admin',     'username' => 'superadmin',   'email' => 'superadmin@madrasah.test',      'role' => User::ROLE_SUPER_ADMIN],
            ['name' => 'Admin Sekolah',   'username' => 'adminsekolah', 'email' => 'admin_sekolah@madrasah.test',   'role' => User::ROLE_ADMIN_SEKOLAH],
            ['name' => 'Operator',        'username' => 'operator',     'email' => 'operator@madrasah.test',        'role' => User::ROLE_OPERATOR],
            ['name' => 'Guru Piket',      'username' => 'gurupiket',    'email' => 'piket@madrasah.test',           'role' => User::ROLE_PIKET],
        ];
        foreach ($adminUsers as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'username' => $data['username'],
                    'password' => Hash::make('password'),
                    'role'     => $data['role'],
                    'status'   => 'aktif',
                ]
            );
        }
        $this->command?->info('✓ 4 user admin/operator dibuat');

        // ================================================================
        //  7. SISWA  — 5-8 siswa per kelas
        // ================================================================
        $siswaModels = [];
        $noUrutSiswa = 1;
        $namaDepanL = ['Ahmad','Rizky','Doni','Fajar','Hendra','Bayu','Dimas','Irfan','Rudi','Adi',
                       'Budi','Cahyo','Eko','Gilang','Hari','Ilham','Joko','Kurnia','Lukman','Miftah',
                       'Nanda','Oki','Prabowo','Qori','Rafi','Sandy','Teguh','Ujang','Vino','Wawan'];
        $namaDepanP = ['Siti','Rina','Dewi','Fitri','Indah','Nurul','Aisyah','Betty','Citra','Diana',
                       'Ella','Fara','Gita','Hana','Ika','Juni','Kartika','Laras','Maya','Nadia',
                       'Olivia','Putri','Queen','Rani','Sari','Tari','Umi','Vina','Winda','Yuli'];
        $namaBelakang = ['Wijaya','Kusuma','Pratama','Ningsih','Saputra','Hidayat','Lestari','Utami',
                         'Santoso','Anggraini','Ramadhan','Putra','Sari','Wulandari','Setiawan',
                         'Fitriani','Permadi','Handayani','Gunawan','Hartati'];

        foreach ($kelasList as $kelas) {
            // Tentukan jurusan untuk kelas ini
            $jurusanKelas = $kelas->jurusan->kode ?? 'UMUM';
            $tingkatAngka = $kelas->tingkat === 'X' ? 1 : ($kelas->tingkat === 'XI' ? 2 : 3);
            $tahunAjaran = 2025;
            $tahunMasuk = $tahunAjaran - $tingkatAngka + 1;

            $jmlSiswa = rand(5, 8);
            for ($s = 0; $s < $jmlSiswa; $s++) {
                $jk = rand(0, 1) ? 'L' : 'P';
                $depan = $jk === 'L' ? $namaDepanL[array_rand($namaDepanL)] : $namaDepanP[array_rand($namaDepanP)];
                $belakang = $namaBelakang[array_rand($namaBelakang)];
                $namaLengkap = $depan . ' ' . $belakang;

                $nis  = str_pad((string) $tahunMasuk, 2, '0', STR_PAD_LEFT) . str_pad((string) $noUrutSiswa, 4, '0', STR_PAD_LEFT);
                $nisn = '00' . str_pad((string) $noUrutSiswa, 8, '0', STR_PAD_LEFT);
                $noUrutSiswa++;

                $user = User::firstOrCreate(
                    ['email' => strtolower($depan . '.' . $belakang . $s . '@siswa.madrasah.test'],
                    [
                        'name'     => $namaLengkap,
                        'username' => $nis,
                        'password' => Hash::make('password'),
                        'role'     => User::ROLE_SISWA,
                        'status'   => 'aktif',
                    ]
                );

                $siswa = Siswa::firstOrCreate(
                    ['nis' => $nis],
                    [
                        'user_id'           => $user->id,
                        'nisn'              => $nisn,
                        'nama_lengkap'      => $namaLengkap,
                        'jenis_kelamin'     => $jk,
                        'tempat_lahir'      => collect(['Jakarta','Bandung','Semarang','Surabaya','Yogyakarta','Malang','Bekasi','Tangerang','Bogor','Depok'])->random(),
                        'tanggal_lahir'     => Carbon::create($tahunMasuk + 14, rand(1, 12), rand(1, 28))->format('Y-m-d'),
                        'alamat'            => 'Jl. ' . collect(['Merdeka','Sudirman','Ahmad Yani','Diponegoro','Gajah Mada','Pahlawan','Kemerdekaan'])->random() . ' No. ' . rand(1, 200),
                        'no_hp'             => '0813' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                        'kelas_id'          => $kelas->id,
                        'tahun_akademik_id' => $taAktif->id,
                        'status'            => 'aktif',
                    ]
                );
                $siswaModels[] = $siswa;
            }
        }
        $this->command?->info('✓ ' . count($siswaModels) . ' siswa dibuat');

        // ================================================================
        //  8. ORANG TUA  — 1 orang tua per 2-3 siswa
        // ================================================================
        $ortuCount = 0;
        foreach (array_chunk($siswaModels, rand(2, 3)) as $chunk) {
            $namaOrtu = collect(['Ayah','Ibu'])->random();
            $namaDepanOrtu = $namaDepanL[array_rand($namaDepanL)];
            $namaLengkapOrtu = $namaDepanOrtu . ' ' . $namaBelakang[array_rand($namaBelakang)];
            if ($namaOrtu === 'Ibu') {
                $namaLengkapOrtu = $namaDepanP[array_rand($namaDepanP)] . ' ' . $namaBelakang[array_rand($namaBelakang)];
            }

            $ortuUser = User::firstOrCreate(
                ['email' => 'ortu.' . strtolower(str_replace(' ', '', $namaLengkapOrtu)) . '@madrasah.test'],
                [
                    'name'     => $namaLengkapOrtu . ' (' . $namaOrtu . ')',
                    'username' => 'ortu_' . Str::random(4),
                    'password' => Hash::make('password'),
                    'role'     => User::ROLE_ORANG_TUA,
                    'no_hp'    => '0856' . str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'hubungan' => $namaOrtu,
                    'status'   => 'aktif',
                ]
            );

            foreach ($chunk as $siswa) {
                $siswa->ortuUser()->associate($ortuUser);
                $siswa->save();
                // Juga tambahkan ke pivot siswa_ortu
                if (!$siswa->ortu()->where('ortu_user_id', $ortuUser->id)->exists()) {
                    $siswa->ortu()->attach($ortuUser->id);
                }
            }
            $ortuCount++;
        }
        $this->command?->info('✓ ' . $ortuCount . ' orang tua dibuat');

        // ================================================================
        //  9. MAPEL  (Mata Pelajaran)
        // ================================================================
        $mapelData = [
            // [kode, nama, kelompok]
            ['MPL-001', 'Matematika',                    'umum'],
            ['MPL-002', 'Bahasa Indonesia',              'umum'],
            ['MPL-003', 'Bahasa Inggris',                'umum'],
            ['MPL-004', 'Pendidikan Agama Islam',        'umum'],
            ['MPL-005', 'PPKN',                          'umum'],
            ['MPL-006', 'Sejarah Indonesia',             'umum'],
            ['MPL-007', 'Penjaskes',                     'umum'],
            ['MPL-008', 'Seni Budaya',                   'umum'],
            ['MPL-009', 'Biologi',                       'umum'],
            ['MPL-010', 'Fisika',                        'umum'],
            ['MPL-011', 'Kimia',                         'umum'],
            ['MPL-012', 'Informatika',                   'umum'],
            ['MPL-013', 'Teknik Komputer & Jaringan',    'kejuruan'],
            ['MPL-014', 'Pemrograman Web',               'kejuruan'],
            ['MPL-015', 'Administrasi Server',           'kejuruan'],
            ['MPL-016', 'Teknik Sepeda Motor',           'kejuruan'],
            ['MPL-017', 'Elektronika Dasar',             'kejuruan'],
            ['MPL-018', 'Akuntansi Dasar',               'kejuruan'],
            ['MPL-019', 'Ekonomi Bisnis',                'kejuruan'],
            ['MPL-020', 'Administrasi Perkantoran',      'kejuruan'],
            ['MPL-021', 'Desain Grafis',                 'kejuruan'],
            ['MPL-022', 'Bahasa Sunda',                  'muatan_lokal'],
            ['MPL-023', 'Bahasa Jepang',                 'muatan_lokal'],
        ];

        $mapelModels = [];
        foreach ($mapelData as $d) {
            $mapelModels[] = Mapel::firstOrCreate(
                ['kode_mapel' => $d[0]],
                [
                    'nama_mapel' => $d[1],
                    'kelompok'   => $d[2],
                    'status'     => true,
                ]
            );
        }
        $this->command?->info('✓ ' . count($mapelModels) . ' mapel dibuat');

        // ================================================================
        //  10. GURU_MAPEL  (pivot — relasi banyak ke banyak)
        // ================================================================
        // Setiap guru mengajar 2-3 mapel
        foreach ($guruModels as $guru) {
            $acakMapel = collect($mapelModels)->random(rand(2, 3));
            foreach ($acakMapel as $mapel) {
                if (!$guru->mapels()->where('mapel_id', $mapel->id)->exists()) {
                    $guru->mapels()->attach($mapel->id);
                }
            }
        }
        $this->command?->info('✓ Relasi guru_mapel dibuat');

        // ================================================================
        //  11. JADWAL PELAJARAN
        // ================================================================
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jamMulaiList = ['07:00', '07:45', '08:30', '09:30', '10:15', '11:00', '12:00', '12:45', '13:30', '14:15'];
        $durasi = 45; // menit

        $jadwalCount = 0;
        foreach ($kelasList as $kelas) {
            $hariTerpakai = [];
            $mapelKelas = collect($mapelModels)->random(min(8, count($mapelModels)));

            foreach ($mapelKelas as $idx => $mapel) {
                $hari = $hariList[$idx % count($hariList)];
                $jamIdx = array_rand($jamMulaiList);
                $jamMulai = $jamMulaiList[$jamIdx];
                $jamSelesai = Carbon::createFromFormat('H:i', $jamMulai)->addMinutes($durasi)->format('H:i');
                $guruPengajar = $guruModels[array_rand($guruModels)];

                JadwalPelajaran::firstOrCreate(
                    [
                        'kelas_id'       => $kelas->id,
                        'hari'           => $hari,
                        'jam_mulai'      => $jamMulai,
                        'mata_pelajaran' => $mapel->nama_mapel,
                    ],
                    [
                        'guru_id'    => $guruPengajar->id,
                        'jam_selesai'=> $jamSelesai,
                    ]
                );
                $jadwalCount++;
            }
        }
        $this->command?->info('✓ ' . $jadwalCount . ' jadwal pelajaran dibuat');

        // ================================================================
        //  12. ABSENSI SISWA  — 1 bulan terakhir (hari kerja saja)
        // ================================================================
        $this->command?->warn('   Membuat absensi siswa...');
        $tanggalMulai = Carbon::now()->subMonth()->startOfDay();
        $tanggalAkhir = Carbon::now()->subDay()->startOfDay();
        $statusOptions = ['hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'sakit', 'izin', 'alpha'];
        $metodeOptions = ['manual', 'qrcode', 'face', 'auto'];

        $absensiSiswaBuffer = [];
        $now = now();
        $tanggalIterasi = $tanggalMulai->copy();
        $absensiCount = 0;

        while ($tanggalIterasi->lte($tanggalAkhir)) {
            if ($tanggalIterasi->isWeekend()) {
                $tanggalIterasi->addDay();
                continue;
            }

            $tglStr = $tanggalIterasi->format('Y-m-d');
            foreach ($siswaModels as $siswa) {
                $status = $statusOptions[array_rand($statusOptions)];
                $jamMasuk = null;
                $jamPulang = null;

                if (in_array($status, ['hadir', 'terlambat'])) {
                    $jamDasar = $status === 'hadir' ? '06:45' : '07:30';
                    $jamMenit = rand(0, 30);
                    $jamMasuk = Carbon::createFromFormat('H:i', $jamDasar)->addMinutes($jamMenit)->format('H:i:s');
                    $jamPulang = Carbon::createFromFormat('H:i', '15:00')->addMinutes(rand(0, 60))->format('H:i:s');
                } elseif ($status === 'sakit') {
                    $jamMasuk = null;
                    $jamPulang = null;
                } elseif ($status === 'izin') {
                    $jamMasuk = null;
                    $jamPulang = null;
                }

                $absensiSiswaBuffer[] = [
                    'siswa_id'   => $siswa->id,
                    'kelas_id'   => $siswa->kelas_id,
                    'tanggal'    => $tglStr,
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status'     => $status,
                    'keterangan' => $status === 'sakit' ? 'Sakit' : ($status === 'izin' ? 'Ada keperluan keluarga' : null),
                    'guru_id'    => $guruModels[array_rand($guruModels)]->id,
                    'metode'     => $metodeOptions[array_rand($metodeOptions)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $absensiCount++;

                // Batch insert tiap 500 record (gunakan insertOrIgnore utk hindari duplikat)
                if (count($absensiSiswaBuffer) >= 500) {
                    AbsensiSiswa::insertOrIgnore($absensiSiswaBuffer);
                    $absensiSiswaBuffer = [];
                }
            }
            $tanggalIterasi->addDay();
        }
        if (!empty($absensiSiswaBuffer)) {
            AbsensiSiswa::insertOrIgnore($absensiSiswaBuffer);
        }
        $this->command?->info('✓ ' . $absensiCount . ' absensi siswa dibuat');

        // ================================================================
        //  13. ABSENSI GURU  — 1 bulan terakhir
        // ================================================================
        $this->command?->warn('   Membuat absensi guru...');
        $statusGuruOptions = ['hadir', 'hadir', 'hadir', 'terlambat', 'sakit', 'izin'];
        $absensiGuruBuffer = [];
        $absensiGuruCount = 0;
        $tanggalIterasi = $tanggalMulai->copy();

        while ($tanggalIterasi->lte($tanggalAkhir)) {
            if ($tanggalIterasi->isWeekend()) {
                $tanggalIterasi->addDay();
                continue;
            }

            $tglStr = $tanggalIterasi->format('Y-m-d');
            foreach ($guruModels as $guru) {
                $status = $statusGuruOptions[array_rand($statusGuruOptions)];
                $jamMasuk = $jamPulang = null;

                if (in_array($status, ['hadir', 'terlambat'])) {
                    $jamDasar = $status === 'hadir' ? '06:50' : '07:30';
                    $jamMenit = rand(0, 25);
                    $jamMasuk = Carbon::createFromFormat('H:i', $jamDasar)->addMinutes($jamMenit)->format('H:i:s');
                    $jamPulang = Carbon::createFromFormat('H:i', '15:00')->addMinutes(rand(0, 90))->format('H:i:s');
                }

                $absensiGuruBuffer[] = [
                    'guru_id'    => $guru->id,
                    'tanggal'    => $tglStr,
                    'jam_masuk'  => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status'     => $status,
                    'keterangan' => $status === 'sakit' ? 'Sakit' : ($status === 'izin' ? 'Izin' : null),
                    'metode'     => $metodeOptions[array_rand($metodeOptions)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $absensiGuruCount++;

                if (count($absensiGuruBuffer) >= 500) {
                    AbsensiGuru::insertOrIgnore($absensiGuruBuffer);
                    $absensiGuruBuffer = [];
                }
            }
            $tanggalIterasi->addDay();
        }
        if (!empty($absensiGuruBuffer)) {
            AbsensiGuru::insertOrIgnore($absensiGuruBuffer);
        }
        $this->command?->info('✓ ' . $absensiGuruCount . ' absensi guru dibuat');

        // ================================================================
        //  14. IZIN / SAKIT  — beberapa sample
        // ================================================================
        $izinData = [
            // Siswa izin
            [
                'tipe'           => 'siswa',
                'reference_id'   => $siswaModels[0]->id,
                'user_id'        => $siswaModels[0]->user_id,
                'tanggal_mulai'  => Carbon::now()->subDays(5)->format('Y-m-d'),
                'tanggal_selesai'=> Carbon::now()->subDays(4)->format('Y-m-d'),
                'jenis'          => 'izin',
                'keterangan'     => 'Ada acara keluarga',
                'status'         => 'disetujui',
                'disetujui_oleh' => $guruModels[0]->user_id,
            ],
            // Siswa sakit
            [
                'tipe'           => 'siswa',
                'reference_id'   => $siswaModels[1]->id,
                'user_id'        => $siswaModels[1]->user_id,
                'tanggal_mulai'  => Carbon::now()->subDays(3)->format('Y-m-d'),
                'tanggal_selesai'=> Carbon::now()->subDays(2)->format('Y-m-d'),
                'jenis'          => 'sakit',
                'keterangan'     => 'Demam dan flu',
                'status'         => 'disetujui',
                'disetujui_oleh' => $guruModels[1]->user_id,
            ],
            // Guru izin
            [
                'tipe'           => 'guru',
                'reference_id'   => $guruModels[2]->id,
                'user_id'        => $guruModels[2]->user_id,
                'tanggal_mulai'  => Carbon::now()->subDays(7)->format('Y-m-d'),
                'tanggal_selesai'=> Carbon::now()->subDays(7)->format('Y-m-d'),
                'jenis'          => 'izin',
                'keterangan'     => 'Ada pelatihan di luar',
                'status'         => 'disetujui',
                'disetujui_oleh' => User::where('role', User::ROLE_ADMIN_SEKOLAH)->first()?->id ?? 1,
            ],
            // Izin pending
            [
                'tipe'           => 'siswa',
                'reference_id'   => $siswaModels[2]->id,
                'user_id'        => $siswaModels[2]->user_id,
                'tanggal_mulai'  => Carbon::now()->addDays(2)->format('Y-m-d'),
                'tanggal_selesai'=> Carbon::now()->addDays(2)->format('Y-m-d'),
                'jenis'          => 'izin',
                'keterangan'     => 'Izin tidak masuk',
                'status'         => 'pending',
                'disetujui_oleh' => null,
            ],
        ];

        foreach ($izinData as $d) {
            IzinSakit::firstOrCreate(
                [
                    'tipe'          => $d['tipe'],
                    'reference_id'  => $d['reference_id'],
                    'tanggal_mulai' => $d['tanggal_mulai'],
                ],
                $d
            );
        }
        $this->command?->info('✓ ' . count($izinData) . ' izin/sakit sample dibuat');

        // ================================================================
        //  15. PENGATURAN  (setting aplikasi)
        // ================================================================
        $pengaturanData = [
            ['key' => 'nama_sekolah',       'value' => 'SMK Negeri 1 Madani',             'group' => 'sekolah'],
            ['key' => 'alamat_sekolah',     'value' => 'Jl. Pendidikan No. 123, Kota',   'group' => 'sekolah'],
            ['key' => 'telp_sekolah',       'value' => '(021) 12345678',                  'group' => 'sekolah'],
            ['key' => 'email_sekolah',      'value' => 'info@smkn1madani.sch.id',          'group' => 'sekolah'],
            ['key' => 'jam_masuk_default',  'value' => '07:00',                           'group' => 'absensi'],
            ['key' => 'jam_pulang_default', 'value' => '15:00',                           'group' => 'absensi'],
            ['key' => 'batas_terlambat',    'value' => '15',                              'group' => 'absensi'],
            ['key' => 'toleransi_terlambat','value' => '10',                              'group' => 'absensi'],
            ['key' => 'semester_aktif',     'value' => 'genap',                           'group' => 'akademik'],
            ['key' => 'tahun_ajaran',       'value' => '2025/2026',                       'group' => 'akademik'],
            ['key' => 'metode_absensi',     'value' => 'qrcode,face,manual',              'group' => 'absensi'],
            ['key' => 'wa_gateway_active',  'value' => 'true',                            'group' => 'notifikasi'],
            ['key' => 'wa_api_key',         'value' => Str::random(32),                   'group' => 'notifikasi'],
            ['key' => 'wa_nomor',           'value' => '6281212345678',                   'group' => 'notifikasi'],
        ];

        foreach ($pengaturanData as $d) {
            Pengaturan::firstOrCreate(
                ['key' => $d['key']],
                ['value' => $d['value'], 'group' => $d['group']]
            );
        }
        $this->command?->info('✓ ' . count($pengaturanData) . ' pengaturan dibuat');

        // ================================================================
        //  SELESAI
        // ================================================================
        $this->command?->info('');
        $this->command?->info('✅ DemoDataSeeder selesai!');
        $this->command?->info('   • ' . count($kelasList) . ' kelas');
        $this->command?->info('   • ' . count($guruModels) . ' guru');
        $this->command?->info('   • ' . count($siswaModels) . ' siswa');
        $this->command?->info('   • ' . count($staffModels) . ' staff TU');
        $this->command?->info('   • ' . $ortuCount . ' orang tua');
        $this->command?->info('   • ' . count($mapelModels) . ' mapel');
        $this->command?->info('   • ' . $jadwalCount . ' jadwal pelajaran');
        $this->command?->info('   • ' . $absensiCount . ' absensi siswa');
        $this->command?->info('   • ' . $absensiGuruCount . ' absensi guru');
        $this->command?->info('   • ' . count($izinData) . ' izin/sakit');
        $this->command?->info('   • ' . count($pengaturanData) . ' pengaturan');
        $this->command?->info('');
        $this->command?->info('   🔑 Semua password: password');
    }
}
