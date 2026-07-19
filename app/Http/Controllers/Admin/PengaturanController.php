<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\GoogleSheetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    private array $defaults = [
        // Tab: Lembaga
        'nama_yayasan_dinas' => 'Kementerian Agama Republik Indonesia',
        'nama_lembaga' => 'Sistem Absensi',
        'nama_sekolah' => 'Sistem Absensi',
        'status_akreditasi' => 'Akreditasi A',
        'jenjang' => 'SMA/MA/SMK',
        'jumlah_tahun_sekolah' => '3',
        'website_lembaga' => 'man1kotabandung.sch.id',
        'nama_kepala_lembaga' => 'YAYAN R JAYA, S.Pd., S.E., M.M',
        'nip_kepala_lembaga' => '197002021993011004',
        'no_telp_lembaga' => '0226027957',
        'kecamatan' => 'Bandung',
        'minimal_hadir_persen' => '90',
        'password_unlock_scan_qr' => '',
        'tampilkan_beranda' => 'Ya',
        // Tambahan field kelembagaan
        'alamat_lembaga' => 'Jl. Jenderal Sudirman No.1, Bandung',
        'kontak_lembaga' => '0226027957',
        'email_lembaga' => 'info@man1kotabandung.sch.id',

        // Tab: Ijin -> Ijin rFID
        'izinkan_rfid' => 'Tidak',
        // Tab: Ijin -> Ijin Lokasi
        'izinkan_lokasi_absensi_mandiri' => 'Ya',
        'izinkan_lokasi_scan_qr' => 'Tidak',
        'latitude' => '-6.922405',
        'longitude' => '107.5717651',
        'radius_jarak_absen' => '900',
        'minimal_akurasi_gps' => '100',
        'deteksi_fake_gps' => 'Ya',
        'zona_waktu' => 'Asia/Jakarta (WIB)',
        // Tab: Ijin -> Ijin Umum
        'ijinkan_absen_mandiri_web' => 'Ya',
        'ijinkan_absen_mandiri_android' => 'Ya',
        'ijinkan_simpan_foto_absen' => 'Tidak',
        'ijinkan_pembuatan_akun_mandiri' => 'Tidak',
        'ijinkan_pulang_lebih_awal' => '2024-07-27',
        'lock_device_android' => 'Tidak',
        'lock_device_pc' => 'Tidak',
        'ijinkan_pop_up_foto' => 'Tidak',
        'ijinkan_pop_up_jadwal' => 'Tidak',

        // Tab: Notifikasi
        'jenis_notifikasi_ortu' => 'WhatsApp (WA)',
        'mode_notifikasi_scan_qr' => 'Mode Audio',
        'varian_notifikasi_suara' => 'default',
        'aktifkan_bunyi_notif_absensi' => 'Ya',
        'freq_bunyi_hadir' => '880',
        'freq_bunyi_terlambat' => '440',
        'freq_bunyi_streak' => '523',
        'freq_bunyi_early' => '698',
        'freq_bunyi_normal' => '523',
        'freq_bunyi_late' => '349',
        'freq_bunyi_checkout' => '392',
        'pengiriman_notifikasi_scan_qr' => 'Kirim otomatis',
        'token_bot_telegram' => '6281285399737',
        'penerima_notifikasi_pendidik' => 'w6282187771403',
        'penerima_notifikasi_ajuan_ijin' => '6282295556906',
        'link_server_wa' => 'https://wa.lutfifuadi.my.id/send-message',
        'nomor_server_wa_api_key' => '6282295556906',
        'jeda_waktu_kirim_pesan_detik' => '5',
        'jeda_waktu_kirim_notifikasi_detik' => '1',
        // WA Gateway dedicated settings
        'wa_gateway_enabled' => 'Ya',
        'wa_api_key' => '',
        'wa_nomor_admin' => '',
        'wa_nomor_notifikasi' => '',

        // Sinkronisasi API Master
        'master_db_sync_enabled' => 'Ya',
        'master_db_sync_mode'    => 'otomatis',
        'master_db_sync_time' => '03:00',
        'master_db_api_url' => '',
        'master_db_api_key' => '',

        // Integrasi PMBM (Webhook Masuk)
        'pmbm_incoming_api_key' => '',
        
        // Kartu ID
        'tanda_tangan_kepala_sekolah' => '',
        'ttd_url'                     => '',
        'cap_sekolah'                 => '',
        'cap_url'                     => '',
        'kota_penerbitan'             => '',
        'logo_dinas'                  => '',
        'logo_dinas_url'              => '',

        // Legacy
        'logo_sekolah'         => '',
        'logo_url'            => '',
        'jam_masuk'            => '07:00',
        'jam_batas_masuk'      => '08:00',
        'jam_pulang'           => '15:00',
        'jam_akhir_pulang'     => '17:00',
        'jam_mulai_pulang'     => '14:00',
        'toleransi_terlambat'  => '15',

        // GitHub Update Settings
        'github_repo_owner' => '',
        'github_repo_name' => '',
        'app_version' => '1.10.5',

        // AI Configuration
        'gemini_api_key' => '',

        // Google Drive Configuration
        'google_drive_folder_id' => '',
        'google_drive_credentials_json' => '',
        'google_drive_auth_type' => 'service_account',
        'google_drive_client_id' => '',
        'google_drive_client_secret' => '',
        'google_drive_refresh_token' => '',

        // Google Fonts Configuration
        'google_font_family' => 'Product Sans',
        'live_board_font_family' => 'Product Sans',
        'live_board_counter_font_family' => 'Courier New',
        'live_board_counter_color' => '#7367f0',
    ];

    protected \App\Services\UpdateService $updateService;

    public function __construct(\App\Services\UpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    public function index()
    {
        $settings = [];
        foreach ($this->defaults as $key => $default) {
            $row = Pengaturan::where('key', $key)->first();
            $value = $row ? $row->value : null;

            if ($key === 'pmbm_incoming_api_key' && trim((string) $value) === '') {
                $value = env('PMBM_INCOMING_API_KEY', $default);
            }

            $settings[$key] = $row ? $value : $default;
        }
        // Jangan tampilkan hash password, hanya status apakah sudah diset
        $settings['scan_qr_password_set'] = !empty($settings['password_unlock_scan_qr']);
        $settings['password_unlock_scan_qr'] = '';

        $currentVersion = $this->updateService->getCurrentVersion();
        $updateInfo = $this->updateService->getCachedUpdateInfo();

        $setting = GoogleSheetSetting::where('type', 'siswa')->first() ?? GoogleSheetSetting::whereNull('type')->first() ?? new GoogleSheetSetting(['type' => 'siswa']);
        $guruSetting = GoogleSheetSetting::where('type', 'guru')->first() ?? new GoogleSheetSetting(['type' => 'guru']);

        $tahunAkademikList = \App\Models\TahunAkademik::orderBy('nama', 'desc')
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $kelasList = \App\Models\Kelas::orderBy('tingkat', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin.pengaturan.index', compact('settings', 'currentVersion', 'updateInfo', 'setting', 'guruSetting', 'tahunAkademikList', 'kelasList'));
    }

    public function clearCache(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');

            return response()->json([
                'success' => true,
                'message' => 'Cache aplikasi berhasil dibersihkan!'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal membersihkan cache: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan cache: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'tanda_tangan_kepala_sekolah' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'cap_sekolah'                 => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $data = $request->except(['_token', 'logo_sekolah', 'logo_url', 'logo_dinas', 'logo_dinas_url', 'tanda_tangan_kepala_sekolah', 'ttd_url', 'cap_sekolah', 'cap_url', 'google_drive_credentials_file']);
        
        // Handle Google Drive Credentials JSON Upload
        if ($request->hasFile('google_drive_credentials_file')) {
            try {
                $file = $request->file('google_drive_credentials_file');
                $request->validate([
                    'google_drive_credentials_file' => 'required|file',
                ]);
                // Read the JSON file content and validate it is valid json
                $jsonContent = file_get_contents($file->getRealPath());
                $decoded = json_decode($jsonContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()->withInput()->with('error', 'File credentials Google Drive harus berupa file JSON yang valid.');
                }
                
                // Encrypt and store
                $encryptedContent = encrypt($jsonContent, false);
                $data['google_drive_credentials_json'] = $encryptedContent;
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal upload credentials Google Drive: ' . $e->getMessage());
            }
        }

        // Clean up oauth credentials space to avoid storing unexpected values
        // Note: they are simple text fields, so they are part of $data and will be saved automatically by the loop.

        // Hash password scan QR jika diisi, atau hapus dari $data jika kosong
        if (!empty($data['password_unlock_scan_qr'])) {
            $data['password_unlock_scan_qr'] = Hash::make($data['password_unlock_scan_qr']);
        } else {
            unset($data['password_unlock_scan_qr']);
        }

        // Handle logo upload dari file — simpan langsung ke public/uploads/logo/
        if ($request->hasFile('logo_sekolah')) {
            $old = Pengaturan::where('key', 'logo_sekolah')->value('value');
            if ($old) {
                $oldPath = public_path('uploads/logo/' . $old);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('logo_sekolah');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logo'), $filename);
            $data['logo_sekolah'] = $filename;
            // Hapus logo_url jika ada, karena sudah pakai file lokal
            $data['logo_url'] = '';
        }
        
        // Handle logo_dinas upload — simpan langsung ke public/uploads/logo/
        if ($request->hasFile('logo_dinas')) {
            $old = Pengaturan::where('key', 'logo_dinas')->value('value');
            if ($old) {
                $oldPath = public_path('uploads/logo/' . $old);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $file = $request->file('logo_dinas');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/logo'), $filename);
            $data['logo_dinas'] = $filename;
            // Hapus logo_dinas_url jika ada, karena sudah pakai file lokal
            $data['logo_dinas_url'] = '';
        }
        
        // Handle tanda_tangan_kepala_sekolah upload
        if ($request->hasFile('tanda_tangan_kepala_sekolah')) {
            try {
                $file = $request->file('tanda_tangan_kepala_sekolah');
                $old = Pengaturan::where('key', 'tanda_tangan_kepala_sekolah')->value('value');
                $googleDriveService = app(\App\Services\GoogleDriveService::class);

                if ($googleDriveService->isEnabled()) {
                    $oldFileId = ($old && strlen($old) > 30) ? $old : null;
                    $newFileId = $googleDriveService->uploadPhoto($file, $oldFileId);
                    if ($newFileId) {
                        $data['tanda_tangan_kepala_sekolah'] = $newFileId;
                    } else {
                        throw new \Exception('Gagal upload tanda tangan ke Google Drive.');
                    }
                } else {
                    $ttdDir = public_path('uploads/ttd');
                    if (! is_dir($ttdDir)) {
                        mkdir($ttdDir, 0775, true);
                    }

                    if ($old && strlen($old) <= 30) {
                        @unlink($ttdDir . '/' . $old);
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($ttdDir, $filename);
                    $data['tanda_tangan_kepala_sekolah'] = $filename;
                }
                // Hapus ttd_url jika ada, karena sudah pakai file lokal
                $data['ttd_url'] = '';
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal upload tanda tangan: ' . $e->getMessage());
            }
        }

        // Handle cap_sekolah upload
        if ($request->hasFile('cap_sekolah')) {
            try {
                $file = $request->file('cap_sekolah');
                $old = Pengaturan::where('key', 'cap_sekolah')->value('value');
                $googleDriveService = app(\App\Services\GoogleDriveService::class);

                if ($googleDriveService->isEnabled()) {
                    $oldFileId = ($old && strlen($old) > 30) ? $old : null;
                    $newFileId = $googleDriveService->uploadPhoto($file, $oldFileId);
                    if ($newFileId) {
                        $data['cap_sekolah'] = $newFileId;
                    } else {
                        throw new \Exception('Gagal upload cap sekolah ke Google Drive.');
                    }
                } else {
                    $capDir = public_path('uploads/cap');
                    if (! is_dir($capDir)) {
                        mkdir($capDir, 0775, true);
                    }

                    if ($old && strlen($old) <= 30) {
                        @unlink($capDir . '/' . $old);
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $file->move($capDir, $filename);
                    $data['cap_sekolah'] = $filename;
                }
                // Hapus cap_url jika ada, karena sudah pakai file lokal
                $data['cap_url'] = '';
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal upload cap sekolah: ' . $e->getMessage());
            }
        }

        // Handle logo dari URL/S3 - simpan URL terus
        $logoUrl = $request->input('logo_url');
        if (!empty($logoUrl)) {
            $validatedUrl = filter_var($logoUrl, FILTER_VALIDATE_URL);
            if ($validatedUrl) {
                // Simpan URL terus, jangan download
                $data['logo_url'] = $validatedUrl;
            }
        }

        // Handle logo_dinas dari URL/S3 - simpan URL terus
        $logoDinasUrl = $request->input('logo_dinas_url');
        if (!empty($logoDinasUrl)) {
            $validatedUrl = filter_var($logoDinasUrl, FILTER_VALIDATE_URL);
            if ($validatedUrl) {
                $data['logo_dinas_url'] = $validatedUrl;
            }
        }

        // Handle ttd_url - simpan URL terus
        $ttdUrl = $request->input('ttd_url');
        if (!empty($ttdUrl)) {
            $validatedUrl = filter_var($ttdUrl, FILTER_VALIDATE_URL);
            if ($validatedUrl) {
                $data['ttd_url'] = $validatedUrl;
            }
        }

        // Handle cap_url - simpan URL terus
        $capUrl = $request->input('cap_url');
        if (!empty($capUrl)) {
            $validatedUrl = filter_var($capUrl, FILTER_VALIDATE_URL);
            if ($validatedUrl) {
                $data['cap_url'] = $validatedUrl;
            }
        }

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->defaults)) {
                $group = $this->groupFor($key);

                // Proteksi: Hanya super_admin yang boleh update grup 'update' atau setting API Master
                if ($group === 'update' || str_starts_with($key, 'master_db_')) {
                    if (!auth()->user()->isSuperAdmin()) {
                        continue;
                    }
                }

                Pengaturan::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => $group]
                );
            }
        }

        // Hapus cache absensi_settings agar live-board pakai nilai terbaru
        Cache::forget('absensi_settings');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function groupFor(string $key): string
    {
        if (str_starts_with($key, 'github_') || $key === 'app_version') {
            return 'update';
        }
        return 'umum';
    }

    public function updateTheme(Request $request)
    {
        $keys = [
            'theme_primary', 'theme_success', 'theme_info', 'theme_warning', 
            'theme_danger', 'theme_secondary', 'theme_text_main', 
            'theme_surface', 'theme_border', 'theme_hero_preset'
        ];

        // Validasi: menolak format warna yang tidak valid (bukan hex valid / di luar enum preset)
        $rules = [];
        foreach ($keys as $key) {
            if ($key === 'theme_hero_preset') {
                $rules[$key] = 'required|string|in:default,ocean,forest,sunset,twilight,dark,custom';
            } elseif (in_array($key, ['theme_surface', 'theme_border'])) {
                // Surface dan Border mendukung format hex (#ffffff) atau rgba (rgba(255,255,255,0.07))
                $rules[$key] = ['required', 'string', function ($attribute, $value, $fail) {
                    $isHex = preg_match('/^#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?$/', $value);
                    $isRgba = preg_match('/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(0(\.\d+)?|1(\.0+)?)\s*\)$/', $value) ||
                              preg_match('/^rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)$/', $value);
                    if (!$isHex && !$isRgba) {
                        $fail("Warna {$attribute} harus berupa format HEX atau RGBA yang valid.");
                    }
                }];
            } else {
                $rules[$key] = ['required', 'string', 'regex:/^#[a-fA-F0-9]{3}([a-fA-F0-9]{3})?$/'];
            }
        }

        $validated = $request->validate($rules);

        // helper logic to auto-generate soft-colors
        // soft-colors are primary, success, info, warning, danger, secondary
        // format is rgba(r, g, b, 0.12)
        $hexToRgb = function ($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return "rgba($r, $g, $b, 0.12)";
        };

        foreach ($validated as $key => $value) {
            Pengaturan::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'theme']
            );

            // Generate soft colors if it's one of the main colors
            $colorName = str_replace('theme_', '', $key);
            if (in_array($colorName, ['primary', 'success', 'info', 'warning', 'danger', 'secondary'])) {
                $softValue = $hexToRgb($value);
                Pengaturan::updateOrCreate(
                    ['key' => "theme_{$colorName}_soft"],
                    ['value' => $softValue, 'group' => 'theme']
                );
            }
        }

        // Bersihkan cache `das_theme_vars`
        Cache::forget('das_theme_vars');

        return response()->json([
            'success' => true,
            'message' => 'Kustomisasi warna tema UI berhasil disimpan.'
        ]);
    }

    public function resetTheme()
    {
        // Hapus data pengaturan yang bertipe theme
        Pengaturan::where('group', 'theme')->delete();

        // Bersihkan cache `das_theme_vars`
        Cache::forget('das_theme_vars');

        return response()->json([
            'success' => true,
            'message' => 'Tema UI berhasil direset ke default.'
        ]);
    }
}
