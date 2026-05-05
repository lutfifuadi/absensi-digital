<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\AbsensiSiswa;

class InstallerController extends Controller
{
    public function step1()
    {
        // 0. Inisialisasi Environment (Buat .env & APP_KEY jika belum ada)
        $this->setEnv([]);

        $requirements = [
            'PHP Version >= 8.1' => version_compare(phpversion(), '8.1.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'JSON Extension' => extension_loaded('json'),
            'Ctype Extension' => extension_loaded('ctype'),
            'XML Extension' => extension_loaded('xml'),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Curl Extension' => extension_loaded('curl'),
            'Storage is Writable' => is_writable(storage_path()),
            'Cache is Writable' => is_writable(base_path('bootstrap/cache')),
        ];

        $allPassed = !in_array(false, $requirements);

        return view('installer.step1', compact('requirements', 'allPassed'));
    }

    public function step2()
    {
        return view('installer.step2');
    }

    public function step2Submit(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'registered_domain' => 'required|string',
        ]);

        // 1. Validasi Lisensi via API Pusat
        $licenseApiUrl = 'https://saas-presensi.lutfifuadi.my.id/api/license/verify';
        $domain = trim($request->registered_domain);
        $license = trim($request->license_key);

        // --- DEVELOPMENT BYPASS ---
        if ($license === 'DEV-MASTER-KEY') {
            $this->setEnv([
                'LICENSE_KEY' => $license,
                'REGISTERED_DOMAIN' => $domain
            ]);
            return redirect()->route('installer.step3');
        }

        try {
            $response = Http::asForm()->withoutVerifying()->timeout(30)->post($licenseApiUrl, [
                'license_key' => $license,
                'domain' => $domain,
            ]);

            $result = $response->json();

            if (!$response->successful() || empty($result['success'])) {
                if ($response->status() === 404) {
                    \Illuminate\Support\Facades\Log::error('License API 404 Error', [
                        'url' => $licenseApiUrl,
                        'domain' => $domain,
                        'response' => $response->body()
                    ]);
                }
                
                $status = $response->successful() ? 'Validasi Gagal' : 'Server Error (' . $response->status() . ') (v2)';
                $errorMsg = 'Lisensi Tidak Valid untuk Domain: ' . $domain;
                
                if (isset($result['message'])) {
                    $errorMsg .= ' | Pesan: ' . $result['message'];
                }
                
                return back()->withInput()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal Verifikasi Lisensi: ' . $e->getMessage());
        }

        $this->setEnv([
            'LICENSE_KEY' => $license,
            'REGISTERED_DOMAIN' => $domain,
        ]);

        return redirect()->route('installer.step3');
    }

    public function step3()
    {
        return view('installer.step3');
    }

    public function step3Submit(Request $request)
    {
        $request->validate([
            'db_connection' => 'required|in:mysql,mariadb,sqlite',
            'db_host'       => 'required_if:db_connection,mysql,mariadb',
            'db_port'       => 'required_if:db_connection,mysql,mariadb',
            'db_name'       => 'required',
            'db_user'       => 'required_if:db_connection,mysql,mariadb',
        ]);

        // 1. Test Database Connection
        try {
            if ($request->db_connection === 'sqlite') {
                $dbPath = $request->db_name;
                if (!str_ends_with($dbPath, '.sqlite')) {
                    $dbPath = database_path($request->db_name . '.sqlite');
                }
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                }
                $pdo = new \PDO("sqlite:{$dbPath}");
            } else {
                $pdo = new \PDO(
                    "mysql:host={$request->db_host};port={$request->db_port};dbname={$request->db_name}",
                    $request->db_user,
                    $request->db_pass
                );
            }
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // 2. DETEKSI TABEL EKSISTING (Untuk fitur Fresh Install)
            if ($request->db_connection === 'sqlite') {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            } else {
                $stmt = $pdo->query("SHOW TABLES");
            }
            $tables = $stmt->fetchAll();

            if (count($tables) > 0 && !$request->has('confirm_wipe')) {
                return back()->withInput()->with('db_warning', 'Database tidak kosong! Terdeteksi ' . count($tables) . ' tabel. Jika dilanjutkan, data lama akan dihapus.');
            }

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Koneksi Database Gagal: ' . $e->getMessage());
        }

