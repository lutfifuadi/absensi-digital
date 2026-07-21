<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportWaliKelasSuratTugas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wali-kelas:import-from-surat-tugas
                            {--dry-run : Only show what will be done, don\'t actually insert}
                            {--assign-class : Also assign wali kelas to their respective classes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import 36 wali kelas dari Surat Tugas Semester Ganjil 2026/2027';

    /**
     * Data wali kelas yang akan di-import.
     * Sumber: Surat Tugas Wali Kelas Semester Ganjil 2026-2027 MAN 1 Kota Bandung.
     */
    protected array $dataWaliKelas = [
        // Kelas X (12 guru)
        ['nama' => 'Dea Sudawati, S.Pd.',              'nip' => '199206052019032020', 'kelas' => 'X-A',   'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Asep Setia, S.Ag., M.Ag.',          'nip' => '197005112006041010', 'kelas' => 'X-B',   'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Dra. Hj. Elita Yusriwiningsih',     'nip' => '196901011995122004', 'kelas' => 'X-C',   'jk' => 'P', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Dra. Fatimah',                      'nip' => '196712151995122002', 'kelas' => 'X-D',   'jk' => 'P', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Dinie Citra Utami, S.Pd.',          'nip' => '199012242019032020', 'kelas' => 'X-E',   'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Siti Fatimah Maulina, S.Pd.',       'nip' => '199707192025052010', 'kelas' => 'X-F',   'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Muh. Syarif Hidayatullah, S.Pd., M.Pd.', 'nip' => '198307172024211013', 'kelas' => 'X-G', 'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Hj. Yani Yulistiani, S.Ag., M.Pd.I.', 'nip' => '197807242007012019', 'kelas' => 'X-H', 'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Tia Oktaviani, S.Pd.',              'nip' => '199810282024212041', 'kelas' => 'X-I',   'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Iis Rodiyah, S.Ag.',                'nip' => '197610242009012005', 'kelas' => 'X-J',   'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Arnandho Ricco, S.Sn., M.Pd.',      'nip' => '197809152023211007', 'kelas' => 'X-K',   'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Desi Destiani Guntari, S.Pd.',      'nip' => '199006222025052002', 'kelas' => 'X-L',   'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],

        // Kelas XI (12 guru)
        ['nama' => 'Tika Fajar Muflihah, S.Pd.',        'nip' => '198709142011012010', 'kelas' => 'XI.F-1',  'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Berlian Nurcahya, S.Pd., M.P.Fis.', 'nip' => '197912122005012009', 'kelas' => 'XI.F-2',  'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Toha Tarmana, S.Ag., M.Pd.I.',      'nip' => '197202082007101003', 'kelas' => 'XI.F-3',  'jk' => 'L', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Siti Safariah, S.Pd.',              'nip' => '199702172025052006', 'kelas' => 'XI.F-4',  'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Khaeratun Nisa, S.Pd.',             'nip' => '199105042019032018', 'kelas' => 'XI.F-5',  'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Heri Hendriana, M.Pd.',             'nip' => '197609082007101002', 'kelas' => 'XI.F-6',  'jk' => 'L', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Tia Athiyyah, M.Pd.',               'nip' => '199106212024212040', 'kelas' => 'XI.F-7',  'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Riseu Liyana, S.Sn.',               'nip' => '198706052025212012', 'kelas' => 'XI.F-8',  'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Hermawan Arisusanto, S.Hum.',       'nip' => '199610172025051004', 'kelas' => 'XI.F-9',  'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Sri Nurhayati, S.Si.',              'nip' => '199005172025052001', 'kelas' => 'XI.F-10', 'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Jemi Edi Jayadi, S.Pd.',            'nip' => '198111222023211006', 'kelas' => 'XI.F-11', 'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'H. Endang Kurnia Priatna, S.Pd.',   'nip' => '197402152005011003', 'kelas' => 'XI.F-12', 'jk' => 'L', 'jabatan' => 'Guru Ahli Madya'],

        // Kelas XII (12 guru)
        ['nama' => 'Drs. Endang Rochmana',              'nip' => '196801231994031005', 'kelas' => 'XII.F-1', 'jk' => 'L', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Drs. Toto Taufikurohman, M.Pd.',    'nip' => '196806201993031001', 'kelas' => 'XII.F-2', 'jk' => 'L', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Hj. Liza Rosmaniar, S.Pd.',         'nip' => '196806211991012001', 'kelas' => 'XII.F-3', 'jk' => 'P', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Hj. Cuncun Hasanah, S.Pd.',         'nip' => '197711202009012002', 'kelas' => 'XII.F-4', 'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Dra. Hj. Lilis Widianti, M.P.Mat.', 'nip' => '196905211995032001', 'kelas' => 'XII.F-5', 'jk' => 'P', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'H. Abdul Azis Muslim, M.Pd.',       'nip' => '197207191998031006', 'kelas' => 'XII.F-6', 'jk' => 'L', 'jabatan' => 'Guru Ahli Madya'],
        ['nama' => 'Sri Sultini, S.Pd.',                'nip' => '197611252005012003', 'kelas' => 'XII.F-7', 'jk' => 'P', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Sheni Asrianti, S.Pd.',             'nip' => '199502062019032016', 'kelas' => 'XII.F-8', 'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Furqon Saeful Aziz, S.Ag.',         'nip' => '197309102007101005', 'kelas' => 'XII.F-9', 'jk' => 'L', 'jabatan' => 'Guru Ahli Muda'],
        ['nama' => 'Yanti Maryanti, S.Pd.',             'nip' => '199901192024212035', 'kelas' => 'XII.F-10', 'jk' => 'P', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Halim Irfani, S.Pd.I., M.Pd.',      'nip' => '197609152011011007', 'kelas' => 'XII.F-11', 'jk' => 'L', 'jabatan' => 'Guru Ahli Pertama'],
        ['nama' => 'Euis Ratna Dewi, S.Pd.',            'nip' => '196806131999032002', 'kelas' => 'XII.F-12', 'jk' => 'P', 'jabatan' => 'Guru Ahli Madya'],
    ];

    /**
     * Prefix gelar yang akan dihapus dari nama untuk membuat username.
     */
    protected array $gelarDepan = [
        'Drs.', 'Dra.', 'Hj.', 'H.', 'Ir.', 'R.', 'Rr.',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $assignClass = $this->option('assign-class');

        $total = count($this->dataWaliKelas);

        $this->line('╔══════════════════════════════════════════════════════════╗');
        $this->line('║   IMPORT WALI KELAS DARI SURAT TUGAS SEMESTER GANJIL   ║');
        $this->line('║               2026/2027 MAN 1 KOTA BANDUNG              ║');
        $this->line('╚══════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($dryRun) {
            $this->warn('🔍 DRY-RUN MODE: Tidak ada data yang akan ditulis ke database.');
            $this->newLine();
        }

        $this->info("Total data wali kelas: {$total}");
        if ($assignClass) {
            $this->info("Opsi --assign-class AKTIF: Wali kelas akan ditetapkan ke kelas masing-masing.");
        }
        $this->newLine();

        // Progress bar
        $bar = $this->output->createProgressBar($total);
        $bar->setFormat('verbose');
        $bar->start();

        $successCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($this->dataWaliKelas as $index => $item) {
                $namaLengkap = $item['nama'];
                $nip = $item['nip'];
                $namaKelas = $item['kelas'];
                $jk = $item['jk'];
                $jabatan = $item['jabatan'];

                try {
                    // -- Step 1: Cek duplikasi NIP --
                    $existingGuru = Guru::where('nip', $nip)->first();
                    if ($existingGuru) {
                        $bar->advance();
                        $skippedCount++;
                        $this->line('');
                        $this->warn("  ⏭ [{$namaKelas}] {$namaLengkap} — already exists (NIP: {$nip})");
                        continue;
                    }

                    // -- Step 2: Buat username dari nama tanpa gelar --
                    $username = $this->buatUsername($namaLengkap);

                    // Cek duplikasi username
                    $existingUser = User::where('username', $username)->first();
                    if ($existingUser) {
                        $bar->advance();
                        $skippedCount++;
                        $this->line('');
                        $this->warn("  ⏭ [{$namaKelas}] {$namaLengkap} — username '{$username}' sudah dipakai");
                        continue;
                    }

                    $email = $username . '@madrasah.sch.id';

                    if ($dryRun) {
                        $this->line('');
                        $this->line("  📋 [{$namaKelas}] {$namaLengkap}");
                        $this->line("     ├ Username: {$username}");
                        $this->line("     ├ Email: {$email}");
                        $this->line("     ├ NIP: {$nip}");
                        $this->line("     ├ JK: {$jk}");
                        $this->line("     └ Jabatan: {$jabatan}");

                        if ($assignClass) {
                            $kelasData = $this->cariKelas($namaKelas);
                            if ($kelasData) {
                                $this->line("       → Akan ditetapkan sebagai wali kelas: {$namaKelas} (ID: {$kelasData->id}, TA: {$kelasData->tahun_akademik_id})");
                            } else {
                                $this->warn("       ⚠ Kelas '{$namaKelas}' tidak ditemukan di database!");
                            }
                        }

                        $bar->advance();
                        $successCount++;
                        continue;
                    }

                    // -- Step 3: Buat User --
                    $user = User::create([
                        'name' => $namaLengkap,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make('rahasia123'),
                        'role' => User::ROLE_WALI_KELAS,
                        'roles' => [User::ROLE_WALI_KELAS],
                    ]);

                    // -- Step 4: Buat Guru --
                    $guru = Guru::create([
                        'user_id' => $user->id,
                        'nip' => $nip,
                        'nama_lengkap' => $namaLengkap,
                        'jenis_kelamin' => $jk,
                        'jabatan' => $jabatan,
                        'no_hp' => null,
                        'status' => 'aktif',
                    ]);

                    $successCount++;

                    // -- Step 5: Assign ke kelas (jika --assign-class) --
                    if ($assignClass) {
                        $kelas = $this->cariKelas($namaKelas);
                        if ($kelas) {
                            $kelas->update(['wali_kelas_id' => $guru->id]);
                            $this->line('');
                            $this->info("  ✅ [{$namaKelas}] {$namaLengkap} — ditetapkan sebagai wali kelas");
                        } else {
                            $this->line('');
                            $this->warn("  ⚠ [{$namaKelas}] {$namaLengkap} — kelas '{$namaKelas}' tidak ditemukan di database, wali kelas tidak ditetapkan");
                        }
                    }

                    $bar->advance();
                } catch (\Exception $e) {
                    $bar->advance();
                    $errorCount++;
                    $errors[] = "[{$namaKelas}] {$namaLengkap}: {$e->getMessage()}";
                    $this->line('');
                    $this->error("  ❌ [{$namaKelas}] {$namaLengkap} — Error: {$e->getMessage()}");
                }
            }

            if ($dryRun) {
                // Rollback karena dry-run
                DB::rollBack();
            } else {
                DB::commit();
            }

            $bar->finish();
            $this->newLine(2);

            // -- Output Summary --
            $this->line('╔══════════════════════════════════════════════════════════╗');
            $this->line('║                    HASIL IMPORT                         ║');
            $this->line('╚══════════════════════════════════════════════════════════╝');

            $this->table(
                ['Status', 'Jumlah'],
                [
                    ['✅ Sukses', $successCount],
                    ['⏭ Skipped', $skippedCount],
                    ['❌ Error', $errorCount],
                    ['📊 Total', $total],
                ]
            );

            if ($dryRun) {
                $this->warn('Mode dry-run: Tidak ada perubahan yang disimpan ke database.');
            }

            if ($assignClass && !$dryRun) {
                $this->info('Wali kelas telah ditetapkan ke kelas masing-masing.');
            }

            if (count($errors) > 0) {
                $this->newLine();
                $this->warn('Detail Error:');
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->newLine(2);
            $this->error("Fatal error: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Buat username dari nama lengkap dengan menghapus gelar.
     * Contoh: "Dea Sudawati, S.Pd." → "dea.sudawati"
     *          "Dra. Hj. Elita Yusriwiningsih" → "elita.yusriwiningsih"
     *          "Drs. Toto Taufikurohman, M.Pd." → "toto.taufikurohman"
     */
    protected function buatUsername(string $namaLengkap): string
    {
        // Hapus semua gelar belakang (setelah koma)
        $nama = explode(',', $namaLengkap)[0];

        // Hapus gelar depan
        foreach ($this->gelarDepan as $gelar) {
            $nama = preg_replace('/^' . preg_quote($gelar, '/') . '\s*/', '', $nama);
        }

        // Trim dan lower
        $nama = trim($nama);
        $nama = strtolower($nama);

        // Ganti spasi dengan titik
        $username = preg_replace('/\s+/', '.', $nama);

        // Hapus karakter non-alfanumerik kecuali titik
        $username = preg_replace('/[^a-z0-9.]/', '', $username);

        // Hapus titik berulang
        $username = preg_replace('/\.{2,}/', '.', $username);

        // Hapus titik di awal/akhir
        $username = trim($username, '.');

        return $username;
    }

    /**
     * Cari kelas berdasarkan nama kelas.
     * Jika ada duplikat (multiple tahun akademik), pilih yang memiliki tahun_akademik_id terbesar.
     */
    protected function cariKelas(string $namaKelas): ?Kelas
    {
        $kelasList = Kelas::where('nama', $namaKelas)
            ->orderBy('tahun_akademik_id', 'desc')
            ->get();

        if ($kelasList->isEmpty()) {
            return null;
        }

        // Jika ada duplikat, ambil yang tahun_akademik_id paling besar
        return $kelasList->first();
    }
}
