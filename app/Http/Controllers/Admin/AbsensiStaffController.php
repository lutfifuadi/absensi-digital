<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiStaff;
use App\Models\StaffTataUsaha;
use Illuminate\Http\Request;

class AbsensiStaffController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $status = $request->query('status');
        $tanggal = $request->query('tanggal');
        $perPage = (int) $request->query('per_page', 10);

        $query = AbsensiStaff::with('staff')->orderByDesc('tanggal');

        if ($user->role === \App\Models\User::ROLE_STAFF_TU) {
            $staff = $user->staff;
            if (!$staff) abort(404, 'Data staff tidak ditemukan.');
            $query->where('staff_id', $staff->id);
        }

        // Apply search filter (nama staff atau NIP)
        $query->when($search, function ($q) use ($search) {
            $q->whereHas('staff', function ($qStaff) use ($search) {
                $qStaff->where('nama_lengkap', 'like', "%{$search}%")
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
            return view('admin.absensi-staff.table', compact('absensi'))->render();
        }

        return view('admin.absensi-staff.index', compact('absensi'));
    }

    public function create()
    {
        $staffOptions = StaffTataUsaha::orderBy('nama_lengkap')->get();

        return view('admin.absensi-staff.form', compact('staffOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id' => 'required|exists:staff_tata_usaha,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        // Prevent duplicate absensi on the same date
        $duplicate = AbsensiStaff::where('staff_id', $data['staff_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->exists();
        if ($duplicate) {
            return back()->withInput()->withErrors(['tanggal' => 'Absensi staff ini sudah tercatat untuk tanggal tersebut.']);
        }

        AbsensiStaff::create($data);

        return redirect()->route('admin.absensi-staff.index')->with('success', 'Absensi staff berhasil disimpan.');
    }

    public function edit(AbsensiStaff $absensiStaff)
    {
        $staffOptions = StaffTataUsaha::orderBy('nama_lengkap')->get();

        return view('admin.absensi-staff.form', compact('absensiStaff', 'staffOptions'));
    }

    public function update(Request $request, AbsensiStaff $absensiStaff)
    {
        $data = $request->validate([
            'staff_id' => 'required|exists:staff_tata_usaha,id',
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,sakit,izin,alpha,terlambat',
            'keterangan' => 'nullable|string',
            'metode' => 'required|in:manual,qr,rfid',
        ]);

        $absensiStaff->update($data);

        return redirect()->route('admin.absensi-staff.index')->with('success', 'Absensi staff berhasil diperbarui.');
    }

    public function destroy(AbsensiStaff $absensiStaff)
    {
        $absensiStaff->delete();

        return redirect()->route('admin.absensi-staff.index')->with('success', 'Absensi staff berhasil dihapus.');
    }
}
