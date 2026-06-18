<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;

class AbsensiSiswaController extends Controller
{
    public function index()
    {
        $absensi = AbsensiSiswa::with(['siswa:id,nama_lengkap,kelas_id', 'kelas:id,nama', 'guru:id,nama_lengkap'])
            ->orderByDesc('tanggal')
            ->paginate(50);

        return view('admin.absensi-siswa.index', compact('absensi'));
    }

    public function create()
    {
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        $kelasOptions = Kelas::orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-siswa.form', compact('siswaOptions', 'kelasOptions', 'guruOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'guru_id' => 'nullable|exists:guru,id',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        // Prevent duplicate absensi on the same date
        $duplicate = AbsensiSiswa::where('siswa_id', $data['siswa_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['tanggal' => 'Absensi siswa ini sudah tercatat untuk tanggal tersebut.']);
        }

        AbsensiSiswa::create($data);

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil disimpan.');
    }

    public function edit(AbsensiSiswa $absensiSiswa)
    {
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        $kelasOptions = Kelas::orderBy('nama')->get();
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-siswa.form', compact('absensiSiswa', 'siswaOptions', 'kelasOptions', 'guruOptions'));
    }

    public function update(Request $request, AbsensiSiswa $absensiSiswa)
    {
        $data = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'guru_id' => 'nullable|exists:guru,id',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        $absensiSiswa->update($data);

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil diperbarui.');
    }

    public function destroy(AbsensiSiswa $absensiSiswa)
    {
        $absensiSiswa->delete();

        return redirect()->route('admin.absensi-siswa.index')->with('success', 'Absensi siswa berhasil dihapus.');
    }

    public function scan()
    {
        return view('admin.absensi-siswa.scan');
    }

    public function scanStore(Request $request)
    {
        $data = $request->validate([
            'qr_code' => 'required|string',
            'tanggal' => 'nullable|date',
            'status'  => 'nullable|in:hadir,sakit,izin,alpha,terlambat',
        ]);

        $tanggal     = $data['tanggal'] ?? now()->toDateString();
        $currentTime = now()->format('H:i');

        $settings = \App\Models\Pengaturan::whereIn('key', [
            'jam_masuk', 'jam_batas_masuk', 'jam_pulang', 'jam_mulai_pulang', 'jam_akhir_pulang', 'toleransi_terlambat'
        ])->pluck('value', 'key');

        $jamMasuk       = $settings['jam_masuk']       ?? '07:00';
        $jamBatasMasuk  = $settings['jam_batas_masuk'] ?? '08:00';
        $jamMulaiPulang = $settings['jam_mulai_pulang'] ?? '14:00';
        $jamAkhirPulang = $settings['jam_akhir_pulang'] ?? '17:00';
        $toleransi      = (int)($settings['toleransi_terlambat'] ?? 15);

        $siswa = Siswa::where('qr_code', $data['qr_code'])->first();

        if (! $siswa) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'QR code tidak dikenal. Pastikan QR code siswa valid.');
        }

        // Gunakan jam khusus kelas jika diatur
        if ($siswa->kelas_id) {
            $kelas = Kelas::find($siswa->kelas_id);
            if ($kelas && $kelas->kustomisasi_jam) {
                if ($kelas->jam_masuk) {
                    $jamMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->format('H:i');
                    $jamBatasMasuk = \Carbon\Carbon::parse($kelas->jam_masuk)->addMinutes($toleransi)->format('H:i');
                }
                if ($kelas->jam_pulang) {
                    $jamMulaiPulang = \Carbon\Carbon::parse($kelas->jam_pulang)->format('H:i');
                }
            }
        }

        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // LOGIKA PULANG
        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($currentTime > $jamAkhirPulang) {
                return redirect()->route('admin.absensi-siswa.scan')
                    ->with('error', 'Sesi scan pulang sudah berakhir (Batas: ' . $jamAkhirPulang . ').');
            }

            if ($absensi->jam_pulang) {
                return redirect()->route('admin.absensi-siswa.scan')
                    ->with('error', 'Siswa ' . $siswa->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang);
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('success', 'Jam pulang ' . $siswa->nama_lengkap . ' berhasil dicatat.');
        }

        if ($absensi) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'Absensi siswa ' . $siswa->nama_lengkap . ' sudah dicatat untuk hari ini.');
        }

        // Cek Batas Masuk
        if ($currentTime > $jamBatasMasuk) {
            return redirect()->route('admin.absensi-siswa.scan')
                ->with('error', 'Sesi scan masuk sudah berakhir (Batas: ' . $jamBatasMasuk . ').');
        }

        // Hitung status jika tidak dipaksa dari form
        $status = $data['status'] ?? 'hadir';
        if ($status === 'hadir') {
            $limitHadir = \Carbon\Carbon::createFromFormat('H:i', $jamMasuk)->addMinutes($toleransi)->format('H:i');
            if ($currentTime > $limitHadir) {
                $status = 'terlambat';
            }
        }

        AbsensiSiswa::create([
            'siswa_id'   => $siswa->id,
            'kelas_id'   => $siswa->kelas_id,
            'tanggal'    => $tanggal,
            'jam_masuk'  => $currentTime,
            'status'     => $status,
            'keterangan' => 'Absensi otomatis via QR scanner',
            'guru_id'    => null,
            'metode'     => 'qr',
        ]);

        return redirect()->route('admin.absensi-siswa.scan')
            ->with('success', 'Absensi ' . $siswa->nama_lengkap . ' berhasil dicatat.' . ($status === 'terlambat' ? ' (TERLAMBAT)' : ''));
    }

    /**
     * Halaman input absensi cepat (semua siswa satu kelas).
     */
    public function bulkForm(Request $request)
    {
        $kelasOptions = Kelas::orderBy('nama')->get();
        $selectedKelasId = $request->query('kelas_id');
        $siswa = collect();
        if ($selectedKelasId) {
            $siswa = Siswa::with(['absensi' => function($q) use ($request) {
                    $q->whereDate('tanggal', $request->query('tanggal', now()->toDateString()));
                }])
                ->where('kelas_id', $selectedKelasId)
                ->where('status', 'aktif')
                ->orderBy('nama_lengkap')
                ->limit(200)
                ->get();
        }

        return view('admin.absensi-siswa.bulk', compact('kelasOptions', 'selectedKelasId', 'siswa'));
    }

    /**
     * Simpan absensi cepat per kelas.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal'  => 'required|date',
            'absensi'  => 'required|array',
            'absensi.*.siswa_id'  => 'required|exists:siswa,id',
            'absensi.*.status'    => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'absensi.*.keterangan' => 'nullable|string',
        ]);

        $tanggal = $request->tanggal;
        $kelasId = $request->kelas_id;
        $count = 0;

        foreach ($request->absensi as $item) {
            // Jika status tidak dipilih (fallback ke hadir)
            $status = $item['status'] ?? 'hadir';
            
            AbsensiSiswa::updateOrCreate(
                [
                    'siswa_id' => $item['siswa_id'],
                    'tanggal'  => $tanggal,
                ],
                [
                    'kelas_id'   => $kelasId,
                    'status'     => $status,
                    'keterangan' => $item['keterangan'] ?? null,
                    'metode'     => 'manual',
                    'jam_masuk'  => ($status === 'hadir' || $status === 'terlambat') ? now()->format('H:i') : null,
                ]
            );
            $count++;
        }

        return redirect()->route('admin.absensi-siswa.index')
            ->with('success', "Berhasil menyimpan $count data absensi kelas.");
    }
}