        try {
            // Update .env with DB and standalone mode
            $envData = [
                'DB_CONNECTION' => $request->db_connection,
                'DB_DATABASE'   => $request->db_name,
                'APP_MULTIPURPOSE_MODE' => 'standalone',
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'file',
            ];

            if ($request->db_connection !== 'sqlite') {
                $envData['DB_HOST'] = $request->db_host;
                $envData['DB_PORT'] = $request->db_port;
                $envData['DB_USERNAME'] = $request->db_user;
                $envData['DB_PASSWORD'] = $request->db_pass ?? '';
            }

            $this->setEnv($envData);
            Artisan::call('config:clear');

            return redirect()->route('installer.step4');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan konfigurasi: ' . $e->getMessage());
        }
    }

    public function step4()
    {
        return view('installer.step4');
    }

    public function step4Submit(Request $request)
    {
        $request->validate([
            'school_name'    => 'required',
            'school_slogan'  => 'nullable|string|max:255',
            'school_address' => 'required|string',
            'school_phone'   => 'required|string',
            'school_email'   => 'required|email',
            'enable_website' => 'required|string',
        ]);

        // Simpan ke session untuk digunakan di step 5
        session([
            'install_school_name'    => $request->school_name,
            'install_school_slogan'  => $request->school_slogan,
            'install_school_address' => $request->school_address,
            'install_school_phone'   => $request->school_phone,
            'install_school_email'   => $request->school_email,
            'install_enable_website' => $request->enable_website,
        ]);

        return redirect()->route('installer.step5');
    }

    public function step5()
    {
        return view('installer.step5');
    }

    public function process(Request $request)
    {
        $request->validate([
            'admin_name'    => 'required',
            'admin_email'   => 'required|email',
            'admin_username' => 'required|min:4',
            'admin_password' => 'required|min:6',
        ]);

        // Cegah timeout untuk proses migrasi berat
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        try {
            // 1. Jalankan migrasi
            Artisan::call('migrate:fresh', ['--force' => true]);
            
            // 2. Seed data pengaturan default
            $pengaturanDefaults = [
                'master_db_sync_enabled' => 'Ya',
                'zona_waktu'             => 'Asia/Jakarta',
                'nama_lembaga'           => session('install_school_name'),
                'nama_sekolah'           => session('install_school_name'),
                'slogan_lembaga'         => session('install_school_slogan') ?? 'Sistem Absensi Digital Modern',
                'alamat_lembaga'         => session('install_school_address'),
                'telepon_lembaga'        => session('install_school_phone'),
                'email_lembaga'         => session('install_school_email'),
                'tampilkan_beranda'     => session('install_enable_website'),
            ];
            foreach ($pengaturanDefaults as $key => $value) {
                \App\Models\Pengaturan::create([
                    'key' => $key, 
                    'value' => $value,
                ]);
            }

            // 3. Buat User Admin
            User::create([
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'username' => $request->admin_username,
                'password' => Hash::make($request->admin_password),
                'role' => 'super_admin',
                'roles' => ['super_admin'],
            ]);

            // 4. Seed Dummy Data (Optional)
            if ($request->has('include_dummy_data')) {
                $this->seedDummyData();
            }

            // 5. Publish Livewire assets
            Artisan::call('livewire:publish', ['--assets' => true]);

            // Create storage/installed
            file_put_contents(storage_path('installed'), 'installed on ' . date('Y-m-d H:i:s'));

            // Clear session data
            session()->forget(['install_school_name', 'install_school_slogan', 'install_school_address', 'install_school_phone', 'install_school_email', 'install_enable_website']);

            return response()->json([
                'success' => true,
                'message' => 'Instalasi Berhasil!',
                'redirect' => url('/')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses instalasi: ' . $e->getMessage()
            ], 500);
        }
    }

    private function seedDummyData()
    {
        // 1. Tahun Akademik
        $ta = TahunAkademik::create([
            'nama' => '2024/2025',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2024-07-15',
            'tanggal_selesai' => '2024-12-20',
            'is_aktif' => true,
        ]);

        // 2. Guru (Wali Kelas)
        $userGuru = User::create([
            'name' => 'Budi Santoso, S.Pd',
            'email' => 'budi@sekolah.com',
            'username' => 'guru_budi',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'roles' => ['guru', 'wali_kelas'],
        ]);

        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '198501012010011001',
            'nama_lengkap' => 'Budi Santoso, S.Pd',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru Tetap',
            'status' => 'aktif',
        ]);

        // 3. Kelas
        $kelasX = Kelas::create([
            'nama' => 'X-MIPA-1',
            'tingkat' => 'X',
            'jurusan' => 'MIPA',
            'wali_kelas_id' => $guru->id,
            'tahun_akademik_id' => $ta->id,
            'is_aktif_absensi' => true,
        ]);

        $kelasXI = Kelas::create([
            'nama' => 'XI-MIPA-2',
            'tingkat' => 'XI',
            'jurusan' => 'MIPA',
            'tahun_akademik_id' => $ta->id,
            'is_aktif_absensi' => true,
        ]);

        // 4. Siswa
        $siswaData = [
            ['nama' => 'Ahmad Fauzi', 'nis' => '10001', 'jk' => 'L', 'kelas' => $kelasX->id],
            ['nama' => 'Siti Aminah', 'nis' => '10002', 'jk' => 'P', 'kelas' => $kelasX->id],
            ['nama' => 'Bambang Heru', 'nis' => '11001', 'jk' => 'L', 'kelas' => $kelasXI->id],
            ['nama' => 'Dewi Lestari', 'nis' => '11002', 'jk' => 'P', 'kelas' => $kelasXI->id],
        ];

        foreach ($siswaData as $data) {
            $userSiswa = User::create([
                'name' => $data['nama'],
                'email' => strtolower(str_replace(' ', '', $data['nama'])) . '@siswa.com',
                'username' => $data['nis'],
                'password' => Hash::make($data['nis']),
                'role' => 'siswa',
                'roles' => ['siswa'],
            ]);

            Siswa::create([
                'user_id' => $userSiswa->id,
                'nis' => $data['nis'],
                'nisn' => '00' . $data['nis'] . rand(100, 999),
                'nama_lengkap' => $data['nama'],
                'jenis_kelamin' => $data['jk'],
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2008-01-01',
                'kelas_id' => $data['kelas'],
                'tahun_akademik_id' => $ta->id,
                'status' => 'aktif',
            ]);
        }

        // 5. Jadwal Pelajaran (Contoh)
        $hariSchedules = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        foreach ($hariSchedules as $hari) {
            JadwalPelajaran::create([
                'kelas_id' => $kelasX->id,
                'guru_id' => $guru->id,
                'mata_pelajaran' => 'Matematika',
                'hari' => $hari,
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:00:00',
            ]);
        }

        // 6. Data Presensi (Sampel 3 hari terakhir)
        $siswas = Siswa::all();
        for ($i = 0; $i < 3; $i++) {
            $date = now()->subDays($i);
            // Skip weekend
            if ($date->isWeekend()) continue;

            foreach ($siswas as $siswa) {
                // Random status: hadir (80%), sakit (10%), izin (10%)
                $rand = rand(1, 100);
                $status = 'hadir';
                $jamMasuk = '07:15:00';
                $jamPulang = '14:00:00';

                if ($rand > 80 && $rand <= 90) {
                    $status = 'sakit';
                    $jamMasuk = null;
                    $jamPulang = null;
                } elseif ($rand > 90) {
                    $status = 'izin';
                    $jamMasuk = null;
                    $jamPulang = null;
                }

                AbsensiSiswa::create([
                    'siswa_id' => $siswa->id,
                    'kelas_id' => $siswa->kelas_id,
                    'tanggal' => $date->format('Y-m-d'),
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status' => $status,
                    'metode' => 'QR Code',
                ]);
            }
        }
    }

    private function setEnv($data = [])
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            copy(base_path('.env.example'), $path);
        }

        $env = file_get_contents($path);

        if (!isset($data['APP_KEY']) || empty($data['APP_KEY'])) {
            if (preg_match('/^APP_KEY=(.*)$/m', $env, $matches) && !empty($matches[1])) {
                $data['APP_KEY'] = trim($matches[1]);
            } else {
                $data['APP_KEY'] = 'base64:' . base64_encode(random_bytes(32));
            }
        }

        foreach ($data as $key => $value) {
            if (strpos($env, $key . '=') !== false) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        }

        file_put_contents($path, $env);
    }
}
