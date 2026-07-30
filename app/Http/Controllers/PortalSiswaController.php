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

        $siswa = Siswa::with(['kelas', 'tahunAkademik'])->where('user_id', $user->id)->firstOrFail();
        
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
            if (strlen($ttdPath) > 30 && !str_contains($ttdPath, '/') && !str_contains($ttdPath, '\\')) {
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
            if (strlen($capPath) > 30 && !str_contains($capPath, '/') && !str_contains($capPath, '\\')) {
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
            if (strlen($siswa->foto) > 30 && !str_contains($siswa->foto, '/') && !str_contains($siswa->foto, '\\')) {
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

        // 4. Background template (Front & Back)
        $bgBase64 = null;
        if ($template && $template->background_path) {
            if (str_starts_with($template->background_path, 'http://') || str_starts_with($template->background_path, 'https://')) {
                $bgBase64 = $template->background_path;
            } elseif (strlen($template->background_path) > 30 && !str_contains($template->background_path, '/') && !str_contains($template->background_path, '\\')) {
                try {
                    $bgBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal load background dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullBgPath = storage_path('app/public/' . $template->background_path);
                if (file_exists($fullBgPath)) {
                    $ext = strtolower(pathinfo($template->background_path, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'svg' => 'image/svg+xml',
                        'webp' => 'image/webp',
                        default => 'image/png',
                    };
                    $bgData = @file_get_contents($fullBgPath);
                    if ($bgData !== false) {
                        $bgBase64 = 'data:' . $mime . ';base64,' . base64_encode($bgData);
                    }
                }
            }
        }

        $bgBackBase64 = null;
        if ($template && $template->background_path_back) {
            if (str_starts_with($template->background_path_back, 'http://') || str_starts_with($template->background_path_back, 'https://')) {
                $bgBackBase64 = $template->background_path_back;
            } elseif (strlen($template->background_path_back) > 30 && !str_contains($template->background_path_back, '/') && !str_contains($template->background_path_back, '\\')) {
                try {
                    $bgBackBase64 = app(\App\Services\GoogleDriveService::class)->getPhotoBase64($template->background_path_back);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('PortalSiswaController: Gagal load background back dari Google Drive: ' . $e->getMessage());
                }
            } else {
                $fullBgPath = storage_path('app/public/' . $template->background_path_back);
                if (file_exists($fullBgPath)) {
                    $ext = strtolower(pathinfo($template->background_path_back, PATHINFO_EXTENSION));
                    $mime = match($ext) {
                        'jpg', 'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'svg' => 'image/svg+xml',
                        'webp' => 'image/webp',
                        default => 'image/png',
                    };
                    $bgData = @file_get_contents($fullBgPath);
                    if ($bgData !== false) {
                        $bgBackBase64 = 'data:' . $mime . ';base64,' . base64_encode($bgData);
                    }
                }
            }
        }

        // Tahun akademik
        $tahunAkademik = \App\Models\TahunAkademik::where('is_aktif', true)->value('nama')
            ?? (date('Y') . '/' . (date('Y') + 1));

        $pdfService = app(\App\Services\IdCardPdfService::class);
        $lembagaDataService = $pdfService->getLembagaData();

        $lembagaData = array_merge($lembagaDataService, [
            'nama_sekolah' => $namaSekolah,
            'alamat_lembaga' => Pengaturan::where('key', 'alamat_lembaga')->value('value') ?? '',
            'email_lembaga' => Pengaturan::where('key', 'email_lembaga')->value('value') ?? '',
            'website_lembaga' => Pengaturan::where('key', 'website_lembaga')->value('value') ?? '',
            'nama_kepala_lembaga' => Pengaturan::where('key', 'nama_kepala_lembaga')->value('value') ?? '',
            'nip_kepala_lembaga' => Pengaturan::where('key', 'nip_kepala_lembaga')->value('value') ?? '',
            'kota_penerbitan' => Pengaturan::where('key', 'kota_penerbitan')->value('value') ?? '',
            'logo_base64' => $logoSekolah ?: ($lembagaDataService['logo_base64'] ?? null),
            'ttd_base64' => $ttdBase64 ?: ($lembagaDataService['ttd_base64'] ?? null),
            'cap_base64' => $capBase64 ?: ($lembagaDataService['cap_base64'] ?? null),
        ]);

        // Attach dynamic attributes to $siswa for _elements_render compatibility
        $siswa->_foto_base64 = $fotoBase64;
        $siswa->_qr_base64   = $qrImage;
        $siswa->_masa_berlaku = $pdfService->hitungMasaBerlakuSiswa($siswa, $lembagaData['jumlah_tahun_sekolah'] ?? 3);

        $config = $template ? $template->config : null;

        return view('siswa.kartu-pelajar-preview', compact(
            'siswa', 'template', 'config', 'qrImage', 'fotoBase64', 'bgBase64', 'bgBackBase64', 'tahunAkademik', 'lembagaData'
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

    public function absensi(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $filter = $request->query('filter', 'monthly');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $semester = $request->query('semester', '');

        $query = \App\Models\AbsensiSiswa::where('siswa_id', $siswa->id);

        switch ($filter) {
            case 'weekly':
                $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d');
                $endOfWeek = now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d');
                $query->whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
                break;

            case 'semester':
                if ($semester === 'ganjil') {
                    $query->whereMonth('tanggal', '>=', 7)
                          ->whereMonth('tanggal', '<=', 12);
                } else {
                    $query->whereMonth('tanggal', '>=', 1)
                          ->whereMonth('tanggal', '<=', 6);
                }
                $query->whereYear('tanggal', $year);
                break;

            case 'yearly':
                $query->whereYear('tanggal', $year);
                break;

            case 'monthly':
            default:
                $query->whereMonth('tanggal', $month)
                      ->whereYear('tanggal', $year);
                break;
        }

        $absensi = $query->orderBy('tanggal', 'asc')->get();

        return view('siswa.absensi', compact('siswa', 'absensi', 'month', 'year', 'filter', 'semester'));
    }

    public function absensiJson(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        $filter = $request->query('filter', 'monthly');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $semester = $request->query('semester', '');

        $query = \App\Models\AbsensiSiswa::where('siswa_id', $siswa->id);

        switch ($filter) {
            case 'weekly':
                $startOfWeek = now()->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d');
                $endOfWeek = now()->endOfWeek(\Carbon\Carbon::SUNDAY)->format('Y-m-d');
                $query->whereBetween('tanggal', [$startOfWeek, $endOfWeek]);
                break;

            case 'semester':
                if ($semester === 'ganjil') {
                    $query->whereMonth('tanggal', '>=', 7)
                          ->whereMonth('tanggal', '<=', 12);
                } else {
                    $query->whereMonth('tanggal', '>=', 1)
                          ->whereMonth('tanggal', '<=', 6);
                }
                $query->whereYear('tanggal', $year);
                break;

            case 'yearly':
                $query->whereYear('tanggal', $year);
                break;

            case 'monthly':
            default:
                $query->whereMonth('tanggal', $month)
                      ->whereYear('tanggal', $year);
                break;
        }

        $absensi = $query->orderBy('tanggal', 'asc')->get();

        $dataAbsensi = $absensi->map(function ($item) {
            $statusBadge = match ($item->status) {
                'hadir' => 'bg-label-success',
                'terlambat' => 'bg-label-warning',
                'sakit' => 'bg-label-info',
                'izin' => 'bg-label-primary',
                'alpha' => 'bg-label-danger',
                default => 'bg-label-secondary',
            };

            $statusText = match ($item->status) {
                'hadir' => 'Hadir',
                'terlambat' => 'Terlambat',
                'sakit' => 'Sakit',
                'izin' => 'Izin',
                'alpha' => 'Alpha',
                default => ucfirst($item->status ?? '-'),
            };

            $metodeIcon = match ($item->metode) {
                'mandiri' => '<i class="ti tabler-gps me-1"></i> GPS Mandiri',
                'qr' => '<i class="ti tabler-qrcode me-1"></i> Scan QR',
                'manual' => '<i class="ti tabler-edit me-1"></i> Manual',
                default => '<i class="ti tabler-help-circle me-1"></i> ' . ucfirst($item->metode ?? '—'),
            };

            return [
                'tanggal' => \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y'),
                'tanggal_raw' => $item->tanggal,
                'jam_masuk' => $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-',
                'jam_pulang' => $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-',
                'status' => $item->status,
                'status_badge' => $statusBadge,
                'status_text' => $statusText,
                'metode' => $item->metode ?? '-',
                'metode_icon' => $metodeIcon,
            ];
        });

        return response()->json([
            'siswa' => [
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'kelas' => $siswa->kelas ? $siswa->kelas->nama : null,
            ],
            'absensi' => $dataAbsensi,
            'filter' => $filter,
            'month' => $month,
            'year' => $year,
            'semester' => $semester,
        ]);
    }

    /**
     * Halaman Layanan Pengaduan Portal Siswa.
     */
    public function pengaduan(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $siswaRecord = Siswa::where('user_id', $user->id)->first();
        $noHp = $siswaRecord ? $siswaRecord->no_hp : $user->no_hp;

        // Ambil daftar pengaduan berdasarkan nomor_wa siswa, urutkan terbaru dari atas
        $pengaduanList = \App\Models\Pengaduan::where('nomor_wa', $noHp)
            ->where('status_pelapor', 'siswa')
            ->orderBy('created_at', 'desc')
            ->get();

        $activePengaduan = null;
        $activeLogs = collect();

        if ($pengaduanList->isNotEmpty()) {
            // Tentukan pengaduan aktif berdasarkan parameter id, default pertama di list
            $activeId = $request->query('id');
            if ($activeId) {
                $activePengaduan = $pengaduanList->firstWhere('id', $activeId);
            }

            // Jika id parameter tidak ditemukan atau tidak dikirim, ambil yang pertama
            if (!$activePengaduan) {
                $activePengaduan = $pengaduanList->first();
            }

            if ($activePengaduan) {
                // Ambil logs dan urutkan
                $activeLogs = $activePengaduan->logs()->orderBy('created_at', 'asc')->get();
            }
        }

        $viewName = view()->exists('siswa.pengaduan') ? 'siswa.pengaduan' : 'content.siswa.pengaduan';
        return view($viewName, compact('pengaduanList', 'activePengaduan', 'activeLogs'));
    }

    /**
     * Upload & crop pas foto resmi siswa (Square 1:1, Max 250KB).
     */
    public function uploadFoto(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $siswa = Siswa::where('user_id', $user->id)->first();
        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'foto.required' => 'File foto wajib dipilih.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'foto.max' => 'Ukuran file terlalu besar (maksimal 2MB).',
        ]);

        $file = $request->file('foto');
        
        // Pengecekan ukuran file (harus di bawah 250KB / 256000 bytes)
        if ($file->getSize() > 256000) {
            return response()->json([
                'success' => false,
                'message' => 'Ukuran pas foto terlalu besar (maksimal 250 KB). Silakan kompres atau atur ulang foto.'
            ], 422);
        }

        try {
            // Tentukan nama file berdasarkan NISN (atau NIS / id jika NISN kosong)
            $nisn = !empty($siswa->nisn) ? trim($siswa->nisn) : (!empty($siswa->nis) ? trim($siswa->nis) : 'siswa_' . $siswa->id);
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = $nisn . '.' . $extension;

            // Bungkus file dengan nama file NISN agar nama di Google Drive sesuai NISN
            $customUploadedFile = new \Illuminate\Http\UploadedFile(
                $file->getRealPath(),
                $filename,
                $file->getClientMimeType(),
                null,
                true
            );

            $googleDriveService = app(\App\Services\GoogleDriveService::class);
            $newFotoValue = null;

            if ($googleDriveService->isEnabled()) {
                $oldFileId = (strlen($siswa->foto ?? '') > 30 && !str_contains($siswa->foto ?? '', '/') && !str_contains($siswa->foto ?? '', '\\')) ? $siswa->foto : null;
                $newFileId = $googleDriveService->uploadPhoto($customUploadedFile, $oldFileId);
                if ($newFileId) {
                    $newFotoValue = $newFileId;
                }
            }

            // Jika Google Drive tidak aktif / return null, simpan ke storage local dengan nama NISN
            if (!$newFotoValue) {
                if ($siswa->foto && (str_contains($siswa->foto, '/') || str_contains($siswa->foto, 'siswa/'))) {
                    $oldLocalPath = storage_path('app/public/' . $siswa->foto);
                    if (file_exists($oldLocalPath)) {
                        @unlink($oldLocalPath);
                    }
                }

                $path = $file->storeAs('siswa', $filename, 'public');
                $newFotoValue = $path;
            }

            $siswa->update([
                'foto' => $newFotoValue
            ]);

            // Bersihkan cache base64 foto (baik file ID baru maupun lama) agar foto baru langsung tampil
            if ($newFotoValue) {
                \Illuminate\Support\Facades\Cache::forget("gd_photo_base64_{$newFotoValue}");
            }
            if (isset($oldFileId) && $oldFileId) {
                \Illuminate\Support\Facades\Cache::forget("gd_photo_base64_{$oldFileId}");
            }

            // Ambil URL/Src foto baru untuk pratinjau di frontend
            $photoUrl = null;
            if (strlen($newFotoValue) > 30 && !str_contains($newFotoValue, '/') && !str_contains($newFotoValue, '\\')) {
                try {
                    $photoUrl = $googleDriveService->getPhotoBase64($newFotoValue);
                } catch (\Exception $e) {
                    $photoUrl = asset('assets/img/avatars/1.png');
                }
            } else {
                $photoUrl = asset('storage/' . $newFotoValue) . '?v=' . time();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pas foto resmi (' . $filename . ') berhasil diperbarui & disimpan!',
                'filename' => $filename,
                'photo_url' => $photoUrl
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('PortalSiswaController uploadFoto error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengunggah foto: ' . $e->getMessage()
            ], 500);
        }
    }
}
