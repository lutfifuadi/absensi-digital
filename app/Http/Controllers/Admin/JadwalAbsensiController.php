<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Controller untuk mengelola jadwal absensi per kelas per hari.
 *
 * PRD-016: Admin dapat mengatur jam masuk, pulang, dan status libur
 * untuk setiap kelas pada hari Senin hingga Minggu.
 */
class JadwalAbsensiController extends Controller
{
    /**
     * Daftar hari yang valid.
     */
    private const HARI_VALID = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

    /**
     * Tampilkan daftar kelas dengan status jadwal absensi.
     *
     * - Query kelas dengan eager loading jadwalAbsensi, jurusan, tahunAkademik, waliKelas
     * - Support pagination (10, 25, 50, 100)
     * - Support search by nama kelas
     * - Support filter by tingkat (X, XI, XII)
     */
    public function index(Request $request)
    {
        $search  = $request->query('search');
        $tingkat = $request->query('tingkat');
        $perPage = (int) $request->query('per_page', 10);

        $tahunAjaranId = session('tahun_ajaran_id', session('tahun_akademik_id'));

        $kelas = Kelas::with(['jadwalAbsensi', 'jurusan', 'tahunAkademik', 'waliKelas'])
            ->where('tahun_akademik_id', $tahunAjaranId)
            ->when($tingkat, function ($query, $tingkat) {
                return $query->where('tingkat', $tingkat);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhereHas('jurusan', function ($qj) use ($search) {
                          $qj->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.jadwal-absensi.table', compact('kelas'))->render();
        }

        $tingkatOptions = \App\Helpers\JenjangHelper::getTingkatOptions();

        // Ambil semua kelas untuk dropdown bulk apply (tanpa pagination)
        $allKelas = Kelas::with(['jadwalAbsensi', 'jurusan'])
            ->where('tahun_akademik_id', $tahunAjaranId)
            ->orderBy('nama')
            ->get();

        return view('admin.jadwal-absensi.index', compact('kelas', 'tingkat', 'tingkatOptions', 'allKelas'));
    }

    /**
     * Tampilkan jadwal absensi 7 hari untuk kelas tertentu.
     *
     * Format data sebagai array of objects dengan key hari.
     * Mendukung response view atau JSON (untuk AJAX).
     */
    public function show(Kelas $kelas)
    {
        $kelas->load('jadwalAbsensi', 'jurusan');

        // Format jadwal sebagai array dengan key hari
        $jadwalFormatted = [];
        foreach (self::HARI_VALID as $hari) {
            $jadwal = $kelas->jadwalAbsensi->firstWhere('hari', $hari);
            $jadwalFormatted[$hari] = [
                'id'                 => $jadwal?->id,
                'hari'               => $hari,
                'jam_mulai_absensi'  => $jadwal?->jam_mulai_absensi ? $jadwal->jam_mulai_absensi->format('H:i') : null,
                'jam_masuk'          => $jadwal?->jam_masuk ? $jadwal->jam_masuk->format('H:i') : null,
                'jam_pulang'         => $jadwal?->jam_pulang ? $jadwal->jam_pulang->format('H:i') : null,
                'jam_akhir_pulang'   => $jadwal?->jam_akhir_pulang ? $jadwal->jam_akhir_pulang->format('H:i') : null,
                'is_libur'           => $jadwal?->is_libur ?? ($hari === 'sabtu' || $hari === 'minggu'),
            ];
        }

        // Request AJAX → return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'kelas'   => [
                        'id'   => $kelas->id,
                        'nama' => $kelas->nama,
                    ],
                    'jadwal'  => $jadwalFormatted,
                ],
            ]);
        }

        return view('admin.jadwal-absensi.show', compact('kelas', 'jadwalFormatted'));
    }

    /**
     * Simpan jadwal absensi untuk 1 hari pada 1 kelas.
     *
     * Menggunakan updateOrCreate dengan unique key ['kelas_id', 'hari'].
     * Return JSON response untuk AJAX.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id'            => 'required|exists:kelas,id',
            'hari'                => ['required', Rule::in(self::HARI_VALID)],
            'jam_mulai_absensi'   => 'nullable|date_format:H:i',
            'jam_masuk'           => 'nullable|date_format:H:i',
            'jam_pulang'          => 'nullable|date_format:H:i',
            'jam_akhir_pulang'    => 'nullable|date_format:H:i',
            'is_libur'            => 'nullable|boolean',
        ]);

        // Validasi business rules: urutan waktu
        $validationError = $this->validateTimeSequence($data);
        if ($validationError) {
            return response()->json([
                'success' => false,
                'message' => $validationError,
            ], 422);
        }

        $jadwal = KelasJadwalAbsensi::updateOrCreate(
            [
                'kelas_id' => $data['kelas_id'],
                'hari'     => $data['hari'],
            ],
            [
                'jam_mulai_absensi' => $data['jam_mulai_absensi'] ?? null,
                'jam_masuk'         => $data['jam_masuk'] ?? null,
                'jam_pulang'        => $data['jam_pulang'] ?? null,
                'jam_akhir_pulang'  => $data['jam_akhir_pulang'] ?? null,
                'is_libur'          => $data['is_libur'] ?? false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal hari ' . ucfirst($data['hari']) . ' berhasil disimpan.',
            'data'    => $jadwal,
        ]);
    }

    /**
     * Simpan semua hari sekaligus untuk 1 kelas.
     *
     * Loop dan updateOrCreate untuk setiap hari.
     * Return JSON response untuk AJAX.
     */
    public function storeAll(Request $request)
    {
        $data = $request->validate([
            'kelas_id'              => 'required|exists:kelas,id',
            'jadwal'                => 'required|array|max:7',
            'jadwal.*.hari'         => ['required', Rule::in(self::HARI_VALID)],
            'jadwal.*.jam_mulai_absensi' => 'nullable|date_format:H:i',
            'jadwal.*.jam_masuk'         => 'nullable|date_format:H:i',
            'jadwal.*.jam_pulang'        => 'nullable|date_format:H:i',
            'jadwal.*.jam_akhir_pulang'  => 'nullable|date_format:H:i',
            'jadwal.*.is_libur'          => 'nullable|boolean',
        ]);

        // Validasi business rules untuk setiap hari
        foreach ($data['jadwal'] as $index => $jadwal) {
            $validationError = $this->validateTimeSequence($jadwal, "jadwal.{$index}.");
            if ($validationError) {
                return response()->json([
                    'success' => false,
                    'message' => $validationError,
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            foreach ($data['jadwal'] as $jadwal) {
                KelasJadwalAbsensi::updateOrCreate(
                    [
                        'kelas_id' => $data['kelas_id'],
                        'hari'     => $jadwal['hari'],
                    ],
                    [
                        'jam_mulai_absensi' => $jadwal['jam_mulai_absensi'] ?? null,
                        'jam_masuk'         => $jadwal['jam_masuk'] ?? null,
                        'jam_pulang'        => $jadwal['jam_pulang'] ?? null,
                        'jam_akhir_pulang'  => $jadwal['jam_akhir_pulang'] ?? null,
                        'is_libur'          => $jadwal['is_libur'] ?? false,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua jadwal berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jadwal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Terapkan jadwal dari 1 kelas sumber ke beberapa kelas tujuan.
     *
     * Copy jadwal dari source_kelas_id ke setiap target_kelas_ids.
     * Return JSON response.
     */
    public function bulkApply(Request $request)
    {
        $data = $request->validate([
            'source_kelas_id'   => 'required|exists:kelas,id',
            'target_kelas_ids'  => 'required|array|min:1',
            'target_kelas_ids.*' => 'required|exists:kelas,id',
        ]);

        // Load jadwal dari kelas sumber
        $sourceJadwal = KelasJadwalAbsensi::where('kelas_id', $data['source_kelas_id'])
            ->get();

        if ($sourceJadwal->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Kelas sumber belum memiliki jadwal yang bisa disalin.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $totalCopied = 0;

            foreach ($data['target_kelas_ids'] as $targetKelasId) {
                // Skip jika target sama dengan source
                if ($targetKelasId == $data['source_kelas_id']) {
                    continue;
                }

                foreach ($sourceJadwal as $jadwal) {
                    KelasJadwalAbsensi::updateOrCreate(
                        [
                            'kelas_id' => $targetKelasId,
                            'hari'     => $jadwal->hari,
                        ],
                        [
                            'jam_mulai_absensi' => $jadwal->jam_mulai_absensi,
                            'jam_masuk'         => $jadwal->jam_masuk,
                            'jam_pulang'        => $jadwal->jam_pulang,
                            'jam_akhir_pulang'  => $jadwal->jam_akhir_pulang,
                            'is_libur'          => $jadwal->is_libur,
                        ]
                    );
                    $totalCopied++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyalin jadwal ke " . count($data['target_kelas_ids']) . " kelas tujuan.",
                'data'    => [
                    'total_copied' => $totalCopied,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyalin jadwal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus semua jadwal absensi untuk 1 kelas.
     *
     * Delete semua record di kelas_jadwal_absensi where kelas_id = $kelas->id.
     * Return JSON response.
     */
    public function destroy(Kelas $kelas)
    {
        DB::beginTransaction();
        try {
            $deletedCount = KelasJadwalAbsensi::where('kelas_id', $kelas->id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} jadwal dari kelas {$kelas->nama}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus jadwal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validasi urutan waktu (business rules BR-03 & BR-04).
     *
     * - jam_mulai_absensi harus <= jam_masuk
     * - jam_pulang harus <= jam_akhir_pulang
     *
     * @param  array  $data
     * @param  string $prefix  Prefix untuk pesan error (untuk validasi array)
     * @return string|null     Pesan error jika validasi gagal, null jika berhasil
     */
    private function validateTimeSequence(array $data, string $prefix = ''): ?string
    {
        $jamMulaiAbsensi = $data['jam_mulai_absensi'] ?? null;
        $jamMasuk        = $data['jam_masuk'] ?? null;
        $jamPulang       = $data['jam_pulang'] ?? null;
        $jamAkhirPulang  = $data['jam_akhir_pulang'] ?? null;
        $hari            = $data['hari'] ?? '';

        $hariLabel = $hari ? ' (' . ucfirst($hari) . ')' : '';

        // BR-03: jam_mulai_absensi <= jam_masuk
        if ($jamMulaiAbsensi && $jamMasuk && $jamMulaiAbsensi > $jamMasuk) {
            return "Jam Mulai Absensi{$hariLabel} harus lebih awal atau sama dengan Jam Masuk.";
        }

        // BR-04: jam_pulang <= jam_akhir_pulang
        if ($jamPulang && $jamAkhirPulang && $jamPulang > $jamAkhirPulang) {
            return "Jam Pulang{$hariLabel} harus lebih awal atau sama dengan Jam Akhir Pulang.";
        }

        return null;
    }
}
