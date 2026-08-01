<?php

namespace App\Services;

use App\Models\IdCardTemplate;
use App\Models\Pengaturan;
use App\Support\QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class IdCardPdfService
{
    /**
     * Ambil semua data lembaga dari tabel pengaturan sekaligus.
     *
     * @return array
     */
    public function getLembagaData(): array
    {
        $keys = [
            'nama_sekolah',
            'alamat_lembaga',
            'email_lembaga',
            'website_lembaga',
            'nama_kepala_lembaga',
            'nip_kepala_lembaga',
            'logo_sekolah',
            'logo_url',
            'logo_dinas',
            'logo_dinas_url',
            'kota_penerbitan',
            'tanda_tangan_kepala_sekolah',
            'ttd_url',
            'cap_sekolah',
            'cap_url',
            'jumlah_tahun_sekolah',
        ];

        $rows = [];
        foreach ($keys as $k) {
            $rows[$k] = setting($k);
        }

        $logoVal      = !empty($rows['logo_url']) ? $rows['logo_url'] : ($rows['logo_sekolah'] ?? '');
        $logoDinasVal = !empty($rows['logo_dinas_url']) ? $rows['logo_dinas_url'] : ($rows['logo_dinas'] ?? '');
        $ttdVal       = !empty($rows['ttd_url']) ? $rows['ttd_url'] : ($rows['tanda_tangan_kepala_sekolah'] ?? '');
        $capVal       = !empty($rows['cap_url']) ? $rows['cap_url'] : ($rows['cap_sekolah'] ?? '');

        return [
            'nama_sekolah'                 => $rows['nama_sekolah'] ?? 'Madrasah Aliyah',
            'alamat_lembaga'               => $rows['alamat_lembaga'] ?? '',
            'email_lembaga'                => $rows['email_lembaga'] ?? '',
            'website_lembaga'              => $rows['website_lembaga'] ?? '',
            'nama_kepala_lembaga'          => $rows['nama_kepala_lembaga'] ?? '',
            'nip_kepala_lembaga'           => $rows['nip_kepala_lembaga'] ?? '',
            'logo_sekolah'                 => $rows['logo_sekolah'] ?? '',
            'logo_url'                     => $rows['logo_url'] ?? '',
            'logo_dinas'                   => $rows['logo_dinas'] ?? '',
            'logo_dinas_url'               => $rows['logo_dinas_url'] ?? '',
            'kota_penerbitan'              => $rows['kota_penerbitan'] ?? '',
            'tanda_tangan_kepala_sekolah'  => $rows['tanda_tangan_kepala_sekolah'] ?? '',
            'ttd_url'                      => $rows['ttd_url'] ?? '',
            'cap_sekolah'                  => $rows['cap_sekolah'] ?? '',
            'cap_url'                      => $rows['cap_url'] ?? '',
            'jumlah_tahun_sekolah'         => (int) ($rows['jumlah_tahun_sekolah'] ?? 3),

            // Base64 images
            'logo_base64'       => $this->toBase64($logoVal, 'logo'),
            'logo_dinas_base64' => $this->toBase64($logoDinasVal, 'logo'),
            'ttd_base64'        => $this->toBase64($ttdVal, 'ttd'),
            'cap_base64'        => $this->toBase64($capVal, 'cap'),
        ];
    }

    /**
     * Konversi file gambar atau URL eksternal (S3, Drive) ke data URI base64.
     *
     * @param  string  $filename  Nama file / URL eksternal / Google Drive ID
     * @param  string  $folder    Nama folder di public/uploads/
     * @return string
     */
    private function toBase64(string $filename, string $folder): string
    {
        if (empty($filename)) {
            return '';
        }

        // 1. Jika URL eksternal (S3, CDN, HTTP/HTTPS)
        if (str_starts_with($filename, 'http://') || str_starts_with($filename, 'https://')) {
            try {
                $data = @file_get_contents($filename);
                if ($data !== false) {
                    $extPath = parse_url($filename, PHP_URL_PATH);
                    $ext     = strtolower(pathinfo($extPath, PATHINFO_EXTENSION)) ?: 'png';
                    $mime    = match ($ext) {
                        'png'         => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif'         => 'image/gif',
                        'svg'         => 'image/svg+xml',
                        default       => 'image/png',
                    };
                    return "data:{$mime};base64," . base64_encode($data);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('IdCardPdfService toBase64: Gagal fetch gambar dari URL: ' . $e->getMessage());
            }
            return $filename; // Fallback ke raw URL jika fetch bermasalah
        }

        // 2. Jika Google Drive file ID: string panjang > 25, tanpa pemisah path atau titik ekstensi
        if (strlen($filename) > 25
            && !str_contains($filename, '/')
            && !str_contains($filename, '\\')
            && !str_contains($filename, '.')
        ) {
            try {
                return app(\App\Services\GoogleDriveService::class)->getPhotoBase64($filename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('IdCardPdfService toBase64: Gagal mengambil base64 dari Google Drive: ' . $e->getMessage());
                return '';
            }
        }

        // 3. File lokal di public/uploads/{folder}/{filename} atau storage/app/public/
        $path = public_path("uploads/{$folder}/{$filename}");
        if (!file_exists($path)) {
            $storagePath = storage_path("app/public/{$folder}/{$filename}");
            if (file_exists($storagePath)) {
                $path = $storagePath;
            }
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return '';
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            'svg'         => 'image/svg+xml',
            default       => 'image/png',
        };

        return "data:{$mime};base64," . base64_encode($data);
    }

    /**
     * Hitung masa berlaku kartu pelajar berdasarkan tahun akademik.
     *
     * @param  \App\Models\Siswa  $siswa
     * @param  int                $jumlahTahun
     * @return string
     */
    public function hitungMasaBerlakuSiswa($siswa, int $jumlahTahun): string
    {
        $tahunLulus = \App\Helpers\JenjangHelper::getTahunLulusSiswa($siswa);
        if ($tahunLulus) {
            return '30 Juni ' . $tahunLulus;
        }

        return 'Selama menjadi siswa aktif';
    }

    /**
     * Render PDF kartu pelajar siswa.
     *
     * @param  \Illuminate\Support\Collection  $siswaList
     * @param  \App\Models\IdCardTemplate|null  $template
     * @param  string                           $label       Nama file PDF (tanpa .pdf)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderKartuSiswa(Collection $siswaList, ?IdCardTemplate $template, string $label): Response
    {
        $lembaga     = $this->getLembagaData();
        $jumlahTahun = $lembaga['jumlah_tahun_sekolah'];

        // Jika template null → fallback download kartu QR lama
        if (! $template) {
            $qrImages = $siswaList->mapWithKeys(function ($siswa) {
                if (! $siswa->qr_code) {
                    $fallback = $siswa->nisn ?: QrCodeGenerator::generate('SISWA');
                    $siswa->update(['qr_code' => $fallback]);
                    $siswa->refresh();
                }
                return [$siswa->id => QrCodeGenerator::renderDataUri($siswa->qr_code, 160)];
            });

            $namaSekolah = $lembaga['nama_sekolah'];
            $namaKelas   = $label;

            return Pdf::loadView('admin.siswa.kartu-qr-pdf', compact('siswaList', 'namaSekolah', 'namaKelas', 'qrImages'))
                ->setPaper('a4', 'portrait')
                ->download("{$label}.pdf");
        }

        $logoFallback = $lembaga['logo_base64'] ?: ($lembaga['logo_url'] ?? '');

        // Siapkan data entitas dengan masa berlaku, foto base64, QR base64
        $entities = $siswaList->map(function ($siswa) use ($jumlahTahun, $logoFallback) {
            // Pastikan ada QR
            if (! $siswa->qr_code) {
                $fallback = $siswa->nisn ?: QrCodeGenerator::generate('SISWA');
                $siswa->update(['qr_code' => $fallback]);
                $siswa->refresh();
            }

            $masaBerlaku = $this->hitungMasaBerlakuSiswa($siswa, $jumlahTahun);
            $fotoBase64  = $this->fotoToBase64($siswa->foto ?? '', $logoFallback);
            $qrBase64    = QrCodeGenerator::renderDataUri($siswa->qr_code, 200);

            $siswa->_nis = $siswa->nis;
            $siswa->_nisn = $siswa->nisn;
            $siswa->_masa_berlaku = $masaBerlaku;
            $siswa->_foto_base64  = $fotoBase64;
            $siswa->_qr_base64    = $qrBase64;

            return $siswa;
        });

        $config = $template->config;
        $configFront = $this->extractFrontConfig($config);
        $configBack  = $this->extractBackConfig($config);
        $hasSideBack = $this->hasActiveBackSide($config);

        $paperWidth  = $hasSideBack ? ($config['canvas']['width'] * 2) : $config['canvas']['width'];
        $paperHeight = $config['canvas']['height'];

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'configFront', 'configBack', 'hasSideBack', 'entities', 'lembaga'))
            ->setPaper([0, 0, $paperWidth, $paperHeight])
            ->download("{$label}.pdf");
    }

    /**
     * Render PDF kartu identitas guru.
     *
     * @param  \Illuminate\Support\Collection  $guruList
     * @param  \App\Models\IdCardTemplate|null  $template
     * @param  string                           $label
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderKartuGuru(Collection $guruList, ?IdCardTemplate $template, string $label): Response
    {
        $lembaga = $this->getLembagaData();

        // Masa berlaku guru: statis dari pengaturan atau default
        $masaBerlakuDefault = setting('masa_berlaku_kartu')
            ?? 'Selama menjadi guru aktif';

        // Jika template null → fallback kartu QR lama
        if (! $template) {
            $qrImages = $guruList->mapWithKeys(function ($guru) {
                if (! $guru->qr_code || ! $guru->qr_code_nip) {
                    $guru->update([
                        'qr_code' => $guru->qr_code ?? QrCodeGenerator::generate('GURU'),
                        'qr_code_nip' => $guru->qr_code_nip ?? $guru->nip,
                    ]);
                    $guru->refresh();
                }
                return [
                    $guru->id => [
                        'unik' => QrCodeGenerator::renderDataUri($guru->qr_code, 160),
                        'nip'  => QrCodeGenerator::renderDataUri($guru->qr_code_nip ?? $guru->nip, 160),
                    ]
                ];
            });

            $namaSekolah = $lembaga['nama_sekolah'];

            return Pdf::loadView('admin.guru.kartu-qr-pdf', compact('guruList', 'namaSekolah', 'qrImages'))
                ->setPaper('a4', 'portrait')
                ->download("{$label}.pdf");
        }

        $logoFallback = $lembaga['logo_base64'] ?: ($lembaga['logo_url'] ?? '');

        $entities = $guruList->map(function ($guru) use ($masaBerlakuDefault, $logoFallback) {
            if (! $guru->qr_code || ! $guru->qr_code_nip) {
                $guru->update([
                    'qr_code' => $guru->qr_code ?? QrCodeGenerator::generate('GURU'),
                    'qr_code_nip' => $guru->qr_code_nip ?? $guru->nip,
                ]);
                $guru->refresh();
            }

            $guru->_nip = $guru->nip;
            $guru->_masa_berlaku = $masaBerlakuDefault;
            $guru->_foto_base64  = $this->fotoToBase64($guru->foto ?? '', $logoFallback);
            $guru->_qr_base64    = QrCodeGenerator::renderDataUri($guru->qr_code, 200);
            $guru->_qr_nip_base64 = QrCodeGenerator::renderDataUri($guru->qr_code_nip ?? $guru->nip, 200);
            $guru->_posisi       = $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran);

            return $guru;
        });

        $config = $template->config;
        $configFront = $this->extractFrontConfig($config);
        $configBack  = $this->extractBackConfig($config);
        $hasSideBack = $this->hasActiveBackSide($config);

        $paperWidth  = $hasSideBack ? ($config['canvas']['width'] * 2) : $config['canvas']['width'];
        $paperHeight = $config['canvas']['height'];

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'configFront', 'configBack', 'hasSideBack', 'entities', 'lembaga'))
            ->setPaper([0, 0, $paperWidth, $paperHeight])
            ->download("{$label}.pdf");
    }

    /**
     * Render PDF kartu identitas staff TU.
     *
     * @param  \Illuminate\Support\Collection  $staffList
     * @param  \App\Models\IdCardTemplate|null  $template
     * @param  string                           $label
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderKartuStaff(Collection $staffList, ?IdCardTemplate $template, string $label): Response
    {
        $lembaga = $this->getLembagaData();

        $masaBerlakuDefault = setting('masa_berlaku_kartu')
            ?? 'Selama menjadi staff aktif';

        // Jika template null → fallback
        if (! $template) {
            // Staff tidak punya template fallback qr-pdf lama, redirect dengan pesan error
            abort(422, 'Template ID Card untuk Staff tidak ditemukan. Silakan buat dan aktifkan template terlebih dahulu.');
        }

        $logoFallback = $lembaga['logo_base64'] ?: ($lembaga['logo_url'] ?? '');

        $entities = $staffList->map(function ($staff) use ($masaBerlakuDefault, $logoFallback) {
            if (! $staff->qr_code || ! $staff->qr_code_nip) {
                $staff->update([
                    'qr_code' => $staff->qr_code ?? QrCodeGenerator::generate('STAFF'),
                    'qr_code_nip' => $staff->qr_code_nip ?? $staff->nip,
                ]);
                $staff->refresh();
            }

            $staff->_nip = $staff->nip;
            $staff->_masa_berlaku = $masaBerlakuDefault;
            $staff->_foto_base64  = $this->fotoToBase64($staff->foto ?? '', $logoFallback);
            $staff->_qr_base64    = QrCodeGenerator::renderDataUri($staff->qr_code, 200);
            $staff->_qr_nip_base64 = QrCodeGenerator::renderDataUri($staff->qr_code_nip ?? $staff->nip, 200);
            $staff->_posisi       = $staff->jabatan ?? 'Staff Tata Usaha';

            return $staff;
        });

        $config = $template->config;
        $configFront = $this->extractFrontConfig($config);
        $configBack  = $this->extractBackConfig($config);
        $hasSideBack = $this->hasActiveBackSide($config);

        $paperWidth  = $hasSideBack ? ($config['canvas']['width'] * 2) : $config['canvas']['width'];
        $paperHeight = $config['canvas']['height'];

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'configFront', 'configBack', 'hasSideBack', 'entities', 'lembaga'))
            ->setPaper([0, 0, $paperWidth, $paperHeight])
            ->download("{$label}.pdf");
    }

    /**
     * Helper untuk mengekstrak config elemen sisi Front.
     */
    public function extractFrontConfig(array $config): array
    {
        if (isset($config['front']['elements'])) {
            return [
                'canvas'   => $config['canvas'],
                'elements' => $config['front']['elements'],
            ];
        }
        return $config; // format lama
    }

    /**
     * Helper untuk mengekstrak config elemen sisi Back.
     */
    public function extractBackConfig(array $config): ?array
    {
        if (!isset($config['back']['elements'])) {
            return null;
        }
        return [
            'canvas'   => $config['canvas'],
            'elements' => $config['back']['elements'],
        ];
    }

    /**
     * Cek apakah template memiliki sisi Back yang aktif.
     */
    public function hasActiveBackSide(array $config): bool
    {
        if (isset($config['back']['elements'])) {
            foreach ($config['back']['elements'] as $element) {
                if (!empty($element['show'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Konversi foto entitas ke base64.
     * Path foto ada di storage/app/public/
     * Jika foto kosong/gagal muat, gunakan $fallbackLogoBase64 (Logo Sekolah).
     *
     * @param  string  $fotoPath  Relative path dari storage/app/public/
     * @param  string  $fallbackLogoBase64
     * @return string
     */
    private function fotoToBase64(string $fotoPath, string $fallbackLogoBase64 = ''): string
    {
        if (empty($fotoPath)) {
            return $fallbackLogoBase64;
        }

        // Check if Google Drive File ID
        if (strlen($fotoPath) > 30 && !str_contains($fotoPath, '/') && !str_contains($fotoPath, '\\')) {
            try {
                $driveBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($fotoPath);
                return !empty($driveBase64) ? $driveBase64 : $fallbackLogoBase64;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('IdCardPdfService: Gagal mengambil base64 dari Google Drive: ' . $e->getMessage());
                return $fallbackLogoBase64;
            }
        }

        $fullPath = storage_path('app/public/' . $fotoPath);
        $data     = @file_get_contents($fullPath);

        if ($data === false) {
            return $fallbackLogoBase64;
        }

        $ext  = strtolower(pathinfo($fotoPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'         => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'         => 'image/gif',
            default       => 'image/jpeg',
        };

        return "data:{$mime};base64," . base64_encode($data);
    }
}
