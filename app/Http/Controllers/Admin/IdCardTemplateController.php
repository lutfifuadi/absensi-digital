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

    private function getDefaultConfig(): array
    {
        $front = [
            'photo' => ['x' => 39, 'y' => 50, 'w' => 75, 'h' => 100, 'z_index' => 1, 'show' => true],
            'qr' => ['x' => 49, 'y' => 165, 'w' => 55, 'h' => 55, 'z_index' => 1, 'show' => true],
            'barcode' => ['x' => 39, 'y' => 195, 'w' => 75, 'h' => 25, 'z_index' => 1, 'show' => false],
            'name' => ['x' => 0, 'y' => 20, 'size' => 10, 'color' => '#000000', 'z_index' => 1, 'show' => true, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'uppercase'],
            'nis' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => true, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nisn' => ['x' => 0, 'y' => 40, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => true, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nip' => ['x' => 0, 'y' => 32, 'size' => 7, 'color' => '#555555', 'z_index' => 1, 'show' => true, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'class' => ['x' => 0, 'y' => 152, 'size' => 8, 'color' => '#555555', 'z_index' => 1, 'show' => true, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'gender' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'ttl' => ['x' => 0, 'y' => 228, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'masa_berlaku' => ['x' => 0, 'y' => 234, 'size' => 6, 'color' => '#555555', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'logo_lembaga' => ['x' => 10, 'y' => 10, 'w' => 25, 'h' => 25, 'z_index' => 1, 'show' => false],
            'logo_dinas' => ['x' => 10, 'y' => 40, 'w' => 25, 'h' => 25, 'z_index' => 1, 'show' => false],
            'nama_lembaga' => ['x' => 40, 'y' => 12, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'left', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'alamat_lembaga' => ['x' => 40, 'y' => 22, 'size' => 5, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'left', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'tempat_tanggal_terbit' => ['x' => 0, 'y' => 222, 'size' => 6, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'ttd_kepala_sekolah' => ['x' => 50, 'y' => 228, 'w' => 30, 'h' => 12, 'z_index' => 1, 'show' => false],
            'cap_lembaga' => ['x' => 30, 'y' => 225, 'w' => 20, 'h' => 20, 'z_index' => 1, 'show' => false],
            'nama_kepala_sekolah' => ['x' => 0, 'y' => 240, 'size' => 6, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => true, 'italic' => false, 'transform' => 'none'],
            'nip_kepala_sekolah' => ['x' => 0, 'y' => 246, 'size' => 5, 'color' => '#333333', 'z_index' => 1, 'show' => false, 'align' => 'center', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_1' => ['x' => 10, 'y' => 140, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 1', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_2' => ['x' => 10, 'y' => 150, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 2', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'custom_text_3' => ['x' => 10, 'y' => 160, 'size' => 8, 'color' => '#000000', 'z_index' => 1, 'show' => false, 'align' => 'center', 'content' => 'Teks Kustom 3', 'bold' => false, 'italic' => false, 'transform' => 'none'],
            'divider_1' => ['x' => 10, 'y' => 170, 'w' => 133, 'h' => 2, 'color' => '#cccccc', 'z_index' => 1, 'show' => false],
            'divider_2' => ['x' => 10, 'y' => 180, 'w' => 133, 'h' => 2, 'color' => '#cccccc', 'z_index' => 1, 'show' => false],
        ];

        $back = [];
        foreach ($front as $k => $v) {
            $back[$k] = array_merge($v, ['show' => false]);
        }

        return [
            'canvas' => ['width' => 153, 'height' => 243, 'border_radius' => 5],
            'front' => ['elements' => $front],
            'back' => ['elements' => $back],
        ];
    }

    public function create()
    {
        $defaultConfig = $this->getDefaultConfig();

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
                'ttl' => ($siswa->tempat_lahir && $siswa->tanggal_lahir) ? ($siswa->tempat_lahir . ', ' . $siswa->tanggal_lahir->locale('id')->isoFormat('D MMMM Y')) : '-',
                'masa_berlaku' => $this->idCardPdfService->hitungMasaBerlakuSiswa($siswa, $lembagaData['jumlah_tahun_sekolah'] ?? 3),
                'photo' => $siswa->foto ? ((strlen($siswa->foto) > 30 && !str_contains($siswa->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200' : asset('storage/' . $siswa->foto)) : null
            ] : null,
            'guru' => $guru ? [
                'name' => strtoupper($guru->nama_lengkap),
                'nip' => $guru->nip ?? '-',
                'class' => $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran),
                'gender' => $guru->jenis_kelamin === 'L' ? 'Laki-laki' : ($guru->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi guru aktif',
                'photo' => $guru->foto ? ((strlen($guru->foto) > 30 && !str_contains($guru->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $guru->foto . '&sz=w200' : asset('storage/' . $guru->foto)) : null
            ] : null,
            'staff' => $staff ? [
                'name' => strtoupper($staff->nama_lengkap),
                'nip' => $staff->nip ?? '-',
                'class' => $staff->jabatan ?? 'Staff Tata Usaha',
                'gender' => $staff->jenis_kelamin === 'L' ? 'Laki-laki' : ($staff->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi staff aktif',
                'photo' => $staff->foto ? ((strlen($staff->foto) > 30 && !str_contains($staff->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $staff->foto . '&sz=w200' : asset('storage/' . $staff->foto)) : null
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
            'type' => 'required|in:siswa,guru,staff,pelepasan',
            'background' => 'nullable|image|max:2048',
            'background_url' => 'nullable|url|max:255',
            'background_back' => 'nullable|image|max:2048',
            'background_back_url' => 'nullable|url|max:255',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        // Validate border_radius range 0-5
        $borderRadius = $data['config']['canvas']['border_radius'] ?? 5;
        if (!is_int($borderRadius) || $borderRadius < 0 || $borderRadius > 5) {
            $data['config']['canvas']['border_radius'] = 5;
        }

        // Front background
        if ($request->hasFile('background')) {
            $file = $request->file('background');
            $uploadedPath = null;
            if ($this->googleDriveService->isEnabled()) {
                $uploadedPath = $this->googleDriveService->uploadPhoto($file);
            }
            if (!$uploadedPath) {
                $uploadedPath = $file->store('backgrounds', 'public');
            }
            $data['background_path'] = $uploadedPath;
        } elseif ($request->filled('background_url')) {
            $data['background_path'] = $request->background_url;
        }

        // Back background
        if ($request->hasFile('background_back')) {
            $fileBack = $request->file('background_back');
            $uploadedPathBack = null;
            if ($this->googleDriveService->isEnabled()) {
                $uploadedPathBack = $this->googleDriveService->uploadPhoto($fileBack);
            }
            if (!$uploadedPathBack) {
                $uploadedPathBack = $fileBack->store('backgrounds', 'public');
            }
            $data['background_path_back'] = $uploadedPathBack;
        } elseif ($request->filled('background_back_url')) {
            $data['background_path_back'] = $request->background_back_url;
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
                'ttl' => ($siswa->tempat_lahir && $siswa->tanggal_lahir) ? ($siswa->tempat_lahir . ', ' . $siswa->tanggal_lahir->locale('id')->isoFormat('D MMMM Y')) : '-',
                'masa_berlaku' => $this->idCardPdfService->hitungMasaBerlakuSiswa($siswa, $lembagaData['jumlah_tahun_sekolah'] ?? 3),
                'photo' => $siswa->foto ? ((strlen($siswa->foto) > 30 && !str_contains($siswa->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $siswa->foto . '&sz=w200' : asset('storage/' . $siswa->foto)) : null
            ] : null,
            'guru' => $guru ? [
                'name' => strtoupper($guru->nama_lengkap),
                'nip' => $guru->nip ?? '-',
                'class' => $guru->jabatan ?? ('Guru ' . $guru->mata_pelajaran),
                'gender' => $guru->jenis_kelamin === 'L' ? 'Laki-laki' : ($guru->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi guru aktif',
                'photo' => $guru->foto ? ((strlen($guru->foto) > 30 && !str_contains($guru->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $guru->foto . '&sz=w200' : asset('storage/' . $guru->foto)) : null
            ] : null,
            'staff' => $staff ? [
                'name' => strtoupper($staff->nama_lengkap),
                'nip' => $staff->nip ?? '-',
                'class' => $staff->jabatan ?? 'Staff Tata Usaha',
                'gender' => $staff->jenis_kelamin === 'L' ? 'Laki-laki' : ($staff->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                'ttl' => '-',
                'masa_berlaku' => 'Selama menjadi staff aktif',
                'photo' => $staff->foto ? ((strlen($staff->foto) > 30 && !str_contains($staff->foto, '/')) ? 'https://drive.google.com/thumbnail?id=' . $staff->foto . '&sz=w200' : asset('storage/' . $staff->foto)) : null
            ] : null
        ];

        // Backward Compatibility & Structure Normalization
        $config = $idCardTemplate->config ?? [];
        $defaultConfig = $this->getDefaultConfig();

        // Check if legacy config (elements at root)
        if (isset($config['elements']) && !isset($config['front']['elements'])) {
            $config['front'] = ['elements' => $config['elements']];
            unset($config['elements']);
        }

        // Merge Canvas Defaults
        if (!isset($config['canvas'])) {
            $config['canvas'] = $defaultConfig['canvas'];
        } else {
            foreach ($defaultConfig['canvas'] as $prop => $val) {
                if (!isset($config['canvas'][$prop])) {
                    $config['canvas'][$prop] = $val;
                }
            }
        }

        // Merge Front Elements Defaults
        if (!isset($config['front']['elements'])) {
            $config['front']['elements'] = $defaultConfig['front']['elements'];
        } else {
            foreach ($defaultConfig['front']['elements'] as $key => $defaultEl) {
                if (!isset($config['front']['elements'][$key])) {
                    $config['front']['elements'][$key] = $defaultEl;
                } else {
                    foreach ($defaultEl as $prop => $val) {
                        if (!isset($config['front']['elements'][$key][$prop])) {
                            $config['front']['elements'][$key][$prop] = $val;
                        }
                    }
                }
            }
        }

        // Merge Back Elements Defaults
        if (!isset($config['back']['elements'])) {
            $config['back']['elements'] = $defaultConfig['back']['elements'];
        } else {
            foreach ($defaultConfig['back']['elements'] as $key => $defaultEl) {
                if (!isset($config['back']['elements'][$key])) {
                    $config['back']['elements'][$key] = $defaultEl;
                } else {
                    foreach ($defaultEl as $prop => $val) {
                        if (!isset($config['back']['elements'][$key][$prop])) {
                            $config['back']['elements'][$key][$prop] = $val;
                        }
                    }
                }
            }
        }

        $idCardTemplate->config = $config;

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
            'type' => 'required|in:siswa,guru,staff,pelepasan',
            'background' => 'nullable|image|max:2048',
            'background_url' => 'nullable|url|max:255',
            'background_back' => 'nullable|image|max:2048',
            'background_back_url' => 'nullable|url|max:255',
            'config' => 'required|json',
        ]);

        $data = $request->only(['name', 'type', 'is_active']);
        $data['config'] = json_decode($request->config, true);
        $data['is_active'] = $request->has('is_active');

        // Validate border_radius range 0-5
        $borderRadius = $data['config']['canvas']['border_radius'] ?? 5;
        if (!is_int($borderRadius) || $borderRadius < 0 || $borderRadius > 5) {
            $data['config']['canvas']['border_radius'] = 5;
        }

        // Front background update
        if ($request->hasFile('background')) {
            $file = $request->file('background');
            $old = $idCardTemplate->background_path;
            $uploadedPath = null;
            
            if ($this->googleDriveService->isEnabled()) {
                $oldFileId = ($old && strlen($old) > 30 && !str_contains($old, '/')) ? $old : null;
                $uploadedPath = $this->googleDriveService->uploadPhoto($file, $oldFileId);
            }
            if (!$uploadedPath) {
                if ($old && !str_starts_with($old, 'http')) {
                    if (strlen($old) > 30 && !str_contains($old, '/')) {
                        $this->googleDriveService->deletePhoto($old);
                    } else {
                        Storage::disk('public')->delete($old);
                    }
                }
                $uploadedPath = $file->store('backgrounds', 'public');
            }
            $data['background_path'] = $uploadedPath;
        } elseif ($request->filled('background_url')) {
            $old = $idCardTemplate->background_path;
            if ($old && !str_starts_with($old, 'http')) {
                if (strlen($old) > 30 && !str_contains($old, '/')) {
                    $this->googleDriveService->deletePhoto($old);
                } else {
                    Storage::disk('public')->delete($old);
                }
            }
            $data['background_path'] = $request->background_url;
        }

        // Back background update
        if ($request->hasFile('background_back')) {
            $fileBack = $request->file('background_back');
            $oldBack = $idCardTemplate->background_path_back;
            $uploadedPathBack = null;
            
            if ($this->googleDriveService->isEnabled()) {
                $oldFileIdBack = ($oldBack && strlen($oldBack) > 30 && !str_contains($oldBack, '/')) ? $oldBack : null;
                $uploadedPathBack = $this->googleDriveService->uploadPhoto($fileBack, $oldFileIdBack);
            }
            if (!$uploadedPathBack) {
                if ($oldBack && !str_starts_with($oldBack, 'http')) {
                    if (strlen($oldBack) > 30 && !str_contains($oldBack, '/')) {
                        $this->googleDriveService->deletePhoto($oldBack);
                    } else {
                        Storage::disk('public')->delete($oldBack);
                    }
                }
                $uploadedPathBack = $fileBack->store('backgrounds', 'public');
            }
            $data['background_path_back'] = $uploadedPathBack;
        } elseif ($request->filled('background_back_url')) {
            $oldBack = $idCardTemplate->background_path_back;
            if ($oldBack && !str_starts_with($oldBack, 'http')) {
                if (strlen($oldBack) > 30 && !str_contains($oldBack, '/')) {
                    $this->googleDriveService->deletePhoto($oldBack);
                } else {
                    Storage::disk('public')->delete($oldBack);
                }
            }
            $data['background_path_back'] = $request->background_back_url;
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
            if (strlen($idCardTemplate->background_path) > 30 && !str_contains($idCardTemplate->background_path, '/')) {
                $this->googleDriveService->deletePhoto($idCardTemplate->background_path);
            } elseif (!str_starts_with($idCardTemplate->background_path, 'http')) {
                Storage::disk('public')->delete($idCardTemplate->background_path);
            }
        }
        if ($idCardTemplate->background_path_back) {
            if (strlen($idCardTemplate->background_path_back) > 30 && !str_contains($idCardTemplate->background_path_back, '/')) {
                $this->googleDriveService->deletePhoto($idCardTemplate->background_path_back);
            } elseif (!str_starts_with($idCardTemplate->background_path_back, 'http')) {
                Storage::disk('public')->delete($idCardTemplate->background_path_back);
            }
        }

        $idCardTemplate->delete();
        return back()->with('success', 'Template berhasil dihapus.');
    }

    public function export(IdCardTemplate $idCardTemplate)
    {
        $isLocalBackground = false;
        $base64Background = null;
        $mimeType = null;

        if ($idCardTemplate->background_path && !str_starts_with($idCardTemplate->background_path, 'http')) {
            $isGoogleDrive = (strlen($idCardTemplate->background_path) > 30 && !str_contains($idCardTemplate->background_path, '/'));
            if (!$isGoogleDrive && Storage::disk('public')->exists($idCardTemplate->background_path)) {
                $isLocalBackground = true;
                $fileContent = Storage::disk('public')->get($idCardTemplate->background_path);
                $base64Background = base64_encode($fileContent);
                $mimeType = Storage::disk('public')->mimeType($idCardTemplate->background_path) ?: 'image/jpeg';
            }
        }

        $isLocalBackgroundBack = false;
        $base64BackgroundBack = null;
        $mimeTypeBack = null;

        if ($idCardTemplate->background_path_back && !str_starts_with($idCardTemplate->background_path_back, 'http')) {
            $isGoogleDriveBack = (strlen($idCardTemplate->background_path_back) > 30 && !str_contains($idCardTemplate->background_path_back, '/'));
            if (!$isGoogleDriveBack && Storage::disk('public')->exists($idCardTemplate->background_path_back)) {
                $isLocalBackgroundBack = true;
                $fileContentBack = Storage::disk('public')->get($idCardTemplate->background_path_back);
                $base64BackgroundBack = base64_encode($fileContentBack);
                $mimeTypeBack = Storage::disk('public')->mimeType($idCardTemplate->background_path_back) ?: 'image/jpeg';
            }
        }

        $payload = [
            'name' => $idCardTemplate->name,
            'type' => $idCardTemplate->type,
            'config' => $idCardTemplate->config,
            'background_path' => $idCardTemplate->background_path,
            'background_path_back' => $idCardTemplate->background_path_back,
        ];

        if ($isLocalBackground) {
            $payload['background_base64'] = $base64Background;
            $payload['background_mime'] = $mimeType;
        }
        if ($isLocalBackgroundBack) {
            $payload['background_back_base64'] = $base64BackgroundBack;
            $payload['background_back_mime'] = $mimeTypeBack;
        }

        $filename = 'id-card-template-' . \Illuminate\Support\Str::slug($idCardTemplate->name) . '-' . date('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'template_file' => 'required|file|max:5120|mimetypes:application/json,text/plain',
            'name' => 'nullable|string|max:255',
            'is_active' => 'nullable',
        ]);

        $file = $request->file('template_file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!$data || !isset($data['name']) || !isset($data['type']) || !isset($data['config'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format file JSON tidak valid atau struktur template tidak lengkap.'
                ], 422);
            }
            return back()->with('error', 'Format file JSON tidak valid atau struktur template tidak lengkap.');
        }

        if (!in_array($data['type'], ['siswa', 'guru', 'staff', 'pelepasan'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipe template tidak valid.'
                ], 422);
            }
            return back()->with('error', 'Tipe template tidak valid.');
        }

        if (!is_array($data['config'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Struktur config template tidak valid.'
                ], 422);
            }
            return back()->with('error', 'Struktur config template tidak valid.');
        }

        // Handle backward compatibility for imported legacy JSON format
        if (isset($data['config']['elements']) && !isset($data['config']['front']['elements'])) {
            $defaultConfig = $this->getDefaultConfig();
            $frontElements = $data['config']['elements'];
            $backElements = [];
            foreach ($frontElements as $k => $v) {
                $backElements[$k] = array_merge($v, ['show' => false]);
            }
            foreach ($defaultConfig['back']['elements'] as $k => $v) {
                if (!isset($backElements[$k])) {
                    $backElements[$k] = $v;
                }
            }

            $data['config'] = [
                'canvas' => $data['config']['canvas'] ?? ['width' => 153, 'height' => 243, 'border_radius' => 5],
                'front' => ['elements' => $frontElements],
                'back' => ['elements' => $backElements],
            ];
        }

        // Validate border_radius range 0-5
        $borderRadius = $data['config']['canvas']['border_radius'] ?? 5;
        if (!is_int($borderRadius) || $borderRadius < 0 || $borderRadius > 5) {
            $data['config']['canvas']['border_radius'] = 5;
        }

        $templateName = $request->filled('name') ? $request->input('name') : $data['name'];
        $isActive = $request->has('is_active') ? $request->boolean('is_active') : false;

        $backgroundPath = null;
        $backgroundPathBack = null;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Process Front Background Base64
            if (!empty($data['background_base64']) && !empty($data['background_mime'])) {
                $base64Data = $data['background_base64'];
                $mime = $data['background_mime'];
                $decodedData = base64_decode($base64Data);

                if ($decodedData === false) {
                    throw new \Exception('Gagal mendekode gambar background Front Base64.');
                }

                $extension = str_contains($mime, 'png') ? 'png' : (str_contains($mime, 'webp') ? 'webp' : 'jpg');
                $filename = 'bg_' . uniqid() . '.' . $extension;
                $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
                file_put_contents($tempPath, $decodedData);

                try {
                    if ($this->googleDriveService->isEnabled()) {
                        $fileId = $this->googleDriveService->uploadPhoto($tempPath);
                        if ($fileId) {
                            $backgroundPath = $fileId;
                        } else {
                            throw new \Exception('Gagal mengunggah background Front ke Google Drive.');
                        }
                    } else {
                        $path = Storage::disk('public')->put('backgrounds/' . $filename, $decodedData);
                        if ($path) {
                            $backgroundPath = 'backgrounds/' . $filename;
                        } else {
                            throw new \Exception('Gagal menyimpan file background Front ke local storage.');
                        }
                    }
                } finally {
                    if (file_exists($tempPath)) {
                        @unlink($tempPath);
                    }
                }
            } else {
                $backgroundPath = $data['background_path'] ?? null;
            }

            // Process Back Background Base64
            if (!empty($data['background_back_base64']) && !empty($data['background_back_mime'])) {
                $base64DataBack = $data['background_back_base64'];
                $mimeBack = $data['background_back_mime'];
                $decodedDataBack = base64_decode($base64DataBack);

                if ($decodedDataBack === false) {
                    throw new \Exception('Gagal mendekode gambar background Back Base64.');
                }

                $extensionBack = str_contains($mimeBack, 'png') ? 'png' : (str_contains($mimeBack, 'webp') ? 'webp' : 'jpg');
                $filenameBack = 'bg_back_' . uniqid() . '.' . $extensionBack;
                $tempPathBack = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filenameBack;
                file_put_contents($tempPathBack, $decodedDataBack);

                try {
                    if ($this->googleDriveService->isEnabled()) {
                        $fileIdBack = $this->googleDriveService->uploadPhoto($tempPathBack);
                        if ($fileIdBack) {
                            $backgroundPathBack = $fileIdBack;
                        } else {
                            throw new \Exception('Gagal mengunggah background Back ke Google Drive.');
                        }
                    } else {
                        $pathBack = Storage::disk('public')->put('backgrounds/' . $filenameBack, $decodedDataBack);
                        if ($pathBack) {
                            $backgroundPathBack = 'backgrounds/' . $filenameBack;
                        } else {
                            throw new \Exception('Gagal menyimpan file background Back ke local storage.');
                        }
                    }
                } finally {
                    if (file_exists($tempPathBack)) {
                        @unlink($tempPathBack);
                    }
                }
            } else {
                $backgroundPathBack = $data['background_path_back'] ?? null;
            }

            if ($isActive) {
                IdCardTemplate::where('type', $data['type'])->update(['is_active' => false]);
            }

            $newTemplate = IdCardTemplate::create([
                'name' => $templateName,
                'type' => $data['type'],
                'background_path' => $backgroundPath,
                'background_path_back' => $backgroundPathBack,
                'config' => $data['config'],
                'is_active' => $isActive,
            ]);

            \Illuminate\Support\Facades\DB::commit();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            if ($backgroundPath && !str_starts_with($backgroundPath, 'http')) {
                if (strlen($backgroundPath) > 30 && !str_contains($backgroundPath, '/')) {
                    $this->googleDriveService->deletePhoto($backgroundPath);
                } else {
                    Storage::disk('public')->delete($backgroundPath);
                }
            }
            if ($backgroundPathBack && !str_starts_with($backgroundPathBack, 'http')) {
                if (strlen($backgroundPathBack) > 30 && !str_contains($backgroundPathBack, '/')) {
                    $this->googleDriveService->deletePhoto($backgroundPathBack);
                } else {
                    Storage::disk('public')->delete($backgroundPathBack);
                }
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor template: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Gagal mengimpor template: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Template berhasil di-import.',
                'data' => $newTemplate
            ]);
        }

        return redirect()->route('admin.id-card-templates.index')->with('success', 'Template berhasil di-import.');
    }

    /**
     * Duplikat template ID Card.
     */
    public function duplicate(IdCardTemplate $idCardTemplate)
    {
        $newTemplate = $idCardTemplate->replicate();
        $newTemplate->name = $idCardTemplate->name . ' (Salinan)';
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('admin.id-card-templates.index')
            ->with('success', "Template '{$idCardTemplate->name}' berhasil diduplikasi menjadi '{$newTemplate->name}'.");
    }
}
