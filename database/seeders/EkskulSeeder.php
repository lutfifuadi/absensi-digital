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
        // 1. Ambil atau buat Tahun Akademik tanpa melanggar idx_tahun_akademik_hanya_satu_aktif
        $tahunAkademik = TahunAkademik::where('is_aktif', true)->first() 
            ?? TahunAkademik::first();

        if (!$tahunAkademik) {
            $tahunAkademik = TahunAkademik::create([
                'nama' => '2025/2026',
                'semester' => 'genap',
                'tanggal_mulai' => '2026-01-05',
                'tanggal_selesai' => '2026-06-20',
                'is_aktif' => true,
            ]);
        }

        // Kelas penunjang
        $kelas = Kelas::first() ?? Kelas::create([
            'nama' => 'XII RPL 1',
            'tingkat' => 'XII',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $tahunAkademik->id,
        ]);

        // Guru (pembina) — ambil guru existing atau buat baru
        $gurus = Guru::take(5)->get();
        if ($gurus->count() < 3) {
            $pembinaData = [
                ['nip' => '198501012010011001', 'nama' => 'Budi Santoso, S.Pd.', 'mapel' => 'Olahraga'],
                ['nip' => '198502022010012002', 'nama' => 'Siti Aminah, S.Pd.', 'mapel' => 'Seni Budaya'],
                ['nip' => '198503032010013003', 'nama' => 'Agus Wijaya, S.Pd.', 'mapel' => 'Komputer'],
                ['nip' => '198504042010014004', 'nama' => 'Dewi Lestari, S.Pd.', 'mapel' => 'Biologi'],
            ];

            foreach ($pembinaData as $data) {
                $user = User::firstOrCreate(
                    ['email' => strtolower(str_replace([' ', ',', '.'], '', $data['nama'])) . '@sekolah.sch.id'],
                    [
                        'name' => $data['nama'],
                        'username' => 'guru' . rand(100, 999),
                        'password' => Hash::make('password'),
                        'role' => User::ROLE_GURU,
                    ]
                );

                Guru::firstOrCreate(
                    ['nip' => $data['nip']],
                    [
                        'user_id' => $user->id,
                        'nama_lengkap' => $data['nama'],
                        'jenis_kelamin' => (str_contains($data['nama'], 'Siti') || str_contains($data['nama'], 'Dewi')) ? 'P' : 'L',
                        'mata_pelajaran' => $data['mapel'],
                        'status' => 'aktif',
                    ]
                );
            }
            $gurus = Guru::all();
        }

        // Siswa — ambil siswa existing atau buat baru
        $siswas = Siswa::take(15)->get();
        if ($siswas->count() < 5) {
            $siswaData = [
                ['nis' => '2024001', 'nama' => 'Ahmad Fauzi', 'jk' => 'L'],
                ['nis' => '2024002', 'nama' => 'Rina Marlina', 'jk' => 'P'],
                ['nis' => '2024003', 'nama' => 'Doni Pratama', 'jk' => 'L'],
                ['nis' => '2024004', 'nama' => 'Sari Indah', 'jk' => 'P'],
                ['nis' => '2024005', 'nama' => 'Bayu Setiawan', 'jk' => 'L'],
            ];

            foreach ($siswaData as $data) {
                $user = User::firstOrCreate(
                    ['email' => $data['nis'] . '@siswa.sekolah.sch.id'],
                    [
                        'name' => $data['nama'],
                        'username' => $data['nis'],
                        'password' => Hash::make('password'),
                        'role' => User::ROLE_SISWA,
                    ]
                );

                Siswa::firstOrCreate(
                    ['nis' => $data['nis']],
                    [
                        'user_id' => $user->id,
                        'nisn' => '00' . rand(10000000, 99999999),
                        'nama_lengkap' => $data['nama'],
                        'jenis_kelamin' => $data['jk'],
                        'tempat_lahir' => 'Jakarta',
                        'tanggal_lahir' => '2008-01-01',
                        'kelas_id' => $kelas->id,
                        'tahun_akademik_id' => $tahunAkademik->id,
                        'status' => 'aktif',
                    ]
                );
            }
            $siswas = Siswa::all();
        }

        // 2. Buat / Update Master Ekskul Realistis
        $ekskulsData = [
            [
                'nama' => 'Pramuka',
                'kategori' => 'wajib',
                'deskripsi' => 'Kegiatan kepramukaan untuk menggembleng kedisiplinan, kepemimpinan, kepedulian sosial, dan cinta tanah air.',
                'kuota' => 60,
                'status' => true,
                'icon' => 'pramuka',
            ],
            [
                'nama' => 'Paskibra',
                'kategori' => 'wajib',
                'deskripsi' => 'Pasukan Pengibar Bendera Sekolah yang melatih fisik, kedisiplinan, PBB, serta mental kebangsaan.',
                'kuota' => 40,
                'status' => true,
                'icon' => 'paskibra',
            ],
            [
                'nama' => 'PMR (Palang Merah Remaja)',
                'kategori' => 'pilihan',
                'deskripsi' => 'Pembinaan kesehatan sekolah, pertolongan pertama (P3K), donor darah, dan aksi kesiapsiagaan bencana.',
                'kuota' => 35,
                'status' => true,
                'icon' => 'pmr',
            ],
            [
                'nama' => 'Futsal & Sepak Bola',
                'kategori' => 'olahraga',
                'deskripsi' => 'Pengembangan minat bakat olahraga futsal, taktik permainan tim, dan sportivitas kompetisi antar sekolah.',
                'kuota' => 30,
                'status' => true,
                'icon' => 'futsal',
            ],
            [
                'nama' => 'Bola Basket',
                'kategori' => 'olahraga',
                'deskripsi' => 'Latihan ketangkasan bola basket, stamina, kerjasama tim, dan keikutsertaan turnamen kejuaraan daerah.',
                'kuota' => 25,
                'status' => true,
                'icon' => 'basket',
            ],
            [
                'nama' => 'Seni Musik & Band',
                'kategori' => 'seni',
                'deskripsi' => 'Pengembangan kreasi bermusik, aransemen lagu, vokal grup, serta persiapan pentas seni sekolah.',
                'kuota' => 25,
                'status' => true,
                'icon' => 'musik',
            ],
            [
                'nama' => 'KIR (Kelompok Ilmiah Remaja) & Robotika',
                'kategori' => 'akademik',
                'deskripsi' => 'Penelitian ilmiah remaja, eksperimen sains populer, dan pemrograman dasar mikrokontroler robotika.',
                'kuota' => 30,
                'status' => true,
                'icon' => 'robotik',
            ],
            [
                'nama' => 'English Club & Debating',
                'kategori' => 'akademik',
                'deskripsi' => 'Peningkatan kemampuan komunikasi bahasa Inggris, public speaking, dan teknik debat bahasa asing.',
                'kuota' => 30,
                'status' => true,
                'icon' => 'english',
            ]
        ];

        $ekskulModels = [];
        foreach ($ekskulsData as $d) {
            $existing = DB::table('ekskul')->where('nama', $d['nama'])->first();
            if ($existing) {
                DB::table('ekskul')->where('id', $existing->id)->update([
                    'kategori' => $d['kategori'],
                    'deskripsi' => $d['deskripsi'],
                    'kuota' => $d['kuota'],
                    'status' => $d['status'],
                    'icon' => $d['icon'],
                    'updated_at' => now(),
                ]);
                $ekskulModels[] = $existing->id;
            } else {
                $id = DB::table('ekskul')->insertGetId([
                    'nama' => $d['nama'],
                    'kategori' => $d['kategori'],
                    'deskripsi' => $d['deskripsi'],
                    'kuota' => $d['kuota'],
                    'status' => $d['status'],
                    'icon' => $d['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $ekskulModels[] = $id;
            }
        }

        // 3. Buat / Sync Jadwal Ekskul
        DB::table('ekskul_jadwal')->delete();
        $jadwalList = [
            ['ekskul_id' => $ekskulModels[0], 'hari' => 'rabu', 'jam_mulai' => '14:30', 'jam_selesai' => '16:30', 'lokasi' => 'Lapangan Lapangan Utama'],
            ['ekskul_id' => $ekskulModels[0], 'hari' => 'sabtu', 'jam_mulai' => '08:00', 'jam_selesai' => '11:00', 'lokasi' => 'Lapangan Pramuka'],
            ['ekskul_id' => $ekskulModels[1], 'hari' => 'selasa', 'jam_mulai' => '15:00', 'jam_selesai' => '17:00', 'lokasi' => 'Lapangan Upacara'],
            ['ekskul_id' => $ekskulModels[1], 'hari' => 'kamis', 'jam_mulai' => '15:00', 'jam_selesai' => '17:00', 'lokasi' => 'Lapangan Upacara'],
            ['ekskul_id' => $ekskulModels[2], 'hari' => 'senin', 'jam_mulai' => '14:30', 'jam_selesai' => '16:00', 'lokasi' => 'Ruang UKS Utama'],
            ['ekskul_id' => $ekskulModels[3], 'hari' => 'selasa', 'jam_mulai' => '15:30', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Futsal OutDoor'],
            ['ekskul_id' => $ekskulModels[3], 'hari' => 'jumat', 'jam_mulai' => '15:30', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Futsal OutDoor'],
            ['ekskul_id' => $ekskulModels[4], 'hari' => 'rabu', 'jam_mulai' => '15:30', 'jam_selesai' => '17:30', 'lokasi' => 'Lapangan Basket InDoor'],
            ['ekskul_id' => $ekskulModels[5], 'hari' => 'kamis', 'jam_mulai' => '14:30', 'jam_selesai' => '16:30', 'lokasi' => 'Studio Seni & Musik'],
            ['ekskul_id' => $ekskulModels[6], 'hari' => 'jumat', 'jam_mulai' => '14:00', 'jam_selesai' => '16:00', 'lokasi' => 'Laboratorium Komputer & Robotika'],
            ['ekskul_id' => $ekskulModels[7], 'hari' => 'rabu', 'jam_mulai' => '14:30', 'jam_selesai' => '16:00', 'lokasi' => 'Ruang Multimedia Language Lab'],
        ];

        foreach ($jadwalList as $j) {
            DB::table('ekskul_jadwal')->insert([
                'ekskul_id' => $j['ekskul_id'],
                'hari' => $j['hari'],
                'jam_mulai' => $j['jam_mulai'],
                'jam_selesai' => $j['jam_selesai'],
                'lokasi' => $j['lokasi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Pembina Ekskul
        DB::table('ekskul_pembina')->delete();
        $guruFirst = $gurus->first()?->id ?? 1;
        $guruSecond = $gurus->skip(1)->first()?->id ?? $guruFirst;

        foreach ($ekskulModels as $idx => $eId) {
            DB::table('ekskul_pembina')->insert([
                'ekskul_id' => $eId,
                'guru_id' => ($idx % 2 === 0) ? $guruFirst : $guruSecond,
                'jabatan' => 'Pembina Utama',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 5. Anggota Ekskul (Siswa)
        DB::table('ekskul_anggota')->delete();
        foreach ($ekskulModels as $eId) {
            foreach ($siswas->take(4) as $s) {
                DB::table('ekskul_anggota')->insert([
                    'ekskul_id' => $eId,
                    'siswa_id' => $s->id,
                    'status' => 'aktif',
                    'tanggal_masuk' => now()->subMonths(3)->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. Sampel Kegiatan Ekskul
        DB::table('ekskul_kegiatan')->delete();
        $kegiatans = [
            ['ekskul_id' => $ekskulModels[0], 'nama_kegiatan' => 'Perkemahan Sabtu Minggu (PERSAMI)', 'tanggal' => now()->addDays(14)->toDateString(), 'deskripsi' => 'Kegiatan perkemahan pelantikan anggota baru Pramuka.'],
            ['ekskul_id' => $ekskulModels[1], 'nama_kegiatan' => 'Gladi Bersih Upacara Bendera', 'tanggal' => now()->addDays(7)->toDateString(), 'deskripsi' => 'Latihan ketangkasan baris-berbaris pasukan Paskibra.'],
            ['ekskul_id' => $ekskulModels[2], 'nama_kegiatan' => 'Simulasi Pertolongan Pertama (P3K)', 'tanggal' => now()->addDays(10)->toDateString(), 'deskripsi' => 'Pelatihan teknik pembidaian dan penanganan korban darurat.'],
            ['ekskul_id' => $ekskulModels[3], 'nama_kegiatan' => 'Sparring Futsal Antar Sekolah', 'tanggal' => now()->addDays(5)->toDateString(), 'deskripsi' => 'Uji tanding persahabatan futsal tim sekolah.'],
            ['ekskul_id' => $ekskulModels[6], 'nama_kegiatan' => 'Workshop Coding & Robotika Dasar', 'tanggal' => now()->addDays(20)->toDateString(), 'deskripsi' => 'Pembuatan sensor jarak otomatis menggunakan Arduino.'],
        ];

        foreach ($kegiatans as $k) {
            DB::table('ekskul_kegiatan')->insert([
                'ekskul_id' => $k['ekskul_id'],
                'nama_kegiatan' => $k['nama_kegiatan'],
                'tanggal' => $k['tanggal'],
                'deskripsi' => $k['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
