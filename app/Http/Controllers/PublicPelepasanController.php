<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKegiatan;
use App\Models\Kegiatan;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PublicPelepasanController extends Controller
{
    /**
     * Halaman login kata kunci publik pelepasan.
     */
    public function index()
    {
        if (session('pelepasan_public_authenticated')) {
            return redirect()->route('public.pelepasan.scan');
        }

        return view('public.pelepasan.login');
    }

    /**
     * Proses verifikasi kata kunci.
     */
    public function auth(Request $request)
    {
        $request->validate([
            'kata_kunci' => 'required|string',
        ]);

        // Ambil kata kunci dari pengaturan, fallback ke 'pelepasan2026'
        $storedValue = Pengaturan::where('key', 'password_pelepasan_publik')->value('value');

        $isValid = false;
        if ($storedValue) {
            // Coba cocokkan sebagai bcrypt hash, atau plain text
            $isValid = Hash::check($request->kata_kunci, $storedValue)
                || $request->kata_kunci === $storedValue;
        } else {
            // Tidak ada di DB, gunakan default
            $isValid = $request->kata_kunci === 'pelepasan2026';
        }

        if (!$isValid) {
            return back()->withErrors(['kata_kunci' => 'Kata kunci salah. Silakan coba lagi.'])->withInput();
        }

        session(['pelepasan_public_authenticated' => true]);

        return redirect()->route('public.pelepasan.scan');
    }

    /**
     * Halaman scanner QR publik pelepasan.
     */
    public function scan()
    {
        if (!session('pelepasan_public_authenticated')) {
            return redirect()->route('public.pelepasan.index');
        }

        $taId = TahunAkademik::where('is_aktif', true)->value('id');

        $kelasAkhir = \App\Helpers\JenjangHelper::getKelasAkhir();
        $totalSiswa = Siswa::whereHas('kelas', function ($q) use ($kelasAkhir) {
                $q->where('tingkat', $kelasAkhir);
            })
            ->where('tahun_akademik_id', $taId)
            ->count();

        $kegiatan = $this->getOrCreatePelepasanKegiatan($taId);
        $totalHadir = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)->count();

        return view('public.pelepasan.scan', compact('totalSiswa', 'totalHadir', 'kegiatan'));
    }

    /**
     * AJAX — proses scan QR pelepasan publik.
     */
    public function process(Request $request)
    {
        if (!session('pelepasan_public_authenticated')) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak valid. Silakan login ulang.'], 401);
        }

        $request->validate(['qr_code' => 'required|string']);

        $taId = TahunAkademik::where('is_aktif', true)->value('id');
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
            return response()->json(['success' => false, 'message' => 'Kartu siswa tidak terdaftar!'], 404);
        }

        $kelasAkhir = \App\Helpers\JenjangHelper::getKelasAkhir();
        if (!$siswa->kelas || $siswa->kelas->tingkat !== $kelasAkhir) {
            return response()->json(['success' => false, 'message' => 'Siswa bukan peserta Kelas ' . $kelasAkhir . '!'], 403);
        }

        $already = AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $waktuAbsen = $already ? Carbon::parse($already->jam_absen) : now();
        $isNewAttendance = false;
        $waStatus = 'skip';

        if (!$already) {
            AbsensiKegiatan::create([
                'kegiatan_id' => $kegiatan->id,
                'siswa_id'    => $siswa->id,
                'jam_absen'   => $waktuAbsen,
                'status'      => 'HADIR',
            ]);
            $isNewAttendance = true;

            // WhatsApp Notification
            $phone = $siswa->no_hp_ortu ?: $siswa->no_hp;
            if (!empty($phone)) {
                $waService = new WhatsAppService();
                if ($waService->isEnabled()) {
                    $template = \App\Models\NotificationTemplate::where('type', 'pelepasan_hadir')->first();
                    $message = $template ? $template->content : '';

                    if (empty($message)) {
                        $message = "Assalamu'alaikum Wr. Wb. Yth. Orang Tua/Wali dari *{$siswa->nama_lengkap}* ({$siswa->kelas->nama}), kami menginfokan bahwa putra/putri Anda telah hadir di acara *Pelepasan Kelas {$kelasAkhir}* pada pukul " . $waktuAbsen->format('H:i') . " WIB. Terima kasih.";
                    } else {
                        $replacements = [
                            '{nama}'     => $siswa->nama_lengkap,
                            '{kelas}'    => $siswa->kelas->nama ?? '',
                            '{jam}'      => $waktuAbsen->format('H:i'),
                            '{waktu}'    => $waktuAbsen->format('H:i'),
                            '{tanggal}'  => $waktuAbsen->format('d-m-Y'),
                            '{hari}'     => $waktuAbsen->isoFormat('dddd'),
                            '{status}'   => 'HADIR',
                            '{lembaga}'  => 'MAN 1 Kota Bandung',
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
            'success'     => true,
            'is_new'      => $isNewAttendance,
            'message'     => $isNewAttendance
                ? 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.'
                : 'Siswa ' . $siswa->nama_lengkap . ' sudah melakukan absensi sebelumnya.',
            'siswa_nama'  => $siswa->nama_lengkap,
            'siswa_nisn'  => $siswa->nisn,
            'siswa_kelas' => $siswa->kelas->nama,
            'waktu'       => $waktuAbsen->format('H:i:s'),
            'foto'        => $siswa->foto ? asset('storage/' . $siswa->foto) : null,
            'wa_status'   => $waStatus,
            'total_hadir' => $totalHadir,
        ]);
    }

    /**
     * Logout dari sesi publik pelepasan.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('pelepasan_public_authenticated');
        return redirect()->route('public.pelepasan.index')
            ->with('success', 'Berhasil keluar dari sesi scan pelepasan.');
    }

    /**
     * Helper: dapatkan atau buat kegiatan pelepasan.
     */
    private function getOrCreatePelepasanKegiatan($taId)
    {
        // Prioritas 1: Cek pengaturan yang disimpan admin
        $savedKegiatanId = Pengaturan::where('key', 'pelepasan_kegiatan_id')->value('value');

        if ($savedKegiatanId) {
            $kegiatan = Kegiatan::where('id', $savedKegiatanId)
                ->where('tahun_akademik_id', $taId)
                ->first();

            if ($kegiatan) {
                return $kegiatan;
            }

            // ✅ Cleanup: setting menyimpan ID yang tidak valid (kegiatan sudah dihapus atau beda tahun akademik)
            // Hapus setting rusak agar tidak mengganggu sinkronisasi data
            Pengaturan::where('key', 'pelepasan_kegiatan_id')->delete();
            Log::warning('Setting pelepasan_kegiatan_id tidak valid (ID ' . $savedKegiatanId . '), telah dihapus dan akan dicari ulang.');
        }

        // Prioritas 2: Cari berdasarkan nama yang mengandung "Pelepasan"
        $kelasAkhir = \App\Helpers\JenjangHelper::getKelasAkhir();
        $kegiatan = Kegiatan::where('nama_kegiatan', 'like', '%Pelepasan%Kelas%' . $kelasAkhir . '%')
            ->where('tahun_akademik_id', $taId)
            ->first();

        if (!$kegiatan) {
            // Fallback: cari nama versi lama (dengan tahun dari tahun akademik)
            $ta = TahunAkademik::find($taId);
            $tahunCari = $ta ? $ta->nama : date('Y');
            $kegiatan = Kegiatan::where('nama_kegiatan', "Pelepasan Kelas {$kelasAkhir} Angkatan {$tahunCari}")
                ->where('tahun_akademik_id', $taId)
                ->first();
        }

        // Prioritas 3: Buat baru jika belum ada (fallback)
        if (!$kegiatan) {
            $ta = TahunAkademik::find($taId);
            $tahun = $ta ? $ta->nama : date('Y');
            $qrCode = 'KGT-PELEPASAN-' . date('Y');

            // Gunakan firstOrCreate dengan qr_code_kegiatan sebagai key unik
            // agar aman dari race condition (unique constraint tetap terjaga)
            $kegiatan = Kegiatan::firstOrCreate(
                ['qr_code_kegiatan' => $qrCode],
                [
                    'nama_kegiatan'       => "Pelepasan Kelas {$kelasAkhir} Angkatan {$tahun}",
                    'jenis'               => 'LAINNYA',
                    'tanggal_pelaksanaan' => date('Y-m-d'),
                    'waktu_mulai'         => '07:00:00',
                    'waktu_selesai'       => '13:00:00',
                    'lokasi'              => 'AULA UTAMA',
                    'keterangan'          => "Absensi khusus wisuda & pelepasan siswa kelas {$kelasAkhir}",
                    'is_wajib'            => true,
                    'target_peserta'      => [$kelasAkhir],
                    'tahun_akademik_id'   => $taId,
                ]
            );
        }

        return $kegiatan;
    }
}
