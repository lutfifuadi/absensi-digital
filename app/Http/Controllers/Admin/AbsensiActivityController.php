<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\ActivityAttendance;
use Illuminate\Http\Request;

class AbsensiActivityController extends Controller
{
    public function index()
    {
        $kegiatans = Kegiatan::latest()->get();
        return view('admin.kegiatan.absensi', compact('kegiatans'));
    }

    public function show(Request $request, $id)
    {
        $kegiatan = Kegiatan::findOrFail($id);
        $attendance = ActivityAttendance::with('siswa.kelas')
            ->where('activity_id', $id)
            ->get();
        return view('admin.kegiatan.absensi-show', compact('kegiatan', 'attendance'));
    }
}