<?php

namespace App\Services;

use App\Models\AbsensiPerJamSesi;
use App\Models\AbsensiSiswaPerJadwal;
use App\Models\Holiday;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use App\Models\Mapel;
use App\Models\MonitoringKehadiranGuru;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AbsensiPerJamService — business logic absensi siswa per jam pelajaran (PRD-006, P0).
 *
 * Catatan penting (temuan kritis):
 * - `jadwal_pelajaran.hari` menyimpan "Ahad" untuk hari ke-7, sedangkan
 *   `MonitoringService::getHariIndo()` mengembalikan "Minggu". Semua deteksi
 *   jadwal WAJIB lewat `getHariIndoAbsensiPerJam()` yang menormalkan "Minggu" → "Ahad".
 * - `kelas_jadwal_absensi.hari` menyimpan lowercase ("minggu"), jadi method
 *   `validateHariLibur()` menormalkan sendiri ke format tersebut.
 */
class AbsensiPerJamService
{
    public const STATUS_LIST = ['hadir', 'terlambat', 'sakit', 'izin', 'alpha', 'dispen', 'bolos'];

    public const STATUS_KODE = [
        'hadir'     => 'H',
        'terlambat' => 'T',
        'sakit'     => 'S',
        'izin'      => 'I',
        'alpha'     => 'A',
        'dispen'    => 'D',
        'bolos'     => 'B',
    ];

    /**
     * Nama hari Bahasa Indonesia yang NORMALISASI untuk deteksi jadwal pelajaran.
     *
     * Mengembalikan "Senin".."Jumat","Sabtu","Ahad" — konsisten dengan
     * `jadwal_pelajaran.hari` (bukan "Minggu").
     */
    public function getHariIndoAbsensiPerJam(?string $tanggal = null): string
    {
        $hari = app(MonitoringService::class)->getHariIndo($tanggal);

        return str_replace('Minggu', 'Ahad', $hari);
    }

    /**
     * Daftar jadwal hari ini milik seorang guru (guru pengampu ATAU guru pengganti).
     *
     * @param int|null $guruId Profil guru (id tabel guru) dari user login.
     * @return Collection<int, JadwalPelajaran> — diurutkan jam_mulai, tiap item
     *         dilengkapi atribut: sedang_berlangsung, berikutnya, selesai,
     *         is_pengganti, sudah_diisi.
     */
    public function getJadwalHariIniUntukGuru(int $guruId, ?string $tanggal = null): Collection
    {
        $tanggal = $tanggal ?: now()->toDateString();
        $hari = $this->getHariIndoAbsensiPerJam($tanggal);

        $jadwal = JadwalPelajaran::with(['kelas', 'guru'])
            ->where('hari', $hari)
            ->where(function ($q) use ($guruId, $tanggal) {
                // Jadwal milik guru pengampu ATAU sesi penggantian (monitoring_kehadiran_guru)
                $q->where('guru_id', $guruId)
                  ->orWhereHas('monitoring', function ($mq) use ($guruId, $tanggal) {
                      $mq->where('tanggal', $tanggal)
                         ->where('status', 'tidak_hadir')
                         ->where('ada_pengganti', true)
                         ->where('guru_pengganti_id', $guruId);
                  });
            })
            ->orderBy('jam_mulai')
            ->get();

        return $this->withStatusFlags($jadwal, $tanggal, $guruId);
    }

    /**
     * Daftar SEMUA jadwal untuk tanggal tertentu (untuk Piket / Admin).
     *
     * @return Collection<int, JadwalPelajaran> — diurutkan jam_mulai, tiap item
     *         dilengkapi atribut: sedang_berlangsung, berikutnya, selesai,
     *         is_pengganti, sudah_diisi.
     */
    public function getJadwalHariIniUntukPiket(?string $tanggal = null): Collection
    {
        $tanggal = $tanggal ?: now()->toDateString();
        $hari = $this->getHariIndoAbsensiPerJam($tanggal);

        $jadwal = JadwalPelajaran::with(['kelas', 'guru'])
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        return $this->withStatusFlags($jadwal, $tanggal);
    }

