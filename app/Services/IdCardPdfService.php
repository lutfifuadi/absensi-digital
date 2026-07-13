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
            'kota_penerbitan',
            'tanda_tangan_kepala_sekolah',
            'cap_sekolah',
            'jumlah_tahun_sekolah',
        ];

        $rows = Pengaturan::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        return [
            'nama_sekolah'                 => $rows['nama_sekolah'] ?? 'Madrasah Aliyah',
            'alamat_lembaga'               => $rows['alamat_lembaga'] ?? '',
            'email_lembaga'                => $rows['email_lembaga'] ?? '',
            'website_lembaga'              => $rows['website_lembaga'] ?? '',
            'nama_kepala_lembaga'          => $rows['nama_kepala_lembaga'] ?? '',
            'nip_kepala_lembaga'           => $rows['nip_kepala_lembaga'] ?? '',
            'logo_sekolah'                 => $rows['logo_sekolah'] ?? '',
            'logo_url'                     => $rows['logo_url'] ?? '',
            'kota_penerbitan'              => $rows['kota_penerbitan'] ?? '',
            'tanda_tangan_kepala_sekolah'  => $rows['tanda_tangan_kepala_sekolah'] ?? '',
            'cap_sekolah'                  => $rows['cap_sekolah'] ?? '',
            'jumlah_tahun_sekolah'         => (int) ($rows['jumlah_tahun_sekolah'] ?? 3),

            // Base64 images
            'logo_base64'  => $this->toBase64($rows['logo_sekolah'] ?? '', 'logo'),
            'ttd_base64'   => $this->toBase64($rows['tanda_tangan_kepala_sekolah'] ?? '', 'ttd'),
            'cap_base64'   => $this->toBase64($rows['cap_sekolah'] ?? '', 'cap'),
        ];
    }

    /**
     * Konversi file gambar ke data URI base64.
     *
     * @param  string  $filename  Nama file saja (bukan path lengkap)
     * @param  string  $folder    Nama folder di public/uploads/
     * @return string
     */
    private function toBase64(string $filename, string $folder): string
    {
        if (empty($filename)) {
            return '';
        }

        if (strlen($filename) > 30) {
            try {
                return app(\App\Services\GoogleDriveService::class)->getPhotoBase64($filename);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('IdCardPdfService toBase64: Gagal mengambil base64 dari Google Drive: ' . $e->getMessage());
                return '';
            }
        }

        $path = public_path("uploads/{$folder}/{$filename}");
        $data = @file_get_contents($path);

        if ($data === false) {
            return '';
        }

        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png'       => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'       => 'image/gif',
            'svg'       => 'image/svg+xml',
            default     => 'image/png',
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
        // 1. Cek apakah relasi tahunAkademik ada dan punya tanggal_mulai
        if ($siswa->tahunAkademik && $siswa->tahunAkademik->tanggal_mulai) {
            try {
                $tahun = (int) \Carbon\Carbon::parse($siswa->tahunAkademik->tanggal_mulai)->format('Y');
                return '30 Juni ' . ($tahun + $jumlahTahun);
            } catch (\Exception $e) {
                // lanjut ke fallback
            }
        }

        // 2. Fallback: parse dari nama tahun akademik (format "2023/2024")
        if ($siswa->tahunAkademik && $siswa->tahunAkademik->nama) {
            $nama = $siswa->tahunAkademik->nama;
            if (preg_match('/(\d{4})/', $nama, $matches)) {
                $tahun = (int) $matches[1];
                return '30 Juni ' . ($tahun + $jumlahTahun);
            }
        }

        // 3. Ultimate fallback
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

        // Siapkan data entitas dengan masa berlaku, foto base64, QR base64
        $entities = $siswaList->map(function ($siswa) use ($jumlahTahun) {
            // Pastikan ada QR
            if (! $siswa->qr_code) {
                $fallback = $siswa->nisn ?: QrCodeGenerator::generate('SISWA');
                $siswa->update(['qr_code' => $fallback]);
                $siswa->refresh();
            }

            $masaBerlaku = $this->hitungMasaBerlakuSiswa($siswa, $jumlahTahun);
            $fotoBase64  = $this->fotoToBase64($siswa->foto ?? '');
            $qrBase64    = QrCodeGenerator::renderDataUri($siswa->qr_code, 200);

            $siswa->_nis = $siswa->nis;
            $siswa->_nisn = $siswa->nisn;
            $siswa->_masa_berlaku = $masaBerlaku;
            $siswa->_foto_base64  = $fotoBase64;
            $siswa->_qr_base64    = $qrBase64;

            return $siswa;
        });

        $config = $template->config;

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities', 'lembaga'))
            ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
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
        $masaBerlakuDefault = Pengaturan::where('key', 'masa_berlaku_kartu')->value('value')
            ?? 'Selama menjadi guru aktif';

        // Jika template null → fallback kartu QR lama
        if (! $template) {
            $qrImages = $guruList->mapWithKeys(function ($guru) {
                if (! $guru->qr_code) {
                    $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
                    $guru->refresh();
                }
                return [$guru->id => QrCodeGenerator::renderDataUri($guru->qr_code, 160)];
            });

            $namaSekolah = $lembaga['nama_sekolah'];

            return Pdf::loadView('admin.guru.kartu-qr-pdf', compact('guruList', 'namaSekolah', 'qrImages'))
                ->setPaper('a4', 'portrait')
                ->download("{$label}.pdf");
        }

        $entities = $guruList->map(function ($guru) use ($masaBerlakuDefault) {
            if (! $guru->qr_code) {
                $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
                $guru->refresh();
            }

            $guru->_nip = $guru->nip;
            $guru->_masa_berlaku = $masaBerlakuDefault;
            $guru->_foto_base64  = $this->fotoToBase64($guru->foto ?? '');
            $guru->_qr_base64    = QrCodeGenerator::renderDataUri($guru->qr_code, 200);
            $guru->_posisi       = $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran);

            return $guru;
        });

        $config = $template->config;

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities', 'lembaga'))
            ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
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

        $masaBerlakuDefault = Pengaturan::where('key', 'masa_berlaku_kartu')->value('value')
            ?? 'Selama menjadi staff aktif';

        // Jika template null → fallback
        if (! $template) {
            // Staff tidak punya template fallback qr-pdf lama, redirect dengan pesan error
            abort(422, 'Template ID Card untuk Staff tidak ditemukan. Silakan buat dan aktifkan template terlebih dahulu.');
        }

        $entities = $staffList->map(function ($staff) use ($masaBerlakuDefault) {
            if (! $staff->qr_code) {
                $staff->update(['qr_code' => QrCodeGenerator::generate('STAFF')]);
                $staff->refresh();
            }

            $staff->_nip = $staff->nip;
            $staff->_masa_berlaku = $masaBerlakuDefault;
            $staff->_foto_base64  = $this->fotoToBase64($staff->foto ?? '');
            $staff->_qr_base64    = QrCodeGenerator::renderDataUri($staff->qr_code, 200);
            $staff->_posisi       = $staff->jabatan ?? 'Staff Tata Usaha';

            return $staff;
        });

        $config = $template->config;

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities', 'lembaga'))
            ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
            ->download("{$label}.pdf");
    }

    /**
     * Konversi foto entitas ke base64.
     * Path foto ada di storage/app/public/
     *
     * @param  string  $fotoPath  Relative path dari storage/app/public/
     * @return string
     */
    private function fotoToBase64(string $fotoPath): string
    {
        if (empty($fotoPath)) {
            return '';
        }

        // Check if Google Drive File ID
        if (strlen($fotoPath) > 30) {
            try {
                return app(\App\Services\GoogleDriveService::class)->getPhotoBase64($fotoPath);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('IdCardPdfService: Gagal mengambil base64 dari Google Drive: ' . $e->getMessage());
                return '';
            }
        }

        $fullPath = storage_path('app/public/' . $fotoPath);
        $data     = @file_get_contents($fullPath);

        if ($data === false) {
            return '';
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
