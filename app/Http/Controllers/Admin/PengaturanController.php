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
        'cap_sekolah'                 => '',
        'kota_penerbitan'             => '',

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
        'app_version' => '1.3.0',

        // AI Configuration
        'gemini_api_key' => '',
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

        return view('admin.pengaturan.index', compact('settings', 'currentVersion', 'updateInfo', 'setting', 'guruSetting'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'logo_sekolah', 'logo_url', 'tanda_tangan_kepala_sekolah', 'cap_sekolah']);
        
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
        
        // Handle tanda_tangan_kepala_sekolah upload — simpan ke public/uploads/ttd/
        if ($request->hasFile('tanda_tangan_kepala_sekolah')) {
            try {
                $file = $request->file('tanda_tangan_kepala_sekolah');
                $file->validate(['mimes' => 'png,jpg,jpeg', 'max' => 2048]);

                $ttdDir = public_path('uploads/ttd');
                if (! is_dir($ttdDir)) {
                    mkdir($ttdDir, 0775, true);
                }

                $old = Pengaturan::where('key', 'tanda_tangan_kepala_sekolah')->value('value');
                if ($old) {
                    @unlink($ttdDir . '/' . $old);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($ttdDir, $filename);
                $data['tanda_tangan_kepala_sekolah'] = $filename;
            } catch (\Exception $e) {
                return back()->withInput()->with('error', 'Gagal upload tanda tangan: ' . $e->getMessage());
            }
        }

        // Handle cap_sekolah upload — simpan ke public/uploads/cap/
        if ($request->hasFile('cap_sekolah')) {
            try {
                $file = $request->file('cap_sekolah');
                $file->validate(['mimes' => 'png,jpg,jpeg', 'max' => 2048]);

                $capDir = public_path('uploads/cap');
                if (! is_dir($capDir)) {
                    mkdir($capDir, 0775, true);
                }

                $old = Pengaturan::where('key', 'cap_sekolah')->value('value');
                if ($old) {
                    @unlink($capDir . '/' . $old);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($capDir, $filename);
                $data['cap_sekolah'] = $filename;
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
}
