<?php

namespace App\Console\Commands;

use App\Helpers\JadwalAbsensiHelper;
use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Notifications\NotifikasiAutoAlpha;
use App\Services\PengaturanService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoMarkAlphaCommand extends Command
{
    protected $signature = 'absensi:auto-alpha
                            {--tanggal= : Tanggal target (Y-m-d), default hari ini}
                            {--force : Paksa penandaan alpha tanpa mengecek batas jam masuk kelas}';

    protected $description = 'Otomatis menandai alpha bagi siswa/guru/staff yang belum memiliki catatan absensi pada tanggal tertentu.';

    public function __construct(
        private PengaturanService $pengaturanService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        // ── Cek toggle auto_alpha_siswa_enabled ──────────────────────────
        if (!$this->pengaturanService->isAutoAlphaEnabled()) {
            $this->warn('Auto-alpha dinonaktifkan oleh admin. Proses dilewati.');
            Log::info('Auto-alpha dinonaktifkan oleh admin. Proses dilewati.');
            return self::SUCCESS;
        }

        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))->toDateString()
            : now()->toDateString();

        $jamMasuk = Pengaturan::where('key', 'jam_masuk')->value('value') ?? '07:00';

        $this->info("Auto-mark alpha untuk tanggal: {$tanggal}");

        // ── Variabel untuk notifikasi & count (dikirim SETELAH commit) ──
        $pendingNotifications = [];
        $countSiswaAlpha = 0;
        $countGuruAlpha = 0;
        $countStaffAlpha = 0;

        $currentTimeStr = now()->format('H:i');
        $isToday        = ($tanggal === now()->toDateString());
        $isForce        = (bool) $this->option('force');
        $hariNama       = strtolower(Carbon::parse($tanggal)->locale('id')->isoFormat('dddd'));

        // ── Wrap seluruh proses dalam DB::transaction (C1) ──────────────
        DB::transaction(function () use (
            $tanggal,
            $jamMasuk,
            $currentTimeStr,
            $isToday,
            $isForce,
            $hariNama,
            &$pendingNotifications,
            &$countSiswaAlpha,
            &$countGuruAlpha,
            &$countStaffAlpha
        ) {
            // --- Siswa aktif ---
            $siswaAktif = Siswa::with('kelas.waliKelas.user')->where('status', 'aktif')->get();
            $siswaAktifIds = $siswaAktif->pluck('id');
            $sudahAbsenSiswa = AbsensiSiswa::whereDate('tanggal', $tanggal)
                ->whereIn('siswa_id', $siswaAktifIds)->pluck('siswa_id')->toArray();
            $belumAbsenSiswaIds = $siswaAktifIds->diff($sudahAbsenSiswa);

            $holidaysToday = \App\Models\Holiday::whereDate('tanggal', $tanggal)->get();

            $countSiswaAlpha = 0;
            foreach ($belumAbsenSiswaIds as $siswaId) {
                $s = $siswaAktif->firstWhere('id', $siswaId);
                if ($s) {
                    // Cek apakah hari tersebut libur untuk siswa tersebut menggunakan $holidaysToday->contains(...)
                    $isLibur = $holidaysToday->contains(function ($holiday) use ($s) {
                        // Global holiday (tingkat & kelas null)
                        if (is_null($holiday->tingkat) && is_null($holiday->kelas_id)) {
                            return true;
                        }
                        // Cocok tingkat
                        if ($holiday->tingkat && $s->kelas && $s->kelas->tingkat === $holiday->tingkat) {
                            return true;
                        }
                        // Cocok kelas_id
                        if ($holiday->kelas_id && $s->kelas_id === $holiday->kelas_id) {
                            return true;
                        }
                        return false;
                    });

                    if ($isLibur) {
                        continue;
                    }

                    // Skip jika kelas tidak aktif absensi
                    if ($s->kelas && !$s->kelas->is_aktif_absensi) {
                        continue;
                    }
                }

                // ── Ambil batas jam masuk dari jadwal per kelas ──────────────
                $kelasId = $s?->kelas_id;
                $batasJamMasuk = '09:00'; // default
                $isLiburJadwal = false;

                if ($kelasId) {
                    $jadwal = JadwalAbsensiHelper::getJadwalForKelas($kelasId, $hariNama);
                    $batasJamMasuk = $jadwal['batas_jam_masuk'] ?? '09:00';
                    $isLiburJadwal = $jadwal['is_libur'] ?? false;
                }

                // Skip jika kelas ditandai libur di jadwal_absensi
                if ($isLiburJadwal) {
                    continue;
                }

                // ── OPSI B: Pengecekan Batas Jam Masuk Kelas ────────────────
                // Jika untuk hari ini dan TIDAK di-force,
                // skip siswa jika waktu sekarang BELUM mencapai/melewati batas_jam_masuk kelas
                if ($isToday && !$isForce) {
                    if ($currentTimeStr < $batasJamMasuk) {
                        continue;
                    }
                }

                $keterangan = "Alpha otomatis: Tidak ada absensi masuk hingga batas jam {$batasJamMasuk}. Jika ada kesalahan, hubungi wali kelas.";

                $absensi = AbsensiSiswa::create([
                    'siswa_id'   => $siswaId,
                    'kelas_id'   => $kelasId,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => null,
                    'jam_pulang' => null,
                    'status'     => 'alpha',
                    'keterangan' => $keterangan,
                    'guru_id'    => null,
                    'metode'     => 'auto-alpha',
                ]);

                // ── Kumpulkan notifikasi, kirim SETELAH commit (C1) ────
                $pendingNotifications[] = [$s, $absensi, $batasJamMasuk];

                $countSiswaAlpha++;
            }


            // --- Guru aktif (H4: metode 'auto-alpha' untuk konsistensi) ---
            $guruAktif = Guru::where('status', 'aktif')->pluck('id');
            $sudahAbsenGuru = AbsensiGuru::whereDate('tanggal', $tanggal)
                ->whereIn('guru_id', $guruAktif)->pluck('guru_id')->toArray();
            $belumAbsenGuru = $guruAktif->diff($sudahAbsenGuru);

            foreach ($belumAbsenGuru as $guruId) {
                AbsensiGuru::create([
                    'guru_id'    => $guruId,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => null,
                    'jam_pulang' => null,
                    'status'     => 'alpha',
                    'keterangan' => 'Otomatis oleh sistem',
                    'metode'     => 'auto-alpha',
                ]);
                $countGuruAlpha++;
            }

            // --- Staff aktif (H4: metode 'auto-alpha' untuk konsistensi) ---
            $staffAktif = StaffTataUsaha::where('status', 'aktif')->pluck('id');
            $sudahAbsenStaff = AbsensiStaff::whereDate('tanggal', $tanggal)
                ->whereIn('staff_id', $staffAktif)->pluck('staff_id')->toArray();
            $belumAbsenStaff = $staffAktif->diff($sudahAbsenStaff);

            foreach ($belumAbsenStaff as $staffId) {
                AbsensiStaff::create([
                    'staff_id'   => $staffId,
                    'tanggal'    => $tanggal,
                    'jam_masuk'  => null,
                    'jam_pulang' => null,
                    'status'     => 'alpha',
                    'keterangan' => 'Otomatis oleh sistem',
                    'metode'     => 'auto-alpha',
                ]);
                $countStaffAlpha++;
            }
        }); // ── End DB::transaction (C1) ──

        // ── Kirim notifikasi SETELAH commit (C1) ─────────────────────────
        foreach ($pendingNotifications as [$siswa, $absensi, $batasJam]) {
            $this->kirimNotifikasiAutoAlpha($siswa, $absensi, $batasJam);
        }

        $this->line("  Siswa alpha dibuat: {$countSiswaAlpha}");
        $this->line("  Guru alpha dibuat: {$countGuruAlpha}");
        $this->line("  Staff alpha dibuat: {$countStaffAlpha}");

        $this->info('Auto-alpha selesai.');

        return self::SUCCESS;
    }

    /**
     * Kirim notifikasi in-app ke siswa, orang tua, dan wali kelas.
     */
    private function kirimNotifikasiAutoAlpha(
        Siswa $siswa,
        AbsensiSiswa $absensi,
        string $batasJamMasuk
    ): void {
        $namaSiswa = $siswa->nama_lengkap ?? '-';
        $namaKelas = $siswa->kelas?->nama ?? '-';
        $tanggal   = $absensi->tanggal instanceof Carbon
            ? $absensi->tanggal->toDateString()
            : $absensi->tanggal;
        $keterangan = $absensi->keterangan ?? '-';

        try {
            // 1. Notifikasi ke Siswa
            $siswaUser = $siswa->user;
            if ($siswaUser) {
                $siswaUser->notify(
                    new NotifikasiAutoAlpha('siswa', $namaSiswa, $namaKelas, $tanggal, $batasJamMasuk, $keterangan)
                );
            }

            // 2. Notifikasi ke Orang Tua
            $ortuUser = $siswa->ortuUser;
            if ($ortuUser) {
                $ortuUser->notify(
                    new NotifikasiAutoAlpha('ortu', $namaSiswa, $namaKelas, $tanggal, $batasJamMasuk, $keterangan)
                );
            }

            // 3. Notifikasi ke Wali Kelas
            $waliKelas = $siswa->kelas?->waliKelas;
            if ($waliKelas && $waliKelas->user) {
                $waliKelas->user->notify(
                    new NotifikasiAutoAlpha('wali_kelas', $namaSiswa, $namaKelas, $tanggal, $batasJamMasuk, $keterangan)
                );
            }
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi auto-alpha untuk siswa {$namaSiswa}: " . $e->getMessage());
        }
    }


}
