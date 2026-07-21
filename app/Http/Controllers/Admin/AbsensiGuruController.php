<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use Illuminate\Http\Request;

class AbsensiGuruController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $tanggal = $request->query('tanggal');
        $perPage = (int) $request->query('per_page', 10);

        $query = AbsensiGuru::with('guru')->orderByDesc('tanggal');

        // Apply search filter (nama guru atau NIP)
        $query->when($search, function ($q) use ($search) {
            $q->whereHas('guru', function ($qGuru) use ($search) {
                $qGuru->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%");
            });
        });

        // Apply status filter
        $query->when($status, function ($q, $status) {
            $q->where('status', $status);
        });

        // Apply date filter
        $query->when($tanggal, function ($q, $tanggal) {
            $q->whereDate('tanggal', $tanggal);
        });

        $absensi = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.absensi-guru.table', compact('absensi'))->render();
        }

        return view('admin.absensi-guru.index', compact('absensi'));
    }

    public function create()
    {
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-guru.form', compact('guruOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        // Prevent duplicate absensi on the same date
        $duplicate = AbsensiGuru::where('guru_id', $data['guru_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['tanggal' => 'Absensi guru ini sudah tercatat untuk tanggal tersebut.']);
        }

        AbsensiGuru::create($data);

        return redirect()->route('admin.absensi-guru.index')->with('success', 'Absensi guru berhasil disimpan.');
    }

    public function edit(AbsensiGuru $absensiGuru)
    {
        $guruOptions = Guru::orderBy('nama_lengkap')->get();

        return view('admin.absensi-guru.form', compact('absensiGuru', 'guruOptions'));
    }

    public function update(Request $request, AbsensiGuru $absensiGuru)
    {
        $data = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        $absensiGuru->update($data);

        return redirect()->route('admin.absensi-guru.index')->with('success', 'Absensi guru berhasil diperbarui.');
    }

    public function destroy(AbsensiGuru $absensiGuru)
    {
        $absensiGuru->delete();

        return redirect()->route('admin.absensi-guru.index')->with('success', 'Absensi guru berhasil dihapus.');
    }

    public function scan()
    {
        return view('admin.absensi-guru.scan');
    }

    public function scanAjax(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $guru = Guru::where('qr_code', $request->qr_code)->first();

        if (!$guru) {
            return response()->json([
                'success' => false,
                'message' => 'QR code tidak dikenal. Pastikan QR code guru valid.'
            ], 404);
        }

        $tanggal        = now()->toDateString();
        $currentTime    = now()->format('H:i');
        $jamMulaiPulang = \App\Models\Pengaturan::where('key', 'jam_mulai_pulang')->value('value') ?? '14:00';

        $absensi = AbsensiGuru::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        // LOGIKA PULANG
        if ($absensi && $currentTime >= $jamMulaiPulang) {
            if ($absensi->jam_pulang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ' . $guru->nama_lengkap . ' sudah melakukan scan pulang pada jam ' . $absensi->jam_pulang
                ], 422);
            }

            $absensi->update(['jam_pulang' => $currentTime]);
            return response()->json([
                'success' => true,
                'message' => 'Berhasil! Jam pulang ' . $guru->nama_lengkap . ' tercatat.',
                'data' => [
                    'nama' => $guru->nama_lengkap,
                    'jam'  => $currentTime
                ]
            ]);
        }

        if ($absensi) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi guru ' . $guru->nama_lengkap . ' sudah dicatat hari ini.'
            ], 422);
        }

        $jamMasuk = now()->format('H:i');
        AbsensiGuru::create([
            'guru_id'    => $guru->id,
            'tanggal'    => $tanggal,
            'jam_masuk'  => $jamMasuk,
            'status'     => 'hadir',
            'metode'     => 'qr',
            'keterangan' => 'Absensi QR via Mandiri/Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil! Absensi ' . $guru->nama_lengkap . ' tercatat pada ' . $jamMasuk,
            'data' => [
                'nama' => $guru->nama_lengkap,
                'jam'  => $jamMasuk
            ]
        ]);
    }
}
