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
    private function logInstaller($message, $level = 'info')
    {
        $logPath = storage_path('logs/installer.log');
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        file_put_contents($logPath, $logMessage, FILE_APPEND);
        
        // Also log to laravel.log
        if ($level === 'error') {
            \Illuminate\Support\Facades\Log::error("Installer: " . $message);
        } else {
            \Illuminate\Support\Facades\Log::info("Installer: " . $message);
        }
    }

    public function checkUpdate()
    {
        $currentVersion = env('APP_VERSION', '1.0.0');
        $licenseKey = env('LICENSE_KEY');
        $domain = env('REGISTERED_DOMAIN');

        try {
            $response = Http::timeout(10)->post('https://saas-presensi.lutfifuadi.my.id/api/version/check', [
                'current_version' => $currentVersion,
                'license_key' => $licenseKey,
                'domain' => $domain,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal terhubung ke server update.']);
    }

    public function step1()
    {
        $this->logInstaller('Memulai instalasi Step 1: Pengecekan Requirement');
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

        $this->logInstaller('Memproses Step 2: Verifikasi Lisensi');

        // --- DEVELOPMENT BYPASS ---
        if ($license === 'DEV-MASTER-KEY') {
            $this->logInstaller('Menggunakan Master Key (Development Bypass)');
            $devSchoolName = 'Development School';
            $this->setEnv([
                'LICENSE_KEY'       => $license,
                'REGISTERED_DOMAIN' => $domain,
                'SCHOOL_NAME'       => $devSchoolName,
            ]);
            session([
                'install_license_key'       => $license,
                'install_registered_domain' => $domain,
                'install_school_name'       => $devSchoolName,
            ]);
            return redirect()->route('installer.step2')->with('license_verified', true);
        }

        try {
            $this->logInstaller("Menghubungi server lisensi untuk domain: {$domain}");
            $response = Http::asForm()->timeout(30)->post($licenseApiUrl, [
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

        $this->logInstaller('Lisensi berhasil diverifikasi. Menyimpan ke .env dan session.');

        $schoolName = isset($result['school_name']) ? trim($result['school_name']) : '';
        $this->logInstaller('Nama sekolah terdaftar: ' . ($schoolName ?: '(tidak tersedia)'));

        $this->setEnv([
            'LICENSE_KEY'       => $license,
            'REGISTERED_DOMAIN' => $domain,
            'SCHOOL_NAME'       => $schoolName,
        ]);

        session([
            'install_license_key'       => $license,
            'install_registered_domain' => $domain,
            'install_school_name'       => $schoolName,
        ]);

        return redirect()->route('installer.step2')->with('license_verified', true);
    }

    public function step3()
    {
        return view('installer.step3');
    }

    public function step3Submit(Request $request)
    {
        $this->logInstaller('Memproses Step 3: Konfigurasi Database');
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
            $this->logInstaller("Koneksi database berhasil ke: {$request->db_name}");

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
            
            session([
                'install_db_connection' => $request->db_connection,
                'install_db_host'       => $request->db_host,
                'install_db_port'       => $request->db_port,
                'install_db_name'       => $request->db_name,
                'install_db_user'       => $request->db_user,
                'install_db_pass'       => $request->db_pass,
            ]);

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
        $this->logInstaller('Memproses Step 4: Konfigurasi Sekolah');
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

    public function saveProgress(Request $request)
    {
        $data = $request->except(['_token']);
        foreach ($data as $key => $value) {
            session(['install_' . $key => $value]);
        }
        return response()->json(['success' => true]);
    }

    public function publishAssets()
    {
        try {
            try {
                Artisan::call('livewire:publish', ['--assets' => true, '--force' => true]);
            } catch (\Throwable $e) {
                Artisan::call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
            }
            return response()->json(['success' => true, 'message' => 'Livewire assets berhasil dipublish.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function process(Request $request)
    {
        $this->logInstaller('Memulai Proses Akhir Instalasi (Migrasi & Seeding)');
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
            // 0. Reconfigure DB connection at runtime using session values
            $dbConnection = session('install_db_connection', config('database.default'));
            $dbHost       = session('install_db_host', '127.0.0.1');
            $dbPort       = session('install_db_port', '3306');
            $dbName       = session('install_db_name', '');
            $dbUser       = session('install_db_user', '');
            $dbPass       = session('install_db_pass', '');

            // Fallback: Use 'mysql' driver for 'mariadb' if needed, or vice-versa
            // Some environments are more stable with 'mysql' driver name
            $driver = $dbConnection === 'mariadb' ? 'mariadb' : ($dbConnection === 'sqlite' ? 'sqlite' : 'mysql');

            config([
                'database.default' => $dbConnection,
                "database.connections.{$dbConnection}.driver"   => $driver,
                "database.connections.{$dbConnection}.host"     => $dbHost,
                "database.connections.{$dbConnection}.port"     => $dbPort,
                "database.connections.{$dbConnection}.database" => $dbName,
                "database.connections.{$dbConnection}.username" => $dbUser,
                "database.connections.{$dbConnection}.password" => $dbPass,
            ]);

            // Clear configuration cache to ensure fresh settings are used
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            \Illuminate\Support\Facades\DB::purge($dbConnection);
            \Illuminate\Support\Facades\DB::reconnect($dbConnection);

            // Log for debugging
            $this->logInstaller('Memulai migrasi database...');
            \Illuminate\Support\Facades\Log::info('Installer: Starting migration', ['connection' => $dbConnection, 'db' => $dbName]);

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
                'license_key'           => env('LICENSE_KEY'),
                'github_repo_owner'     => env('GITHUB_REPO_OWNER'),
                'github_repo_name'      => env('GITHUB_REPO_NAME'),
                'github_token'          => env('GITHUB_TOKEN'),
                'app_version'           => env('APP_VERSION', '1.0.0'),
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

            // 5. Publish Livewire assets (opsional, tidak batalkan instalasi jika gagal)
            try {
                Artisan::call('livewire:publish', ['--assets' => true, '--force' => true]);
            } catch (\Throwable $e) {
                // Coba fallback via vendor:publish
                try {
                    Artisan::call('vendor:publish', ['--tag' => 'livewire:assets', '--force' => true]);
                } catch (\Throwable $e2) {
                    Log::warning('Livewire assets publish gagal (tidak kritis): ' . $e2->getMessage());
                }
            }

            $this->logInstaller('Instalasi Selesai dengan Sukses.');
            // Create storage/installed
            file_put_contents(storage_path('installed'), 'installed on ' . date('Y-m-d H:i:s'));

            // Final check for Vite assets (Warn but don't fail)
            $manifestPath = public_path('build/manifest.json');
            $assetWarning = !file_exists($manifestPath) ? ' Peringatan: Asset Vite belum di-build (npm run build).' : '';

            // Clear session data
            $sessionKeys = [
                'install_license_key', 'install_registered_domain',
                'install_db_connection', 'install_db_host', 'install_db_port', 'install_db_name', 'install_db_user', 'install_db_pass',
                'install_school_name', 'install_school_slogan', 'install_school_address', 'install_school_phone', 'install_school_email', 'install_enable_website',
                'install_admin_name', 'install_admin_email', 'install_admin_username'
            ];
            session()->forget($sessionKeys);

            return response()->json([
                'success' => true,
                'message' => 'Instalasi Berhasil!' . $assetWarning,
                'redirect' => url('/')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Installer Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses instalasi: ' . $e->getMessage() . '. Cek laravel.log untuk detail.'
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
            // Quote value if it contains spaces or special shell characters
            $envValue = (preg_match('/[\s"\'#]/', (string) $value))
                ? '"' . addcslashes((string) $value, '"\\') . '"'
                : (string) $value;

            // Match both active and commented-out lines (e.g. "DB_HOST=..." or "# DB_HOST=...")
            $pattern = "/^#?\s*{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                // Replace (including commented-out lines) with the active key=value
                $env = preg_replace_callback(
                    $pattern,
                    fn() => "{$key}={$envValue}",
                    $env
                );
            } else {
                $env .= "\n{$key}={$envValue}";
            }
        }

        try {
            if (!file_put_contents($path, $env)) {
                throw new \Exception('Gagal menulis ke file .env. Pastikan file memiliki izin tulis (writable).');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Installer setEnv Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
