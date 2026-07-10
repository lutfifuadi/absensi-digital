<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\IdCardTemplate;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Services\IdCardPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CetakKartuController extends Controller
{
    /**
     * Tampilkan halaman filter cetak kartu identitas all-in-one.
     */
    public function index()
    {
        // Kelas yang memiliki siswa aktif
        $kelasOptions = Kelas::whereHas('siswa', fn($q) => $q->where('status', 'aktif'))
            ->orderBy('nama')
            ->get();

        // Semua template ID Card
        $templates = IdCardTemplate::orderBy('name')->get();

        // Guru aktif (untuk search individu)
        $guruList = Guru::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nip']);

        // Staff TU aktif
        $staffList = StaffTataUsaha::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nip']);

        return view('admin.cetak-kartu.index', compact('kelasOptions', 'templates', 'guruList', 'staffList'));
    }

    /**
     * Proses download PDF kartu identitas.
     */
    public function download(Request $request)
    {
        $validated = $request->validate([
            'tipe'       => 'required|in:siswa,guru,staff',
            'template_id' => 'required|exists:id_card_templates,id',
            'opsi_cetak' => 'required|in:semua,kelas,individu',
            'kelas_id'   => 'required_if:opsi_cetak,kelas|nullable|exists:kelas,id',
            'entitas_id' => 'required_if:opsi_cetak,individu|nullable|integer',
        ]);

        $tipe       = $validated['tipe'];
        $opsiCetak  = $validated['opsi_cetak'];
        $templateId = $validated['template_id'];

        /** @var Collection $entities */
        $entities = collect();
        $label    = '';

        // ── AMBIL ENTITAS BERDASARKAN TIPE & OPSI CETAK ─────────────────────
        if ($tipe === 'siswa') {
            if ($opsiCetak === 'semua') {
                $entities = Siswa::where('status', 'aktif')
                    ->with('kelas', 'tahunAkademik')
                    ->get();
                $label = 'Kartu_Pelajar_Semua_Siswa';
            } elseif ($opsiCetak === 'kelas') {
                $kelasId = $validated['kelas_id'];
                $kelas   = Kelas::find($kelasId);
                $entities = Siswa::where('kelas_id', $kelasId)
                    ->where('status', 'aktif')
                    ->with('kelas', 'tahunAkademik')
                    ->get();
                $label = 'Kartu_Pelajar_Kelas_' . ($kelas ? str_replace(' ', '_', $kelas->nama) : $kelasId);
            } elseif ($opsiCetak === 'individu') {
                $entities = Siswa::where('id', $validated['entitas_id'])
                    ->where('status', 'aktif')
                    ->with('kelas', 'tahunAkademik')
                    ->get();
                $siswa = $entities->first();
                $label = 'Kartu_Pelajar_' . ($siswa ? str_replace(' ', '_', $siswa->nama_lengkap) : $validated['entitas_id']);
            }
        } elseif ($tipe === 'guru') {
            if ($opsiCetak === 'semua') {
                $entities = Guru::where('status', 'aktif')->get();
                $label = 'Kartu_Guru_Semua_Guru';
            } elseif ($opsiCetak === 'individu') {
                $entities = Guru::where('id', $validated['entitas_id'])
                    ->where('status', 'aktif')
                    ->get();
                $guru = $entities->first();
                $label = 'Kartu_Guru_' . ($guru ? str_replace(' ', '_', $guru->nama_lengkap) : $validated['entitas_id']);
            }
        } elseif ($tipe === 'staff') {
            if ($opsiCetak === 'semua') {
                $entities = StaffTataUsaha::where('status', 'aktif')->get();
                $label = 'Kartu_Staff_Semua_Staff';
            } elseif ($opsiCetak === 'individu') {
                $entities = StaffTataUsaha::where('id', $validated['entitas_id'])
                    ->where('status', 'aktif')
                    ->get();
                $staff = $entities->first();
                $label = 'Kartu_Staff_' . ($staff ? str_replace(' ', '_', $staff->nama_lengkap) : $validated['entitas_id']);
            }
        }

        // ── CEK APAKAH ADA DATA ─────────────────────────────────────────────
        if ($entities->isEmpty()) {
            return back()->with('error', 'Tidak ada data yang ditemukan untuk dicetak.');
        }

        // ── AMBIL TEMPLATE ──────────────────────────────────────────────────
        $template = IdCardTemplate::findOrFail($templateId);

        // ── RENDER PDF VIA SERVICE ──────────────────────────────────────────
        /** @var IdCardPdfService $service */
        $service = app(IdCardPdfService::class);

        return match ($tipe) {
            'siswa' => $service->renderKartuSiswa($entities, $template, $label),
            'guru'  => $service->renderKartuGuru($entities, $template, $label),
            'staff' => $service->renderKartuStaff($entities, $template, $label),
            default => back()->with('error', 'Tipe kartu tidak dikenali.'),
        };
    }
}
