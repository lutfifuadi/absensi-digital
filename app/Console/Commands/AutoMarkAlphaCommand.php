<?php

namespace App\Console\Commands;

use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AutoMarkAlphaCommand extends Command
{
    protected $signature = 'absensi:auto-alpha
                            {--tanggal= : Tanggal target (Y-m-d), default hari ini}';

    protected $description = 'Otomatis menandai alpha bagi siswa/guru/staff yang belum memiliki catatan absensi pada tanggal tertentu.';

    public function handle(): int
    {
        $tanggal = $this->option('tanggal')
            ? Carbon::parse($this->option('tanggal'))->toDateString()
            : now()->toDateString();

        $jamMasuk = Pengaturan::where('key', 'jam_masuk')->value('value') ?? '07:00';

        $this->info("Auto-mark alpha untuk tanggal: {$tanggal}");

        // --- Siswa aktif ---
        $siswaAktif = Siswa::with('kelas')->where('status', 'aktif')->get();
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
            }

            AbsensiSiswa::create([
                'siswa_id'   => $siswaId,
                'kelas_id'   => $s?->kelas_id,
                'tanggal'    => $tanggal,
                'jam_masuk'  => null,
                'jam_pulang' => null,
                'status'     => 'alpha',
                'keterangan' => 'Otomatis oleh sistem (tidak ada catatan absensi)',
                'guru_id'    => null,
                'metode'     => 'manual',
            ]);
            $countSiswaAlpha++;
        }
        $this->line("  Siswa alpha dibuat: {$countSiswaAlpha}");

        // --- Guru aktif ---
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
                'metode'     => 'manual',
            ]);
        }
        $this->line("  Guru alpha dibuat: {$belumAbsenGuru->count()}");

        // --- Staff aktif ---
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
                'metode'     => 'manual',
            ]);
        }
        $this->line("  Staff alpha dibuat: {$belumAbsenStaff->count()}");

        $this->info('Auto-alpha selesai.');

        return self::SUCCESS;
    }
}
