<?php

namespace App\Services;

use App\Models\JenisPelanggaran;
use App\Models\PelanggaranFoto;
use App\Models\PelanggaranSiswa;
use App\Models\TahunAkademik;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class BKPelanggaranService
{
    protected PoinPelanggaranService $poinService;

    public function __construct(PoinPelanggaranService $poinService)
    {
        $this->poinService = $poinService;
    }

    /**
     * Store a new violation record across any class.
     */
    public function storePelanggaran(array $data, ?UploadedFile $buktiFoto = null): PelanggaranSiswa
    {
        return DB::transaction(function () use ($data, $buktiFoto) {
            $jenis = JenisPelanggaran::findOrFail($data['jenis_id']);
            $ta = TahunAkademik::where('is_aktif', true)->first() ?? TahunAkademik::latest()->first();

            if (!$ta) {
                throw new Exception('Tahun akademik aktif tidak ditemukan.');
            }

            $pelanggaran = PelanggaranSiswa::create([
                'siswa_id' => $data['siswa_id'],
                'jenis_id' => $data['jenis_id'],
                'tahun_akademik_id' => $ta->id,
                'tanggal_kejadian' => $data['tanggal_kejadian'],
                'keterangan' => $data['keterangan'] ?? null,
                'poin_saat_itu' => $jenis->poin,
                'dicatat_oleh' => auth()->id(),
                'is_diarsipkan' => false,
            ]);

            // Save optional photo proof (Google Drive if enabled, or private local storage)
            if ($buktiFoto) {
                $gdrive = app(\App\Services\GoogleDriveService::class);
                $path = null;

                if ($gdrive->isEnabled()) {
                    $path = $gdrive->uploadPhoto($buktiFoto);
                }

                if (!$path) {
                    $filename = uniqid('pelanggaran_') . '.' . $buktiFoto->getClientOriginalExtension();
                    $path = $buktiFoto->storeAs('private/pelanggaran-foto', $filename);
                }

                PelanggaranFoto::create([
                    'pelanggaran_id' => $pelanggaran->id,
                    'path_foto' => $path,
                    'nama_file_asli' => $buktiFoto->getClientOriginalName(),
                    'ukuran_byte' => $buktiFoto->getSize(),
                    'created_at' => now(),
                ]);
            }

            // Check if accumulated points trigger an automated SP
            $this->poinService->checkAndTriggerSp($data['siswa_id'], $ta->id);

            return $pelanggaran;
        });
    }

    /**
     * Get paginated violation records with filters.
     */
    public function getPelanggaranList(array $filters = [], int $perPage = 15)
    {
        $query = PelanggaranSiswa::with(['siswa.kelas', 'jenisPelanggaran.kategori', 'pencatat', 'fotos', 'tahunAkademik']);

        if (!empty($filters['siswa_id'])) {
            $query->where('siswa_id', $filters['siswa_id']);
        }

        if (!empty($filters['kelas_id'])) {
            $query->whereHas('siswa', function ($q) use ($filters) {
                $q->where('kelas_id', $filters['kelas_id']);
            });
        }

        if (!empty($filters['kategori_id'])) {
            $query->whereHas('jenisPelanggaran', function ($q) use ($filters) {
                $q->where('kategori_id', $filters['kategori_id']);
            });
        }

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_kejadian', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_kejadian', '<=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        return $query->latest('tanggal_kejadian')->paginate($perPage);
    }
}
