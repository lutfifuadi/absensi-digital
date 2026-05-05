<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncMasterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:master-siswa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan sinkronisasi data siswa dan user dari aplikasi eksternal via API';

    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi data siswa dan user dari API eksternal...');

        $syncEnabled = Pengaturan::where('key', 'master_db_sync_enabled')->value('value') ?? 'Ya';
        if (strtolower($syncEnabled) === 'tidak') {
            $this->warn('Sinkronisasi master data dinonaktifkan melalui pengaturan.');
            return Command::SUCCESS;
        }

        $apiUrl = rtrim(Pengaturan::where('key', 'master_db_api_url')->value('value') ?? env('MASTER_DB_API_URL', ''), '/');
        $apiKey = Pengaturan::where('key', 'master_db_api_key')->value('value') ?? env('MASTER_DB_API_KEY', '');

        if (empty($apiUrl)) {
            $this->error('Gagal: master_db_api_url belum diatur di pengaturan admin atau .env');
            return Command::FAILURE;
        }

        // Endpoint langsung dari URL yang dikonfigurasi (tanpa append path)
        $endpoint = $apiUrl;

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept'    => 'application/json',
            ])
                ->timeout(30)
                ->retry(3, 1000)
                ->get($endpoint);

            if ($response->failed()) {
                $this->error('API eksternal tidak dapat diakses. HTTP Status: ' . $response->status());
                Log::error('Gagal sync master data.', ['endpoint' => $endpoint, 'status' => $response->status(), 'body' => $response->body()]);
                return Command::FAILURE;
            }

            $payload = $response->json();
            $listSiswa = [];

            if (is_array($payload) && array_key_exists('school', $payload)) {
                $this->info('Menyinkronkan data identitas sekolah dari pusat...');
                $this->syncService->syncSchoolSettings($payload['school']);
            }

            if (is_array($payload) && array_key_exists('guru', $payload)) {
                $this->info('Menyinkronkan data guru dari pusat...');
                foreach ($payload['guru'] as $dataGuru) {
                    try {
                        $this->syncService->syncGuru($dataGuru);
                    } catch (\Exception $e) {
                        $this->error('Gagal sync guru: ' . ($dataGuru['nip'] ?? 'unknown') . ' - ' . $e->getMessage());
                    }
                }
            }

            if (is_array($payload) && array_key_exists('staff', $payload)) {
                $this->info('Menyinkronkan data staff dari pusat...');
                foreach ($payload['staff'] as $dataStaff) {
                    try {
                        $this->syncService->syncStaff($dataStaff);
                    } catch (\Exception $e) {
                        $this->error('Gagal sync staff: ' . ($dataStaff['nip'] ?? 'unknown') . ' - ' . $e->getMessage());
                    }
                }
            }

            if (is_array($payload) && array_key_exists('data', $payload)) {
                $listSiswa = $payload['data'];
            } elseif (is_array($payload)) {
                $listSiswa = $payload;
            }

            if (!is_array($listSiswa) || count($listSiswa) === 0) {
                $this->warn('Data siswa dari API eksternal kosong atau format tidak dikenali.');
                Log::warning('Payload sinkronisasi kosong atau invalid.', ['endpoint' => $endpoint, 'payload' => $payload]);
                return Command::SUCCESS;
            }

            $countBaru = 0;
            $countUpdate = 0;
            $countFailed = 0;
            $countSkip  = 0;

            $tahunAkademikDefault = TahunAkademik::where('is_aktif', true)->value('id');

            // Domain email lembaga untuk generate email NISN-based
            $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'sekolah.sch.id';

            // Kelas X default: ambil kelas pertama dengan tingkat X pada TA aktif
            $kelasXDefault = Kelas::where('tingkat', 'X')
                ->when($tahunAkademikDefault, fn ($q) => $q->where('tahun_akademik_id', $tahunAkademikDefault))
                ->orderBy('nama')
                ->value('nama');

            foreach ($listSiswa as $dataSiswa) {
                // Syarat sinkronisasi ke absensi: status=lulus/lulus_cadangan DAN daftar_ulang_selesai=true
                $statusPmbm          = strtolower(trim($dataSiswa['status'] ?? ''));
                $daftarUlangSelesai  = (bool) ($dataSiswa['daftar_ulang_selesai'] ?? false);

                if (!in_array($statusPmbm, ['lulus', 'lulus_cadangan']) || !$daftarUlangSelesai) {
                    $countSkip++;
                    continue;
                }

                try {
                    $nisn  = $dataSiswa['nisn'] ?? $dataSiswa['nis'] ?? null;
                    $nis   = $dataSiswa['nis'] ?? $nisn; // fallback NIS ke NISN jika belum punya NIS

                    // Email: gunakan nisn@domain jika NISN tersedia, fallback ke email PMBM
                    $emailSiswa = $nisn
                        ? strtolower($nisn) . '@' . $domainEmail
                        : ($dataSiswa['email'] ?? null);

                    // no_hp_ortu: prioritas ayah > ibu > wali
                    $noHpOrtu = $dataSiswa['phone_ayah'] ?? $dataSiswa['phone_ibu'] ?? $dataSiswa['phone_wali'] ?? $dataSiswa['no_hp_ortu'] ?? null;

                    // Kelas: pakai dari data PMBM jika ada, fallback ke kelas X default
                    $kelasNama = $dataSiswa['nama_kelas'] ?? $dataSiswa['kelas_nama'] ?? $kelasXDefault;

                    $username = $nisn ? strtolower($nisn) : Str::before(strtolower($emailSiswa), '@');
                    $syncPayload = [
                        'nama_lengkap' => $dataSiswa['nama_lengkap'] ?? $dataSiswa['name'] ?? null,
                        'username' => $username,
                        'email' => $emailSiswa,
                        'password' => $dataSiswa['password'] ?? null,
                        'nis' => $nis,
                        'nisn' => $nisn,
                        'qr_code' => $dataSiswa['qr_code'] ?? $nisn,
                        'jenis_kelamin' => $this->normalizeJenisKelamin($dataSiswa['jenis_kelamin'] ?? $dataSiswa['gender'] ?? 'L'),
                        'tempat_lahir' => $dataSiswa['tempat_lahir'] ?? $dataSiswa['birth_place'] ?? null,
                        'tanggal_lahir' => $this->normalizeTanggalLahir($dataSiswa),
                        'alamat' => $dataSiswa['alamat'] ?? $dataSiswa['address'] ?? null,
                        'no_hp' => $dataSiswa['no_hp'] ?? $dataSiswa['phone_wa'] ?? $dataSiswa['phone'] ?? null,
                        'no_hp_ortu' => $noHpOrtu,
                        'kelas_nama' => $kelasNama,
                        'tahun_akademik_nama' => $dataSiswa['tahun_akademik_nama'] ?? $dataSiswa['tahun_akademik'] ?? null,
                        'tahun_akademik_id_default' => $tahunAkademikDefault,
                        // lulus/lulus_cadangan dari PMBM = siswa diterima/baru masuk → aktif
                        'status' => 'aktif',
                    ];

                    $siswa = $this->syncService->syncSiswa($syncPayload);
                    if ($siswa->wasRecentlyCreated) {
                        $countBaru++;
                        Log::info('PMBM Sync: Siswa baru ditambahkan ke sistem absensi.', ['nisn' => $nisn, 'nama' => $syncPayload['nama_lengkap']]);
                    } else {
                        $countUpdate++;
                    }
                } catch (\Exception $e) {
                    $countFailed++;
                    $this->error('Gagal sinkronisasi siswa: ' . ($dataSiswa['nis'] ?? $dataSiswa['nisn'] ?? 'unknown') . ' - ' . $e->getMessage());
                    Log::error('Sinkronisasi siswa gagal.', ['payload' => $dataSiswa, 'error' => $e->getMessage()]);
                }
            }

            $this->info("Sinkronisasi selesai. Baru: $countBaru, Update: $countUpdate, Lewati: $countSkip, Gagal: $countFailed.");
            Log::info('Sinkronisasi master data selesai.', ['baru' => $countBaru, 'update' => $countUpdate, 'lewati' => $countSkip, 'gagal' => $countFailed]);
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan sistem saat sinkronisasi: ' . $e->getMessage());
            Log::error('Sync master exception', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    private function normalizeJenisKelamin(string $value): string
    {
        return match (strtolower(trim($value))) {
            'laki-laki', 'laki laki', 'l', 'male'   => 'L',
            'perempuan', 'p', 'female'               => 'P',
            default                                  => 'L',
        };
    }

    private function normalizeTanggalLahir(array $data): ?string
    {
        // Format terpisah: tanggal_lahir (hari), bulan_lahir, tahun_lahir
        if (!empty($data['tahun_lahir']) && !empty($data['bulan_lahir']) && !empty($data['tanggal_lahir'])) {
            return sprintf(
                '%04d-%02d-%02d',
                (int) $data['tahun_lahir'],
                (int) $data['bulan_lahir'],
                (int) $data['tanggal_lahir']
            );
        }

        // Format string ISO (YYYY-MM-DD atau YYYY-MM-DD HH:MM:SS)
        if (!empty($data['tanggal_lahir'])) {
            $raw = $data['tanggal_lahir'];
            // Jika berupa angka bulat (unix timestamp)
            if (is_numeric($raw)) {
                return date('Y-m-d', (int) $raw);
            }
            // Ambil bagian tanggal saja
            return substr($raw, 0, 10);
        }

        return $data['birth_date'] ?? null;
    }

    private function normalizeStatus(string $value): string
    {
        return match (strtolower(trim($value))) {
            'aktif', 'active', 'diterima'                       => 'aktif',
            'alumni', 'graduated'                               => 'alumni',
            'nonaktif', 'inactive', 'keluar', 'pindah', 'drop'  => 'nonaktif',
            default                                             => 'aktif',
        };
    }
}
