<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pengaturan;
use App\Support\QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalSiswaController extends Controller
{
    public function downloadKartu()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->firstOrFail();
        
        $template = \App\Models\IdCardTemplate::where('type', 'siswa')->active()->first();

        // 1. Ambil data lembaga / pengaturan
        $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
        
        // Logo sekolah - konversi ke base64 data URI biar html2canvas gak kena CORS
        $logoPath = Pengaturan::where('key', 'logo_sekolah')->value('value');
        $logoSekolah = null;
        if ($logoPath) {
            $fullPath = public_path('storage/' . $logoPath);
            if (file_exists($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $mime = match($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                $imageData = @file_get_contents($fullPath);
                if ($imageData !== false) {
                    $logoSekolah = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                }
            }
        }

        // TTD & Cap Sekolah
        $ttdPath = Pengaturan::where('key', 'tanda_tangan_kepala_sekolah')->value('value');
        $ttdBase64 = null;
        if ($ttdPath) {
            if (strlen($ttdPath) > 30) {
                try {
                    $ttdBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($ttdPath);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal mengambil base64 ttd dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullTtdPath = public_path('uploads/ttd/' . $ttdPath);
                if (!file_exists($fullTtdPath)) {
                    $fullTtdPath = public_path('storage/' . $ttdPath);
                }
                if (file_exists($fullTtdPath)) {
                    $ext = strtolower(pathinfo($fullTtdPath, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        default => 'image/png',
                    };
                    $imageData = @file_get_contents($fullTtdPath);
                    if ($imageData !== false) {
                        $ttdBase64 = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    }
                }
            }
        }

        $capPath = Pengaturan::where('key', 'cap_sekolah')->value('value');
        $capBase64 = null;
        if ($capPath) {
            if (strlen($capPath) > 30) {
                try {
                    $capBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($capPath);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal mengambil base64 cap dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullCapPath = public_path('uploads/cap/' . $capPath);
                if (!file_exists($fullCapPath)) {
                    $fullCapPath = public_path('storage/' . $capPath);
                }
                if (file_exists($fullCapPath)) {
                    $ext = strtolower(pathinfo($fullCapPath, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        default => 'image/png',
                    };
                    $imageData = @file_get_contents($fullCapPath);
                    if ($imageData !== false) {
                        $capBase64 = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    }
                }
            }
        }

        // 2. QR Code
        $qrCodeData = $siswa->qr_code ?: $siswa->nisn;
        $qrImage = QrCodeGenerator::renderDataUri($qrCodeData, 300);

        // 3. Konversi foto siswa ke base64
        $fotoBase64 = null;
        if ($siswa->foto) {
            if (strlen($siswa->foto) > 30) {
                try {
                    $fotoBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($siswa->foto);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal mengambil base64 foto dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullFotoPath = storage_path('app/public/' . $siswa->foto);
                if (file_exists($fullFotoPath)) {
                    $ext = strtolower(pathinfo($fullFotoPath, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'png' => 'image/png',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'image/jpeg',
                    };
                    $imageData = @file_get_contents($fullFotoPath);
                    if ($imageData !== false) {
                        $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                    }
                }
            }
        }

        // 4. Background template
        $bgBase64 = null;
        if ($template && $template->background_path) {
            if (strlen($template->background_path) > 30) {
                try {
                    $bgBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal load background dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullBgPath = storage_path('app/public/' . $template->background_path);
                if (file_exists($fullBgPath)) {
                    $ext = strtolower(pathinfo($template->background_path, PATHINFO_EXTENSION));
                    $bgData = @file_get_contents($fullBgPath);
                    if ($bgData !== false) {
                        $bgBase64 = 'data:image/' . $ext . ';base64,' . base64_encode($bgData);
                    }
                }
            }
        }

        // Tahun akademik
        $tahunAkademik = \App\Models\TahunAkademik::where('is_aktif', true)->value('nama')
            ?? (date('Y') . '/' . (date('Y') + 1));

        $lembagaData = [
            'nama_sekolah' => $namaSekolah,
            'alamat_lembaga' => Pengaturan::where('key', 'alamat_lembaga')->value('value') ?? '',
            'email_lembaga' => Pengaturan::where('key', 'email_lembaga')->value('value') ?? '',
            'website_lembaga' => Pengaturan::where('key', 'website_lembaga')->value('value') ?? '',
            'nama_kepala_lembaga' => Pengaturan::where('key', 'nama_kepala_lembaga')->value('value') ?? '',
            'nip_kepala_lembaga' => Pengaturan::where('key', 'nip_kepala_lembaga')->value('value') ?? '',
            'kota_penerbitan' => Pengaturan::where('key', 'kota_penerbitan')->value('value') ?? '',
            'logo_base64' => $logoSekolah,
            'ttd_base64' => $ttdBase64,
            'cap_base64' => $capBase64,
        ];

        $config = $template ? $template->config : null;

        return view('siswa.kartu-pelajar-preview', compact(
            'siswa', 'template', 'config', 'qrImage', 'fotoBase64', 'bgBase64', 'tahunAkademik', 'lembagaData'
        ));
    }

    public function leaderboard()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->firstOrFail();
        
        return view('siswa.leaderboard', compact('siswa'));
    }

    public function downloadKartuPelepasan()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->firstOrFail();

        // Cek apakah siswa kelas XII
        $tingkat = $siswa->kelas ? trim($siswa->kelas->tingkat) : '';
        if (!$siswa->kelas || !in_array($tingkat, ['XII', '12'])) {
            return back()->with('error', 'Fitur ini hanya tersedia untuk siswa kelas XII.');
        }

        // Data untuk view kartu pelepasan (format gambar PNG)
        $namaSekolah  = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

        // Logo sekolah - konversi ke base64 data URI biar html2canvas gak kena CORS
        $logoPath = Pengaturan::where('key', 'logo_sekolah')->value('value');
        $logoSekolah = null;
        if ($logoPath) {
            $fullPath = public_path('storage/' . $logoPath);
            if (file_exists($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $mime = match($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                $imageData = file_get_contents($fullPath);
                if ($imageData !== false) {
                    $logoSekolah = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                }
            }
        }

        // QR Code
        $qrCodeData = $siswa->qr_code ?: $siswa->nisn;
        $qrImage = QrCodeGenerator::renderDataUri($qrCodeData, 300);

        // Tahun akademik
        $tahunAkademik = \App\Models\TahunAkademik::where('is_aktif', true)->value('nama')
            ?? (date('Y') . '/' . (date('Y') + 1));

        return view('siswa.kartu-pelepasan', compact(
            'siswa', 'namaSekolah', 'logoSekolah', 'qrImage', 'tahunAkademik'
        ));
    }
}
