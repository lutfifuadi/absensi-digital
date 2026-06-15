<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Services\WhatsAppService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\IdCardTemplate;
use App\Support\QrCodeGenerator;

class PelepasanController extends Controller
{
    /**
     * Cetak kartu peserta pelepasan massal untuk kelas XII.
     */
    public function cetakKartu(Request $request)
    {
        ini_set('max_execution_time', 300); // Set to 5 minutes
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        
        // Cari template kartu tipe pelepasan yang aktif
        $template = IdCardTemplate::where('type', 'pelepasan')->active()->first();

        if (!$template) {
            return back()->with('error', 'Template ID Card tipe "Pelepasan" belum dibuat atau belum diaktifkan.');
        }

        // Ambil semua siswa kelas XII
        $siswaList = Siswa::with('kelas')
            ->whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->orderBy('kelas_id')
            ->orderBy('nama_lengkap')
            ->get();

        if ($siswaList->isEmpty()) {
            return back()->with('error', 'Tidak ada data siswa kelas XII untuk dicetak.');
        }

        $config = $template->config;
        $entities = [];

        foreach ($siswaList as $siswa) {
            $qrCodeData = $siswa->qr_code ?: $siswa->nisn;
            
            $entities[] = [
                'name'      => strtoupper($siswa->nama_lengkap),
                'id_number' => "NISN: " . ($siswa->nisn ?? '-'),
                'nis'       => "NIS: " . ($siswa->nis ?? '-'),
                'gender'    => "JK: " . ($siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'),
                'class'     => "KELAS: " . ($siswa->kelas->nama ?? '-'),
                'photo'     => $siswa->foto ? public_path('storage/' . $siswa->foto) : null,
                'qr_code'   => QrCodeGenerator::renderDataUri($qrCodeData, 150)
            ];
        }

        // Gunakan view template PDF yang sudah ada (dikustomisasi lewat config)
        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
            ->setPaper([0, 0, $config['canvas']['width'] * 0.75, $config['canvas']['height'] * 0.75], $config['canvas']['width'] > $config['canvas']['height'] ? 'landscape' : 'portrait')
            ->download("kartu-peserta-pelepasan-2026.pdf");
    }

    /**
     * Dapatkan atau buat kegiatan pelepasan berdasarkan pengaturan admin.
     * Prioritas: 1) Pengaturan tersimpan di tabel pengaturan
     *            2) Cari berdasarkan nama "Pelepasan Kelas XII"
     *            3) Fallback: buat baru dengan tanggal hari ini
     */
    private function getOrCreatePelepasanKegiatan($taId)
    {
        // Prioritas 1: Cek pengaturan yang disimpan admin
        $savedKegiatanId = \App\Models\Pengaturan::where('key', 'pelepasan_kegiatan_id')->value('value');

        if ($savedKegiatanId) {
            $kegiatan = Kegiatan::where('id', $savedKegiatanId)
                ->where('tahun_akademik_id', $taId)
                ->first();

            if ($kegiatan) {
                return $kegiatan;
            }

            // ✅ Cleanup: setting menyimpan ID yang tidak valid (kegiatan sudah dihapus atau beda tahun akademik)
            // Hapus setting rusak agar tidak mengganggu sinkronisasi data
            \App\Models\Pengaturan::where('key', 'pelepasan_kegiatan_id')->delete();
            Log::warning('Setting pelepasan_kegiatan_id tidak valid (ID ' . $savedKegiatanId . '), telah dihapus dan akan dicari ulang.');
        }

        // Prioritas 2: Cari berdasarkan nama yang mengandung "Pelepasan"
        $kegiatan = Kegiatan::where('nama_kegiatan', 'like', '%Pelepasan%Kelas%XII%')
            ->where('tahun_akademik_id', $taId)
            ->first();

        if (!$kegiatan) {
            // Fallback: cari nama versi lama
            $kegiatan = Kegiatan::where('nama_kegiatan', 'Pelepasan Kelas XII Angkatan 2026')
                ->where('tahun_akademik_id', $taId)
                ->first();
        }

        // Prioritas 3: Buat baru jika belum ada (fallback)
        if (!$kegiatan) {
            $ta = TahunAkademik::find($taId);
            $tahun = $ta ? $ta->nama : date('Y');

            $kegiatan = Kegiatan::create([
                'nama_kegiatan' => "Pelepasan Kelas XII Angkatan {$tahun}",
                'jenis' => 'LAINNYA',
                'tanggal_pelaksanaan' => date('Y-m-d'),
                'waktu_mulai' => '07:00:00',
                'waktu_selesai' => '13:00:00',
                'lokasi' => 'AULA UTAMA',
                'keterangan' => 'Absensi khusus wisuda & pelepasan siswa kelas XII',
                'qr_code_kegiatan' => 'KGT-PELEPASAN-' . date('Y'),
                'is_wajib' => true,
                'target_peserta' => ['XII'],
                'tahun_akademik_id' => $taId
            ]);
        }

        return $kegiatan;
    }

    public function settings()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatans = Kegiatan::where('tahun_akademik_id', $taId)
            ->whereIn('jenis', ['LAINNYA', 'PELEPASAN'])
            ->orderBy('tanggal_pelaksanaan', 'desc')
            ->get();

        $currentKegiatanId = \App\Models\Pengaturan::where('key', 'pelepasan_kegiatan_id')->value('value');

        return view('admin.pelepasan.settings', compact('kegiatans', 'currentKegiatanId'));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatan,id'
        ]);

        \App\Models\Pengaturan::updateOrCreate(
            ['key' => 'pelepasan_kegiatan_id'],
            ['value' => $request->kegiatan_id, 'group' => 'pelepasan']
        );

        return redirect()->route('admin.pelepasan.settings')->with('success', 'Pengaturan pelepasan berhasil disimpan.');
    }

    public function index(Request $request)
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        // Cari semua siswa kelas XII
        $siswaQuery = Siswa::with('kelas')
            ->whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId);

        $totalSiswa = $siswaQuery->count();

        // Cari siswa yang sudah hadir di kegiatan pelepasan ini
        $hadirIds = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->pluck('siswa_id')
            ->toArray();

        $totalHadir = count($hadirIds);
        $totalBelumHadir = $totalSiswa - $totalHadir;
        $persenHadir = $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0;

        // Filter / Search siswa
        $search = $request->query('search');
        $status = $request->query('status'); // hadir, belum_hadir

        $siswaList = Siswa::with(['kelas', 'absensiKegiatan' => function ($q) use ($kegiatan) {
                $q->where('kegiatan_id', $kegiatan->id);
            }])
            ->whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($status, function ($q) use ($status, $hadirIds) {
                if ($status === 'hadir') {
                    $q->whereIn('id', $hadirIds);
                } elseif ($status === 'belum_hadir') {
                    $q->whereNotIn('id', $hadirIds);
                }
            })
            ->orderBy('nama_lengkap')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.pelepasan.table', compact('siswaList'))->render();
        }

        return view('admin.pelepasan.index', compact(
            'kegiatan', 'totalSiswa', 'totalHadir', 'totalBelumHadir', 
            'persenHadir', 'siswaList', 'search', 'status'
        ));
    }

    public function liveBoard()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        $totalSiswa = Siswa::whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->count();

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->count();

        // Ambil 5 kehadiran terakhir
        $recentLogs = AbsensiKegiatan::with('siswa.kelas')
            ->where('kegiatan_id', $kegiatan->id)
            ->latest()
            ->take(5)
            ->get();

        return view('admin.pelepasan.live-board', compact('kegiatan', 'totalSiswa', 'totalHadir', 'recentLogs'));
    }

    public function mobileScan()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        $totalSiswa = Siswa::whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->count();

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->count();

        return view('admin.pelepasan.mobile-scan', compact('kegiatan', 'totalSiswa', 'totalHadir'));
    }

    public function realtimeData()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        $totalSiswa = Siswa::whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->count();

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->count();

        $recentLogs = AbsensiKegiatan::with('siswa.kelas')
            ->where('kegiatan_id', $kegiatan->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'siswa_nama' => $log->siswa->nama_lengkap,
                    'siswa_kelas' => $log->siswa->kelas->nama,
                    'waktu' => \Carbon\Carbon::parse($log->jam_absen)->format('H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'total_siswa' => $totalSiswa,
            'total_hadir' => $totalHadir,
            'total_belum_hadir' => $totalSiswa - $totalHadir,
            'persen_hadir' => $totalSiswa > 0 ? round(($totalHadir / $totalSiswa) * 100, 1) : 0,
            'recent_logs' => $recentLogs,
        ]);
    }

    public function scanStore(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        $qrCode = trim($request->qr_code);

        // Cari siswa berdasarkan QR code, NISN, atau NIS
        $siswa = Siswa::with('kelas')
            ->where('tahun_akademik_id', $taId)
            ->where(function ($q) use ($qrCode) {
                $q->where('qr_code', $qrCode)
                  ->orWhere('nisn', $qrCode)
                  ->orWhere('nis', $qrCode);
            })
            ->first();

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Kartu Siswa tidak terdaftar!'], 404);
        }

        // Pastikan kelas XII
        if (!$siswa->kelas || $siswa->kelas->tingkat !== 'XII') {
            return response()->json(['success' => false, 'message' => 'Siswa bukan peserta kelulusan Kelas XII!'], 403);
        }

        // Check duplicate scan today
        $already = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $waktuAbsen = $already ? Carbon::parse($already->jam_absen) : now();
        $isNewAttendance = false;
        $waStatus = 'skip';

        if (!$already) {
            // Catat kehadiran baru
            $already = AbsensiKegiatan::create([
                'kegiatan_id' => $kegiatan->id,
                'siswa_id' => $siswa->id,
                'jam_absen' => $waktuAbsen,
                'status' => 'HADIR',
            ]);
            $isNewAttendance = true;

            // Trigger WhatsApp Notification
            $phone = $siswa->no_hp_ortu ?: $siswa->no_hp;
            if (!empty($phone)) {
                $waService = new WhatsAppService();
                if ($waService->isEnabled()) {
                    // Ambil template notifikasi pelepasan dari database
                    $template = \App\Models\NotificationTemplate::where('type', 'pelepasan_hadir')->first();
                    $message = $template ? $template->content : '';

                    if (empty($message)) {
                        // Fallback jika template tidak ditemukan
                        $message = "Assalamu'alaikum Wr. Wb. Yth. Orang Tua/Wali dari *{$siswa->nama_lengkap}* ({$siswa->kelas->nama}), kami menginfokan bahwa putra/putri Anda telah hadir di acara *Pelepasan Kelas XII MAN 1 Kota Bandung* pada pukul " . $waktuAbsen->format('H:i') . " WIB. Terima kasih.";
                    } else {
                        // Replace variabel template
                        $replacements = [
                            '{nama}' => $siswa->nama_lengkap,
                            '{kelas}' => $siswa->kelas->nama ?? '',
                            '{jam}' => $waktuAbsen->format('H:i'),
                            '{waktu}' => $waktuAbsen->format('H:i'),
                            '{tanggal}' => $waktuAbsen->format('d-m-Y'),
                            '{hari}' => $waktuAbsen->isoFormat('dddd'),
                            '{status}' => 'HADIR',
                            '{lembaga}' => 'MAN 1 Kota Bandung',
                        ];
                        $message = str_replace(array_keys($replacements), array_values($replacements), $message);
                    }

                    $sent = $waService->sendMessage($phone, $message);
                    $waStatus = $sent ? 'sent' : 'failed';
                }
            }
        }

        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->count();

        return response()->json([
            'success' => true,
            'is_new' => $isNewAttendance,
            'message' => $isNewAttendance 
                ? 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.'
                : 'Siswa ' . $siswa->nama_lengkap . ' sudah melakukan absensi sebelumnya.',
            'siswa_nama' => $siswa->nama_lengkap,
            'siswa_nisn' => $siswa->nisn,
            'siswa_kelas' => $siswa->kelas->nama,
            'waktu' => $waktuAbsen->format('H:i:s'),
            'foto' => $siswa->foto ? asset('storage/' . $siswa->foto) : null,
            'wa_status' => $waStatus,
            'total_hadir' => $totalHadir
        ]);
    }

    public function export()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        $siswaList = Siswa::with(['kelas', 'absensiKegiatan' => function ($q) use ($kegiatan) {
                $q->where('kegiatan_id', $kegiatan->id);
            }])
            ->whereHas('kelas', function ($q) {
                $q->where('tingkat', 'XII');
            })
            ->where('tahun_akademik_id', $taId)
            ->orderBy('kelas_id')
            ->orderBy('nama_lengkap')
            ->get();

        $headers = [
            'No', 'NISN', 'NIS', 'Nama Lengkap', 'Kelas', 'Status Kehadiran', 'Waktu Masuk', 'No WA Orang Tua'
        ];

        $callback = function () use ($headers, $siswaList) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($siswaList as $idx => $s) {
                $log = $s->absensiKegiatan->first();
                fputcsv($file, [
                    $idx + 1,
                    $s->nisn,
                    $s->nis,
                    $s->nama_lengkap,
                    $s->kelas->nama ?? '-',
                    $log ? 'HADIR' : 'BELUM HADIR',
                    $log ? Carbon::parse($log->jam_absen)->format('H:i:s') : '-',
                    $s->no_hp_ortu ?? '-'
                ]);
            }
            fclose($file);
        };

        $filename = 'rekap_absensi_pelepasan_xii_' . date('Y-m-d') . '.csv';

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public function resetKehadiran()
    {
        $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);

        if ($kegiatan) {
            // Delete all absensi_kegiatan records linked to this kegiatan
            $deletedCount = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->delete();

            // Log this administrative action if ActivityLog exists
            ActivityLog::record(
                'RESET',
                'Pelepasan Kelas XII',
                'Mereset data kehadiran pelepasan kelas XII (' . $deletedCount . ' data terhapus)'
            );

            return response()->json([
                'success' => true, 
                'message' => 'Berhasil mereset ' . $deletedCount . ' data kehadiran pelepasan kelas XII.'
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => 'Kegiatan pelepasan kelas XII tidak ditemukan.'
        ], 404);
    }
}