    /**
     * Roster siswa aktif (status = aktif) di kelas milik jadwal.
     *
     * @return Collection<int, Siswa>
     */
    public function getRosterSiswa(int $jadwalId, ?int $tahunAkademikId = null): Collection
    {
        $jadwal = JadwalPelajaran::findOrFail($jadwalId);

        $query = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->where('status', 'aktif');

        if ($tahunAkademikId) {
            $query->where('tahun_akademik_id', $tahunAkademikId);
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    /**
     * Data sesi absensi untuk (jadwal + tanggal): catatan existing keyed by siswa_id
     * + info header sesi (bisa null bila belum diisi).
     */
    public function getSesiData(int $jadwalId, string $tanggal): array
    {
        $records = AbsensiSiswaPerJadwal::where('jadwal_pelajaran_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->get()
            ->keyBy('siswa_id');

        $sesi = AbsensiPerJamSesi::where('jadwal_pelajaran_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->first();

        return [
            'records'       => $records,
            'sesi'          => $sesi,
            'terisi'        => $records->isNotEmpty(),
            'jumlah_terisi' => $records->count(),
        ];
    }

    /**
     * Simpan absensi massal (bulk upsert dalam 1 transaksi DB — BR-02).
     *
     * Validasi BR: BR-03 (window waktu), BR-04 (dilarang tanggal masa depan),
     * BR-05 (hari libur), BR-06 (kecocokan kelas), BR-07 (lama_terlambat),
     * BR-10 (dicatat_oleh).
     *
     * @param array<int, array{siswa_id:int, status:string, lama_terlambat?:int|null, keterangan?:string|null}> $rows
     * @return array{berhasil:int, gagal:int, duplikat:int}
     *
     * @throws \Exception Bila validasi kritis gagal (libur, tanggal masa depan, window, tanpa baris valid).
     */
    public function simpanAbsensi(int $jadwalId, string $tanggal, array $rows, int $dicatatOleh, string $metode = 'manual', ?string $materi = null, ?string $catatan = null): array
    {
        $jadwal = JadwalPelajaran::with('kelas')->findOrFail($jadwalId);
        $user = User::find($dicatatOleh);
        $isAdmin = $user && ($user->isSuperAdmin() || $user->isRole(User::ROLE_ADMIN_SEKOLAH));

        // ── BR-04: Dilarang input tanggal masa depan (admin bebas) ────────────
        if (!$isAdmin && Carbon::parse($tanggal)->greaterThan(Carbon::today())) {
            throw new \Exception('Tidak dapat mengisi absensi untuk tanggal yang akan datang.');
        }

        // ── BR-03: Window waktu input (non-admin) ─────────────────────────────
        if (!$isAdmin && $user && !$this->validateWindowWaktu($jadwal, $user, $tanggal)) {
            throw new \Exception('Pengisian hanya dapat dilakukan pada jam pelajaran (dengan toleransi waktu).');
        }

        // ── BR-05: Cek hari libur (kelas + hari & tabel holidays) ─────────────
        $hari = $this->getHariIndoAbsensiPerJam($tanggal);
        if (!$isAdmin && $this->validateHariLibur($jadwal->kelas_id, $hari, $tanggal)) {
            throw new \Exception('Hari ini merupakan hari libur kelas. (Admin dapat override dengan konfirmasi.)');
        }

        // ── BR-06: Kecocokan kelas — siswa wajib kelas_id sama dengan jadwal ──
        $validSiswaIds = Siswa::where('kelas_id', $jadwal->kelas_id)
            ->where('status', 'aktif')
            ->pluck('id')
            ->all();

        $payload = [];
        $berhasil = 0;
        $gagal = 0;

        foreach ($rows as $row) {
            $siswaId = (int) ($row['siswa_id'] ?? 0);
            $status = $row['status'] ?? null;

            // BR-08 status valid + BR-06 kecocokan kelas
            if (!$siswaId || !in_array($status, self::STATUS_LIST, true) || !in_array($siswaId, $validSiswaIds, true)) {
                $gagal++;
                continue;
            }

            $payload[] = [
                'jadwal_pelajaran_id' => $jadwal->id,
                'siswa_id'            => $siswaId,
                'kelas_id'            => $jadwal->kelas_id, // denormalisasi
                'tanggal'             => $tanggal,
                'status'              => $status,
                'lama_terlambat'      => $status === 'terlambat' ? max((int) ($row['lama_terlambat'] ?? 0), 1) : null,
                'keterangan'          => $row['keterangan'] ?? null,
                'metode'              => $metode,
                'dicatat_oleh'        => $dicatatOleh, // BR-10 audit
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $berhasil++;
        }

        if ($payload === []) {
            throw new \Exception('Tidak ada baris absensi yang valid untuk disimpan.');
        }

        // ── BR-02: Bulk upsert dalam 1 transaksi DB ───────────────────────────
        DB::transaction(function () use ($payload, $jadwal, $tanggal, $dicatatOleh, $materi, $catatan) {
            AbsensiSiswaPerJadwal::upsert(
                $payload,
                ['jadwal_pelajaran_id', 'siswa_id', 'tanggal'], // UNIQUE BR-01
                ['status', 'lama_terlambat', 'keterangan', 'metode', 'dicatat_oleh', 'updated_at']
            );

            // Update atau buat header sesi ringkasan (AbsensiPerJamSesi - PRD-006 F-9)
            $allRecords = AbsensiSiswaPerJadwal::where('jadwal_pelajaran_id', $jadwal->id)
                ->where('tanggal', $tanggal)
                ->get();

            $sessionPayload = [
                'kelas_id'     => $jadwal->kelas_id,
                'guru_id'      => $jadwal->guru_id,
                'dicatat_oleh' => $dicatatOleh,
                'jumlah_siswa' => $allRecords->count(),
                'jumlah_hadir' => $allRecords->where('status', 'hadir')->count(),
                'jumlah_alpha' => $allRecords->where('status', 'alpha')->count(),
                'updated_at'   => now(),
            ];

            if ($materi !== null) {
                $sessionPayload['materi'] = $materi;
            }
            if ($catatan !== null) {
                $sessionPayload['catatan'] = $catatan;
            }

            AbsensiPerJamSesi::updateOrCreate(
                [
                    'jadwal_pelajaran_id' => $jadwal->id,
                    'tanggal'             => $tanggal,
                ],
                $sessionPayload
            );
        });

        return [
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
            'duplikat' => 0, // upsert menangani duplikat (BR-01) — tidak ada baris duplikat
        ];
    }

    /**
     * Validasi window waktu input (BR-03).
     *
     * Admin/super_admin → selalu true. Non-admin → hanya dalam rentang
     * `jam_mulai - X menit` s/d `jam_selesai + Y menit` (default X=5, Y=30,
     * key pengaturan: absensi_per_jam_window_buka_menit / ..._tutup_menit)
     * DAN hanya untuk tanggal hari ini (BR-09).
     */
    public function validateWindowWaktu(JadwalPelajaran $jadwal, User $user, string $tanggal): bool
    {
        if ($user->isSuperAdmin() || $user->isRole(User::ROLE_ADMIN_SEKOLAH)) {
            return true;
        }

        // BR-09: non-admin hanya boleh mengisi/edit data tanggal hari ini
        if (Carbon::parse($tanggal)->toDateString() !== now()->toDateString()) {
            return false;
        }

        // Izinkan pengisian absensi pada tanggal hari ini
        return true;
    }

    /**
     * Validasi hari libur (BR-05):
     * 1) `kelas_jadwal_absensi.is_libur = true` untuk (kelas, hari), dan
     * 2) tabel `holidays` (global, per tingkat, atau per kelas).
     */
    public function validateHariLibur(int $kelasId, string $hari, string $tanggal): bool
    {
        // kelas_jadwal_absensi.hari berformat lowercase & "minggu" (bukan "Ahad")
        $hariDb = strtolower($hari);
        $hariDb = str_replace('ahad', 'minggu', $hariDb);

        $liburKelas = KelasJadwalAbsensi::where('kelas_id', $kelasId)
            ->where('hari', $hariDb)
            ->where('is_libur', true)
            ->exists();

        if ($liburKelas) {
            return true;
        }

        $kelas = Kelas::find($kelasId);
        $tingkat = $kelas?->tingkat;

        return Holiday::whereDate('tanggal', $tanggal)
            ->where(function ($q) use ($tingkat, $kelasId) {
                $q->whereNull('tingkat')->whereNull('kelas_id');

                if ($tingkat) {
                    $q->orWhere(function ($qq) use ($tingkat) {
                        $qq->where('tingkat', $tingkat)->whereNull('kelas_id');
                    });
                }

                if ($kelasId) {
                    $q->orWhere('kelas_id', $kelasId);
                }
            })
            ->exists();
    }

    /**
     * Cek apakah guru adalah pengganti resmi untuk (jadwal, tanggal) —
     * berdasarkan monitoring_kehadiran_guru (F-3).
     */
    public function isGuruPengganti(int $guruId, int $jadwalId, string $tanggal): bool
    {
        return MonitoringKehadiranGuru::where('jadwal_pelajaran_id', $jadwalId)
            ->where('tanggal', $tanggal)
            ->where('status', 'tidak_hadir')
            ->where('ada_pengganti', true)
            ->where('guru_pengganti_id', $guruId)
            ->exists();
    }

    /**
     * Rekap per kelas: matriks siswa × pertemuan (F-5).
     *
     * @return array{
     *   kelas: ?Kelas,
     *   siswa: Collection<int, Siswa>,
     *   pertemuan: Collection<int, array>,
     *   pivot: array<int, array<string, string>>,
     *   akumulasi: array<int, array<string, int|float>>,
     *   statusKode: array<string, string>
     * }
     */
    public function getRekapPerKelas(int $kelasId, string $dari, string $sampai, ?int $mapelId = null): array
    {
        $mapelNama = null;
        if ($mapelId) {
            $mapelNama = Mapel::where('id', $mapelId)->value('nama_mapel');
        }

        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nis', 'nama_lengkap']);

        $absensiQuery = AbsensiSiswaPerJadwal::with('jadwalPelajaran')
            ->where('kelas_id', $kelasId)
            ->whereBetween('tanggal', [$dari, $sampai]);

        if ($mapelNama) {
            $absensiQuery->whereHas('jadwalPelajaran', fn ($q) => $q->where('mata_pelajaran', $mapelNama));
        }

        $absensiRows = $absensiQuery->get();

        // Daftar pertemuan (jadwal_pelajaran_id|tanggal) yang memiliki data
        $pertemuanMap = [];
        foreach ($absensiRows as $row) {
            $key = $row->jadwal_pelajaran_id . '|' . $row->tanggal->format('Y-m-d');
            if (isset($pertemuanMap[$key])) {
                continue;
            }

            $jp = $row->jadwalPelajaran;
            $pertemuanMap[$key] = [
                'key'                 => $key,
                'jadwal_pelajaran_id' => $row->jadwal_pelajaran_id,
                'tanggal'             => $row->tanggal->format('Y-m-d'),
                'mata_pelajaran'      => $jp?->mata_pelajaran ?? '-',
                'jam_mulai'           => $jp ? substr($jp->jam_mulai, 0, 5) : '-',
                'jam_selesai'         => $jp ? substr($jp->jam_selesai, 0, 5) : '-',
                'guru'                => $jp?->guru?->nama_lengkap ?? '-',
            ];
        }

        $pertemuanList = collect($pertemuanMap)
            ->sortBy([
                ['tanggal', 'asc'],
                ['jam_mulai', 'asc'],
            ])
            ->values();

        // Pivot & akumulasi
        $pivot = [];
        $akumulasi = [];
        foreach ($siswaList as $siswa) {
            $akumulasi[$siswa->id] = [
                'total'     => 0,
                'hadir'     => 0,
                'terlambat' => 0,
                'sakit'     => 0,
                'izin'      => 0,
                'alpha'     => 0,
                'dispen'    => 0,
                'bolos'     => 0,
                'persen'    => 0.0,
            ];
        }

        foreach ($absensiRows as $row) {
            $key = $row->jadwal_pelajaran_id . '|' . $row->tanggal->format('Y-m-d');
            $pivot[$row->siswa_id][$key] = self::STATUS_KODE[$row->status] ?? $row->status;

            if (isset($akumulasi[$row->siswa_id])) {
                $akumulasi[$row->siswa_id][$row->status]++;
                $akumulasi[$row->siswa_id]['total']++;
            }
        }

        foreach ($akumulasi as &$acc) {
            $efektifHadir = $acc['hadir'] + $acc['terlambat'];
            $acc['persen'] = $acc['total'] > 0
                ? round(($efektifHadir / $acc['total']) * 100, 1)
                : 0.0;
        }
        unset($acc);

        return [
            'kelas'      => Kelas::find($kelasId),
            'siswa'      => $siswaList,
            'pertemuan'  => $pertemuanList,
            'pivot'      => $pivot,
            'akumulasi'  => $akumulasi,
            'statusKode' => self::STATUS_KODE,
        ];
    }

    /**
     * Rekap per siswa: riwayat kronologis per jam + akumulasi per mapel (F-5).
     *
     * @return array{
     *   siswa: Siswa,
     *   riwayat: Collection<int, array>,
     *   perMapel: Collection<int, array>,
     *   dari: string,
     *   sampai: string
     * }
     */
    public function getRekapPerSiswa(int $siswaId, string $dari, string $sampai): array
    {
        $siswa = Siswa::with('kelas')->findOrFail($siswaId);

        $rows = AbsensiSiswaPerJadwal::with(['jadwalPelajaran.kelas', 'jadwalPelajaran.guru'])
            ->where('siswa_id', $siswaId)
            ->whereBetween('tanggal', [$dari, $sampai])
            ->orderBy('tanggal')
            ->orderBy('jadwal_pelajaran_id')
            ->get();

        $riwayat = $rows->map(function (AbsensiSiswaPerJadwal $r) {
            $jp = $r->jadwalPelajaran;

            return [
                'tanggal'         => $r->tanggal->format('Y-m-d'),
                'hari'            => $this->getHariIndoAbsensiPerJam($r->tanggal->format('Y-m-d')),
                'mata_pelajaran'  => $jp?->mata_pelajaran ?? '-',
                'jam_mulai'       => $jp ? substr($jp->jam_mulai, 0, 5) : '-',
                'jam_selesai'     => $jp ? substr($jp->jam_selesai, 0, 5) : '-',
                'kelas'           => $jp?->kelas?->nama ?? '-',
                'guru'            => $jp?->guru?->nama_lengkap ?? '-',
                'status'          => $r->status,
                'lama_terlambat'  => $r->lama_terlambat,
                'keterangan'      => $r->keterangan,
                'metode'          => $r->metode,
            ];
        });

        $perMapel = $rows->groupBy(fn ($r) => $r->jadwalPelajaran?->mata_pelajaran ?? '-')
            ->map(function ($items, $mapel) {
                $acc = [
                    'total'     => 0,
                    'hadir'     => 0,
                    'terlambat' => 0,
                    'sakit'     => 0,
                    'izin'      => 0,
                    'alpha'     => 0,
                    'dispen'    => 0,
                    'bolos'     => 0,
                ];

                foreach ($items as $r) {
                    if (isset($acc[$r->status])) {
                        $acc[$r->status]++;
                    }
                    $acc['total']++;
                }

                $efektifHadir = $acc['hadir'] + $acc['terlambat'];
                $acc['persen'] = $acc['total'] > 0
                    ? round(($efektifHadir / $acc['total']) * 100, 1)
                    : 0.0;

                return [
                    'mata_pelajaran' => $mapel,
                    'total'          => $acc['total'],
                    'hadir'          => $acc['hadir'],
                    'terlambat'      => $acc['terlambat'],
                    'sakit'          => $acc['sakit'],
                    'izin'           => $acc['izin'],
                    'alpha'          => $acc['alpha'],
                    'dispen'         => $acc['dispen'],
                    'bolos'          => $acc['bolos'],
                    'persen'         => $acc['persen'],
                ];
            })
            ->values();

        return [
            'siswa'    => $siswa,
            'riwayat'  => $riwayat,
            'perMapel' => $perMapel,
            'dari'     => $dari,
            'sampai'   => $sampai,
        ];
    }

    /**
     * Tempel flag status (sedang_berlangsung / berikutnya / selesai) dan info
     * tambahan (is_pengganti, sudah_diisi) ke tiap item jadwal, lalu urutkan:
     * sedang berlangsung → berikutnya → belum mulai → selesai (per jam_mulai).
     *
     * @param Collection<int, JadwalPelajaran> $jadwal
     * @return Collection<int, JadwalPelajaran>
     */
    protected function withStatusFlags(Collection $jadwal, string $tanggal, ?int $guruId = null): Collection
    {
        $now = now();

        $terisiJadwalIds = AbsensiSiswaPerJadwal::where('tanggal', $tanggal)
            ->whereIn('jadwal_pelajaran_id', $jadwal->pluck('id')->all())
            ->distinct()
            ->pluck('jadwal_pelajaran_id')
            ->all();

        $penggantiJadwalIds = [];
        if ($guruId) {
            $penggantiJadwalIds = MonitoringKehadiranGuru::where('tanggal', $tanggal)
                ->where('status', 'tidak_hadir')
                ->where('ada_pengganti', true)
                ->where('guru_pengganti_id', $guruId)
                ->pluck('jadwal_pelajaran_id')
                ->all();
        }

        $statusFlag = [];
        $sudahNext = false;

        foreach ($jadwal as $item) {
            $mulai  = Carbon::parse($item->jam_mulai);
            $selesai = Carbon::parse($item->jam_selesai);

            if ($now->between($mulai, $selesai)) {
                $statusFlag[$item->id] = 'sedang_berlangsung';
            } elseif ($now->lt($mulai)) {
                if (!$sudahNext) {
                    $statusFlag[$item->id] = 'berikutnya';
                    $sudahNext = true;
                } else {
                    $statusFlag[$item->id] = 'belum_mulai';
                }
            } else {
                $statusFlag[$item->id] = 'selesai';
            }

            $item->setAttribute('sedang_berlangsung', $statusFlag[$item->id] === 'sedang_berlangsung');
            $item->setAttribute('berikutnya', $statusFlag[$item->id] === 'berikutnya');
            $item->setAttribute('selesai', $statusFlag[$item->id] === 'selesai');
            $item->setAttribute('is_pengganti', in_array($item->id, $penggantiJadwalIds, true));
            $item->setAttribute('sudah_diisi', in_array($item->id, $terisiJadwalIds, true));
        }

        $order = ['sedang_berlangsung' => 0, 'berikutnya' => 1, 'belum_mulai' => 2, 'selesai' => 3];

        return $jadwal
            ->sortBy(function ($item) use ($order, $statusFlag) {
                return [$order[$statusFlag[$item->id]] ?? 9, $item->jam_mulai];
            })
            ->values();
    }
}
