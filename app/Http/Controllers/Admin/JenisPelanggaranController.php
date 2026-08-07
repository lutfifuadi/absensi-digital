<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use App\Http\Requests\StoreJenisPelanggaranRequest;
use App\Http\Requests\UpdateJenisPelanggaranRequest;
use App\Exports\JenisPelanggaranExport;
use App\Exports\JenisPelanggaranTemplateExport;
use App\Imports\JenisPelanggaranImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class JenisPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $query = JenisPelanggaran::query()->with('kategori')->withCount('pelanggaranSiswa');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        if ($request->filled('is_aktif')) {
            $query->where('is_aktif', $request->input('is_aktif'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50, 100, 15];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }

        $jenisPelanggarans = $query->orderBy('kategori_id')->orderBy('nama')->paginate($perPage)->withQueryString();
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();

        if ($request->ajax()) {
            return view('admin.pelanggaran-jenis.table', compact('jenisPelanggarans'))->render();
        }

        return view('admin.pelanggaran-jenis.index', compact('jenisPelanggarans', 'categories'));
    }

    public function create()
    {
        $jenisPelanggaran = new JenisPelanggaran();
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();
        return view('admin.pelanggaran-jenis.create', compact('jenisPelanggaran', 'categories'));
    }

    public function store(StoreJenisPelanggaranRequest $request)
    {
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');

        JenisPelanggaran::create($data);

        return redirect()->route('admin.pelanggaran-jenis.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $categories = KategoriPelanggaran::where('is_aktif', true)->orderBy('urutan')->get();
        return view('admin.pelanggaran-jenis.edit', compact('jenisPelanggaran', 'categories'));
    }

    public function update(UpdateJenisPelanggaranRequest $request, $id)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($id);
        $data = $request->validated();
        $data['is_aktif'] = $request->has('is_aktif');

        $jenisPelanggaran->update($data);

        return redirect()->route('admin.pelanggaran-jenis.index')
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy($id, Request $request)
    {
        $jenis = JenisPelanggaran::findOrFail($id);
        $nama = $jenis->nama;

        // Cek relasi: jika sudah dipakai di pelanggaran_siswa, tidak boleh hard delete, tapi soft delete / nonaktifkan saja
        // Namun, jenis pelanggaran menggunakan SoftDeletes trait.
        // Jika pelanggaranSiswa sudah mencatat jenis ini, soft delete diperbolehkan, tapi hard delete dihalangi.
        // Mari kita cek jumlah record pelanggaranSiswa.
        $terpakai = $jenis->pelanggaranSiswa()->count() > 0;

        if ($terpakai) {
            // Karena menggunakan SoftDeletes, soft delete diperbolehkan.
            // Sesuai PRD: "jenis pelanggaran yang sudah terpakai tidak boleh di-hard delete (hanya bisa dinonaktifkan / soft delete)"
            // Kita lakukan soft delete menggunakan method delete() biasa karena model menggunakan SoftDeletes.
            $jenis->delete();
            $message = "Jenis pelanggaran '{$nama}' berhasil dinonaktifkan/diarsipkan (soft delete) karena sudah pernah digunakan.";
        } else {
            // Jika tidak terpakai, bisa kita forceDelete atau delete biasa. Kita lakukan delete() biasa (akan masuk soft delete).
            $jenis->delete();
            $message = "Jenis pelanggaran '{$nama}' berhasil dihapus.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'terpakai' => $terpakai
            ]);
        }

        return redirect()->route('admin.pelanggaran-jenis.index')->with('success', $message);
    }

    public function updatePoin(Request $request, $id)
    {
        $request->validate([
            'bobot_poin' => 'required|integer|min:0|max:255',
        ]);

        $jenis = JenisPelanggaran::findOrFail($id);
        $jenis->update(['bobot_poin' => (int) $request->input('bobot_poin')]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Bobot poin untuk {$jenis->nama} berhasil diubah menjadi {$jenis->bobot_poin} poin.",
                'data' => $jenis,
            ]);
        }

        return back()->with('success', 'Bobot poin berhasil diperbarui.');
    }

    public function applyPreset(Request $request)
    {
        $preset = $request->input('preset', 'sma_smk');

        // Pastikan Kategori dasar tersedia
        (new \Database\Seeders\KategoriPelanggaranSeeder())->run();

        if ($preset === 'madrasah') {
            $katBusana = KategoriPelanggaran::firstOrCreate(
                ['nama' => 'Kedisiplinan & Kerapian'],
                ['deskripsi' => 'Atribut seragam, kelengkapan busana muslim/ah.', 'warna' => '#7367f0', 'is_aktif' => true]
            );
            $katIbadah = KategoriPelanggaran::firstOrCreate(
                ['nama' => 'Kehadiran & Akses'],
                ['deskripsi' => 'Presensi, sholat berjamaah, & KBM.', 'warna' => '#28c76f', 'is_aktif' => true]
            );
            $katAkhlak = KategoriPelanggaran::firstOrCreate(
                ['nama' => 'Etika & Perilaku'],
                ['deskripsi' => 'Akhlakul karimah, kesopanan terhadap Ustadz/Guru.', 'warna' => '#ff9f43', 'is_aktif' => true]
            );
            $katSyariat = KategoriPelanggaran::firstOrCreate(
                ['nama' => 'Pelanggaran Berat & Keamanan'],
                ['deskripsi' => 'Pelanggaran syariat, ketertiban & keamanan.', 'warna' => '#ea5455', 'is_aktif' => true]
            );

            $presetItems = [
                ['nama' => 'Tidak memakai peci / jilbab sesuai ketentuan', 'bobot_poin' => 5, 'kategori_id' => $katBusana->id],
                ['nama' => 'Terlambat mengikuti sholat berjamaah', 'bobot_poin' => 10, 'kategori_id' => $katIbadah->id],
                ['nama' => 'Sengaja meninggalkan sholat berjamaah', 'bobot_poin' => 20, 'kategori_id' => $katIbadah->id],
                ['nama' => 'Berkata kotor / tidak sopan kepada Guru/Ustadz', 'bobot_poin' => 15, 'kategori_id' => $katAkhlak->id],
                ['nama' => 'Merokok / vaping di lingkungan madrasah', 'bobot_poin' => 35, 'kategori_id' => $katSyariat->id],
            ];

            foreach ($presetItems as $pItem) {
                JenisPelanggaran::updateOrCreate(
                    ['nama' => $pItem['nama']],
                    $pItem
                );
            }
        } else {
            // Preset Standard (SMA/SMK & SMP/MTs)
            (new \Database\Seeders\JenisPelanggaranSeeder())->run();
        }

        $msg = 'Preset Tata Tertib berhasil diterapkan!';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $kategoriId = $request->input('kategori_id');
        $isAktif = $request->input('is_aktif');
        $fileName = 'jenis_pelanggaran_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new JenisPelanggaranExport($search, $kategoriId, $isAktif), $fileName);
    }

    public function downloadTemplate()
    {
        $fileName = 'template_import_jenis_pelanggaran.xlsx';
        return Excel::download(new JenisPelanggaranTemplateExport(), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'import_file.required' => 'File Excel wajib dipilih.',
            'import_file.mimes' => 'File harus berformat xlsx, xls, atau csv.',
            'import_file.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $import = new JenisPelanggaranImport();
            Excel::import($import, $request->file('import_file'));
            $res = $import->getImportResult();

            $msg = "Berhasil mengimpor {$res['imported']} jenis pelanggaran baru";
            if ($res['updated'] > 0) {
                $msg .= " dan memperbarui {$res['updated']} data existing.";
            } else {
                $msg .= ".";
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'data' => $res
                ]);
            }

            return redirect()->route('admin.pelanggaran-jenis.index')->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Import Jenis Pelanggaran Gagal: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengimpor file: ' . $e->getMessage()
                ], 422);
            }

            return redirect()->route('admin.pelanggaran-jenis.index')->with('error', 'Gagal mengimpor file: ' . $e->getMessage());
        }
    }
}
