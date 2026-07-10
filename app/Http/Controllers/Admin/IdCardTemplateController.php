<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdCardTemplate;
use App\Services\IdCardPdfService;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IdCardTemplateController extends Controller
{
    protected $idCardPdfService;
    protected $googleDriveService;

    public function __construct(IdCardPdfService $idCardPdfService, GoogleDriveService $googleDriveService)
    {
        $this->idCardPdfService = $idCardPdfService;
        $this->googleDriveService = $googleDriveService;
    }

    public function index()
    {
        $templates = IdCardTemplate::latest()->paginate(10);
        return view('admin.id-card-templates.index', compact('templates'));
    }

    public function create()
    {
        $defaultConfig = [
            'canvas' => ['width' => 153, 'height' => 243],
            'elements' => [
                'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'show' => true],
                'qr' => ['x' => 49, 'y' => 165, 'w' => 55, 'h' => 55, 'show' => true],
                'name' => ['x' => 0, 'y' => 20, 'size' => 10, 'color' => '#000000', 'show' => true, 'align' => 'center'],
                'nis' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                'nisn' => ['x' => 0, 'y' => 40, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                'nip' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                'class' => ['x' => 0, 'y' => 152, 'size' => 8, 'color' => '#555555', 'show' => true, 'align' => 'center'],
                'gender' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#555555', 'show' => false, 'align' => 'center'],
                'ttl' => ['x' => 0, 'y' => 228, 'size' => 6, 'color' => '#555555', 'show' => false, 'align' => 'center'],
                'masa_berlaku' => ['x' => 0, 'y' => 234, 'size' => 6, 'color' => '#555555', 'show' => false, 'align' => 'center'],
                'logo_lembaga' => ['x' => 10, 'y' => 10, 'w' => 25, 'h' => 25, 'show' => false],
                'nama_lembaga' => ['x' => 40, 'y' => 12, 'size' => 8, 'color' => '#000000', 'show' => false, 'align' => 'left'],
                'alamat_lembaga' => ['x' => 40, 'y' => 22, 'size' => 5, 'color' => '#333333', 'show' => false, 'align' => 'left'],
                'tempat_tanggal_terbit' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#333333', 'show' => false, 'align' => 'center'],
                'ttd_kepala_sekolah' => ['x' => 50, 'y' => 228, 'w' => 30, 'h' => 12, 'show' => false],
                'cap_lembaga' => ['x' => 30, 'y' => 225, 'w' => 20, 'h' => 20, 'show' => false],
                'nama_kepala_sekolah' => ['x' => 0, 'y' => 240, 'size' => 6, 'color' => '#000000', 'show' => false, 'align' => 'center'],
                'nip_kepala_sekolah' => ['x' => 0, 'y' => 246, 'size' => 5, 'color' => '#333333', 'show' => false, 'align' => 'center'],
            ]
        ];

        $lembagaData = $this->idCardPdfService->getLembagaData();
        $siswa = \App\Models\Siswa::with('kelas', 'tahunAkademik')->first();
        $guru = \App\Models\Guru::first();
        $staff = \App\Models\StaffTataUsaha::first();

        $samples = [
            'siswa' => $siswa ? [
                'name' => strtoupper($siswa->nama_lengkap),
                'nis' => $siswa->nis ?? '-',
                'nisn' => $siswa->nisn ?? '-',
                'class' => $siswa->kelas->nama ?? '-',
                'gender' => $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => ($siswa->tempat_lahir && $siswa->tanggal_lahir) ? ($siswa->tempat_lahir . ', ' . $siswa->tanggal_lahir->isoFormat('D MMMM Y')) : '-',
                'masa_berlaku' => $this->idCardPdfService->hitungMasaBerlakuSiswa($siswa, $lembagaData['jumlah_tahun_sekolah'] ?? 3),
                'photo' => $siswa->foto ? (strlen($siswa->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200' : asset('storage/' . $siswa->foto)) : null
            ] : null,
            'guru' => $guru ? [
                'name' => strtoupper($guru->nama_lengkap),
                'nip' => $guru->nip ?? '-',
                'class' => $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran),
                'gender' => $guru->jenis_kelamin === 'L' ? 'Laki-laki' : ($guru->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi guru aktif',
                'photo' => $guru->foto ? (strlen($guru->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $guru->foto . '&sz=w200' : asset('storage/' . $guru->foto)) : null
            ] : null,
            'staff' => $staff ? [
                'name' => strtoupper($staff->nama_lengkap),
                'nip' => $staff->nip ?? '-',
                'class' => $staff->jabatan ?? 'Staff Tata Usaha',
                'gender' => $staff->jenis_kelamin === 'L' ? 'Laki-laki' : ($staff->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi staff aktif',
                'photo' => $staff->foto ? (strlen($staff->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $staff->foto . '&sz=w200' : asset('storage/' . $staff->foto)) : null
            ] : null
        ];

        return view('admin.id-card-templates.form', [
            'template' => new IdCardTemplate(['config' => $defaultConfig]),
            'isEdit' => false,
            'samples' => $samples,
            'lembaga' => $lembagaData
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:siswa,guru,staff',
            'background' => 'nullable|image|max:2048',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('background')) {
            $fileId = $this->googleDriveService->uploadPhoto($request->file('background'));
            $data['background_path'] = $fileId;
        }

        // Deactivate others of same type if this is active
        if ($data['is_active']) {
            IdCardTemplate::where('type', $data['type'])->update(['is_active' => false]);
        }

        IdCardTemplate::create($data);

        return redirect()->route('admin.id-card-templates.index')->with('success', 'Template berhasil dibuat.');
    }

    public function edit(IdCardTemplate $idCardTemplate)
    {
        $lembagaData = $this->idCardPdfService->getLembagaData();
        $siswa = \App\Models\Siswa::with('kelas', 'tahunAkademik')->first();
        $guru = \App\Models\Guru::first();
        $staff = \App\Models\StaffTataUsaha::first();

        $samples = [
            'siswa' => $siswa ? [
                'name' => strtoupper($siswa->nama_lengkap),
                'nis' => $siswa->nis ?? '-',
                'nisn' => $siswa->nisn ?? '-',
                'class' => $siswa->kelas->nama ?? '-',
                'gender' => $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => ($siswa->tempat_lahir && $siswa->tanggal_lahir) ? ($siswa->tempat_lahir . ', ' . $siswa->tanggal_lahir->isoFormat('D MMMM Y')) : '-',
                'masa_berlaku' => $this->idCardPdfService->hitungMasaBerlakuSiswa($siswa, $lembagaData['jumlah_tahun_sekolah'] ?? 3),
                'photo' => $siswa->foto ? (strlen($siswa->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200' : asset('storage/' . $siswa->foto)) : null
            ] : null,
            'guru' => $guru ? [
                'name' => strtoupper($guru->nama_lengkap),
                'nip' => $guru->nip ?? '-',
                'class' => $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran),
                'gender' => $guru->jenis_kelamin === 'L' ? 'Laki-laki' : ($guru->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi guru aktif',
                'photo' => $guru->foto ? (strlen($guru->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $guru->foto . '&sz=w200' : asset('storage/' . $guru->foto)) : null
            ] : null,
            'staff' => $staff ? [
                'name' => strtoupper($staff->nama_lengkap),
                'nip' => $staff->nip ?? '-',
                'class' => $staff->jabatan ?? 'Staff Tata Usaha',
                'gender' => $staff->jenis_kelamin === 'L' ? 'Laki-laki' : ($staff->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi staff aktif',
                'photo' => $staff->foto ? (strlen($staff->foto) > 30 ? 'https://drive.google.com/thumbnail?id=' . $staff->foto . '&sz=w200' : asset('storage/' . $staff->foto)) : null
            ] : null
        ];

        return view('admin.id-card-templates.form', [
            'template' => $idCardTemplate,
            'isEdit' => true,
            'samples' => $samples,
            'lembaga' => $lembagaData
        ]);
    }

    public function update(Request $request, IdCardTemplate $idCardTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:siswa,guru,staff',
            'background' => 'nullable|image|max:2048',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        if ($request->hasFile('background')) {
            $oldFileId = (strlen($idCardTemplate->background_path) > 30) ? $idCardTemplate->background_path : null;
            $fileId = $this->googleDriveService->uploadPhoto($request->file('background'), $oldFileId);
            $data['background_path'] = $fileId;
            
            if (!$oldFileId && $idCardTemplate->background_path) {
                Storage::disk('public')->delete($idCardTemplate->background_path);
            }
        }

        if ($data['is_active']) {
            IdCardTemplate::where('type', $data['type'])
                ->where('id', '!=', $idCardTemplate->id)
                ->update(['is_active' => false]);
        }

        $idCardTemplate->update($data);

        return redirect()->route('admin.id-card-templates.index')->with('success', 'Template berhasil diperbarui.');
    }

    public function destroy(IdCardTemplate $idCardTemplate)
    {
        if ($idCardTemplate->background_path) {
            if (strlen($idCardTemplate->background_path) > 30) {
                $this->googleDriveService->deletePhoto($idCardTemplate->background_path);
            } else {
                Storage::disk('public')->delete($idCardTemplate->background_path);
            }
        }
        $idCardTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }
}
