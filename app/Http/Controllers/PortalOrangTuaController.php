<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\IzinSakit;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Pengaduan;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalOrangTuaController extends Controller
{
    /**
     * Detail Profil Anak.
     */
    public function profilAnak($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anak = Siswa::with(['kelas.waliKelas', 'tahunAkademik'])
            ->where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        // Riwayat absensi paginated
        $absensi = AbsensiSiswa::where('siswa_id', $anak->id)
            ->orderByDesc('tanggal')
            ->paginate(10);

        // Riwayat izin/sakit
        $izinSakit = IzinSakit::where('tipe', 'siswa')
            ->where('reference_id', $anak->id)
            ->orderByDesc('created_at')
            ->get();

        // Statistik ringkasan
        $statsRaw = AbsensiSiswa::where('siswa_id', $anak->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $stats = [
            'hadir' => $statsRaw['hadir'] ?? 0,
            'sakit' => $statsRaw['sakit'] ?? 0,
            'izin' => $statsRaw['izin'] ?? 0,
            'alpha' => $statsRaw['alpha'] ?? 0,
            'terlambat' => $statsRaw['terlambat'] ?? 0,
            'total' => array_sum($statsRaw) ?: 1, // avoid div zero
        ];

        // QR Code for display
        if (empty($anak->qr_code)) {
            $fallback = $anak->nisn ?: QrCodeGenerator::generate('SISWA');
            $anak->update(['qr_code' => $fallback]);
            $anak->refresh();
        }
        $qrImage = QrCodeGenerator::renderDataUri($anak->qr_code, 150);

        return view('portal-ortu.profil-anak', compact('anak', 'absensi', 'izinSakit', 'stats', 'qrImage'));
    }

    /**
     * Riwayat Absensi Anak.
     */
    public function absensiAnak(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anak = Siswa::where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        $filter = $request->query('filter', 'monthly');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $semester = $request->query('semester', '');

        $query = AbsensiSiswa::where('siswa_id', $anak->id);

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

        return view('portal-ortu.absensi-anak', compact('anak', 'absensi', 'month', 'year', 'filter', 'semester'));
    }

    /**
     * Riwayat Absensi Anak — JSON (AJAX).
     */
    public function absensiAnakJson(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anak = Siswa::where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        $filter = $request->query('filter', 'monthly');
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);
        $semester = $request->query('semester', '');

        $query = AbsensiSiswa::where('siswa_id', $anak->id);

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
                'hadir'     => 'bg-label-success',
                'terlambat' => 'bg-label-warning',
                'sakit'     => 'bg-label-info',
                'izin'      => 'bg-label-primary',
                'alpha'     => 'bg-label-danger',
                default     => 'bg-label-secondary',
            };

            $statusText = match ($item->status) {
                'hadir'     => 'Hadir',
                'terlambat' => 'Terlambat',
                'sakit'     => 'Sakit',
                'izin'      => 'Izin',
                'alpha'     => 'Alpha',
                default     => ucfirst($item->status ?? '-'),
            };

            $metodeIcon = match ($item->metode) {
                'mandiri' => '<i class="ti tabler-gps me-1"></i> GPS Mandiri',
                'qr'      => '<i class="ti tabler-qrcode me-1"></i> Scan QR',
                'manual'  => '<i class="ti tabler-edit me-1"></i> Manual',
                default   => '<i class="ti tabler-help-circle me-1"></i> ' . ucfirst($item->metode ?? '—'),
            };

            return [
                'tanggal'      => \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d M Y'),
                'tanggal_raw'  => $item->tanggal,
                'jam_masuk'    => $item->jam_masuk  ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i')  : '-',
                'jam_pulang'   => $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-',
                'status'       => $item->status,
                'status_badge' => $statusBadge,
                'status_text'  => $statusText,
                'metode'       => $item->metode ?? '-',
                'metode_icon'  => $metodeIcon,
            ];
        });

        // ── Hitung statistik ringkasan ────────────────────────────────────────
        $countHadir     = $absensi->where('status', 'hadir')->count();
        $countTerlambat = $absensi->where('status', 'terlambat')->count();
        $countAlpha     = $absensi->where('status', 'alpha')->count();
        $countSakit     = $absensi->where('status', 'sakit')->count();
        $countIzin      = $absensi->where('status', 'izin')->count();
        $totalHadir     = $countHadir + $countTerlambat;
        $total          = $absensi->count();

        // Rata-rata jam masuk (hadir & terlambat yang punya jam_masuk)
        $jamMasukList = $absensi
            ->whereIn('status', ['hadir', 'terlambat'])
            ->filter(fn($a) => !empty($a->jam_masuk))
            ->map(fn($a) => \Carbon\Carbon::parse($a->jam_masuk)->secondsSinceMidnight())
            ->values();

        $avgJamMasuk = null;
        if ($jamMasukList->isNotEmpty()) {
            $avgSeconds  = (int) $jamMasukList->average();
            $avgJamMasuk = gmdate('H:i', $avgSeconds);
        }

        // Persentase kehadiran
        $persenHadir = $total > 0 ? round(($totalHadir / $total) * 100, 1) : 0;

        return response()->json([
            'anak' => [
                'id'           => $anak->id,
                'nama_lengkap' => $anak->nama_lengkap,
                'kelas'        => $anak->kelas ? $anak->kelas->nama : null,
            ],
            'absensi'  => $dataAbsensi,
            'filter'   => $filter,
            'month'    => $month,
            'year'     => $year,
            'semester' => $semester,
            'stats'    => [
                'total'         => $total,
                'hadir'         => $countHadir,
                'terlambat'     => $countTerlambat,
                'alpha'         => $countAlpha,
                'sakit'         => $countSakit,
                'izin'          => $countIzin,
                'total_hadir'   => $totalHadir,
                'persen_hadir'  => $persenHadir,
                'avg_jam_masuk' => $avgJamMasuk,
            ],
        ]);
    }


    /**
     * Daftar Izin/Sakit Anak.
     */
    public function izinSakit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil semua anak yang terhubung dengan ortu ini
        $anakIds = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->pluck('id');

        $izinSakit = IzinSakit::with('siswa.kelas')
            ->whereIn('reference_id', $anakIds)
            ->where('tipe', 'siswa')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal-ortu.izin-sakit-index', compact('izinSakit'));
    }

    /**
     * Form Ajukan Izin/Sakit Anak.
     */
    public function izinSakitCreate()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anakList = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->get();

        // 1. Hubungkan formulir pengajuan izin dengan data anak aktif yang sedang dipilih.
        $activeSiswaId = session('active_siswa_id');
        if (!$activeSiswaId && $anakList->isNotEmpty()) {
            $activeSiswaId = $anakList->first()->id;
            session(['active_siswa_id' => $activeSiswaId]);
        }

        return view('portal-ortu.izin-sakit-create', compact('anakList', 'activeSiswaId'));
    }

    /**
     * Simpan Pengajuan Izin/Sakit Anak.
     */
    public function izinSakitStore(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jenis' => 'required|in:sakit,izin',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // 2MB max
        ]);

        $siswa = Siswa::where('id', $request->siswa_id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        $data = [
            'tipe' => 'siswa',
            'reference_id' => $siswa->id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
        ];

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('izin-lampiran', 'public');
        }

        $izin = IzinSakit::create($data);

        // Notify all admin & super_admin users
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'admin_sekolah'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\IzinDiajukanNotification($izin));
        }

        return redirect()->route('ortu.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim dan menunggu persetujuan.');
    }

    /**
     * Batalkan/Hapus Pengajuan Izin/Sakit Anak.
     */
    public function izinSakitDestroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil semua anak yang terhubung dengan ortu ini
        $anakIds = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->pluck('id');

        $izinSakit = IzinSakit::where('id', $id)
            ->where('tipe', 'siswa')
            ->whereIn('reference_id', $anakIds)
            ->firstOrFail();

        if ($izinSakit->status !== 'pending') {
            return redirect()->route('ortu.izin-sakit.index')
                ->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses (disetujui/ditolak).');
        }

        // Jika ada lampiran, hapus file lampiran
        if ($izinSakit->lampiran) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($izinSakit->lampiran);
        }

        $izinSakit->delete();

        return redirect()->route('ortu.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dibatalkan.');
    }

    /**
     * Halaman Pengaturan Profil & Ganti Password.
     */
    public function pengaturan()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return view('portal-ortu.pengaturan', compact('user'));
    }

    /**
     * Update Data Diri Orang Tua.
     */
    public function updateProfil(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'hubungan' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20|regex:/^[0-9\+\-\s]+$/',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'alamat' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'hubungan.required' => 'Hubungan wajib diisi.',
            'no_hp.required' => 'Nomor WhatsApp/Telepon wajib diisi.',
            'no_hp.regex' => 'Format nomor WhatsApp/Telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
        ]);

        $user->update([
            'name' => $request->name,
            'hubungan' => $request->hubungan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
        ]);

        // Sinkronisasi nomor WhatsApp ke field no_hp_ortu di tabel siswa agar notifikasi WA terkirim ke nomor baru
        Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->update(['no_hp_ortu' => $request->no_hp]);

        return redirect()->route('ortu.pengaturan')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Ganti Password Orang Tua.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'password_lama' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password lama tidak cocok.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',      // Harus ada huruf
                'regex:/[0-9]/',      // Harus ada angka
            ],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.regex' => 'Password baru harus berupa kombinasi huruf dan angka.',
        ]);

        User::setPendingPlainPassword($request->password);
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('ortu.pengaturan')
            ->with([
                'success' => 'Password Anda berhasil diperbarui.',
                'password_success' => true
            ]);
    }

    /**
     * Halaman Layanan Pengaduan Portal Ortu.
     */
    public function pengaduan(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $noHp = $user->no_hp;

        // Normalisasi nomor HP ke semua kemungkinan format yang mungkin tersimpan di DB
        // Misal: no_hp = "6281322427651" → juga cari "081322427651"
        //        no_hp = "081322427651"  → juga cari "6281322427651"
        $normalizedNumbers = $this->buildPhoneVariants($noHp);

        // Ambil daftar pengaduan berdasarkan nomor_wa ortu (semua kemungkinan format)
        $pengaduanList = Pengaduan::whereIn('nomor_wa', $normalizedNumbers)
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

        $viewName = view()->exists('portal-ortu.pengaduan') ? 'portal-ortu.pengaduan' : 'content.ortu.pengaduan';
        return view($viewName, compact('pengaduanList', 'activePengaduan', 'activeLogs'));
    }

    /**
     * Build semua kemungkinan format nomor HP Indonesia dari satu input.
     * Mengembalikan array berisi format "08xxxx" dan "628xxxx".
     *
     * @param  string|null $noHp
     * @return array
     */
    private function buildPhoneVariants(?string $noHp): array
    {
        if (empty($noHp)) {
            return [''];
        }

        // Strip semua karakter non-digit
        $digits = preg_replace('/\D/', '', $noHp);

        $variants = [$noHp]; // selalu sertakan nilai asli

        if (str_starts_with($digits, '628')) {
            // 628xxx → tambahkan 08xxx
            $local = '0' . substr($digits, 2);
            $variants[] = $local;
            $variants[] = $digits; // format 628xxx
        } elseif (str_starts_with($digits, '08')) {
            // 08xxx → tambahkan 628xxx
            $intl = '62' . substr($digits, 1);
            $variants[] = $intl;
            $variants[] = $digits; // format 08xxx
        } elseif (str_starts_with($digits, '8')) {
            // 8xxx → tambahkan 08xxx dan 628xxx
            $variants[] = '0' . $digits;
            $variants[] = '62' . $digits;
        }

        return array_values(array_unique($variants));
    }
}
