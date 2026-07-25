<?php

namespace Tests\Unit;

use App\Helpers\JadwalAbsensiHelper;
use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use App\Models\Pengaturan;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Unit Test Cases untuk JadwalAbsensiHelper.
 *
 * PRD-016: Helper untuk mengelola jadwal absensi per kelas per hari.
 * Menguji method: getJadwalForKelas, isLibur, formatTime.
 */
class JadwalAbsensiHelperTest extends TestCase
{
    use RefreshDatabase;

    protected Kelas $kelas;
    protected TahunAkademik $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama'            => '2026/2027',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif'        => true,
        ]);

        // Buat Kelas
        $this->kelas = Kelas::create([
            'nama'               => 'X MIPA 1',
            'tingkat'            => 'X',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);
    }

    /**
     * Helper: Format time value from helper result.
     * Model casts time fields as 'datetime:H:i' which returns Carbon objects.
     * This helper converts them to 'H:i' string for comparison.
     */
    private function fmtTime(mixed $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }
        return $value;
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: getJadwalForKelas
    // ════════════════════════════════════════════════════════════════════════

    public function test_get_jadwal_for_kelas_returns_correct_data(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        $jadwal = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');

        $this->assertIsArray($jadwal);
        $this->assertEquals('06:30', $this->fmtTime($jadwal['jam_mulai_absensi']));
        $this->assertEquals('07:30', $this->fmtTime($jadwal['jam_masuk']));
        $this->assertEquals('15:30', $this->fmtTime($jadwal['jam_pulang']));
        $this->assertEquals('17:30', $this->fmtTime($jadwal['jam_akhir_pulang']));
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_get_jadwal_for_kelas_falls_back_to_global_when_field_null(): void
    {
        // Setup pengaturan global
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '05:30', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '06:30', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_pulang'], ['value' => '14:30', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_akhir_pulang'], ['value' => '16:30', 'group' => 'absensi']);

        // Buat jadwal dengan semua field time = null
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'selasa',
            'jam_mulai_absensi'  => null,
            'jam_masuk'          => null,
            'jam_pulang'         => null,
            'jam_akhir_pulang'   => null,
            'is_libur'           => false,
        ]);

        // Clear cache agar membaca dari DB
        Cache::forget('absensi_settings');

        $jadwal = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'selasa');

        // Field yang NULL harus fallback ke pengaturan global
        $this->assertEquals('05:30', $jadwal['jam_mulai_absensi']);
        $this->assertEquals('06:30', $jadwal['jam_masuk']);
        $this->assertEquals('14:30', $jadwal['jam_pulang']);
        $this->assertEquals('16:30', $jadwal['jam_akhir_pulang']);
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_get_jadwal_for_kelas_falls_back_to_default_when_no_global_settings(): void
    {
        // Tidak ada record di tabel kelas_jadwal_absensi
        // Tidak ada pengaturan global
        Cache::forget('absensi_settings');

        $jadwal = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'rabu');

        // Harus fallback ke default hardcoded
        $this->assertEquals('06:00', $jadwal['jam_mulai_absensi']);
        $this->assertEquals('07:00', $jadwal['jam_masuk']);
        $this->assertEquals('15:00', $jadwal['jam_pulang']);
        $this->assertEquals('17:00', $jadwal['jam_akhir_pulang']);
        $this->assertFalse($jadwal['is_libur']);
    }

    public function test_get_jadwal_for_kelas_works_for_each_day(): void
    {
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        foreach ($hariList as $index => $hari) {
            KelasJadwalAbsensi::create([
                'kelas_id'           => $this->kelas->id,
                'hari'               => $hari,
                'jam_mulai_absensi'  => '0' . $index . ':00',
                'jam_masuk'          => '0' . ($index + 1) . ':00',
                'is_libur'           => ($hari === 'sabtu' || $hari === 'minggu'),
            ]);

            $jadwal = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, $hari);

            $this->assertEquals('0' . $index . ':00', $this->fmtTime($jadwal['jam_mulai_absensi']));
            $this->assertEquals('0' . ($index + 1) . ':00', $this->fmtTime($jadwal['jam_masuk']));
            $this->assertEquals(
                ($hari === 'sabtu' || $hari === 'minggu'),
                $jadwal['is_libur'],
                "is_libur should be true for {$hari}"
            );
        }
    }

    public function test_get_jadwal_for_kelas_prioritizes_class_schedule_over_global(): void
    {
        // Setup global
        Pengaturan::updateOrCreate(['key' => 'jam_mulai_absensi'], ['value' => '06:00', 'group' => 'absensi']);
        Pengaturan::updateOrCreate(['key' => 'jam_masuk'], ['value' => '07:00', 'group' => 'absensi']);
        Cache::forget('absensi_settings');

        // Buat jadwal kelas dengan jam berbeda dari global
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'kamis',
            'jam_mulai_absensi'  => '05:00',
            'jam_masuk'          => '06:00',
            'is_libur'           => false,
        ]);

        $jadwal = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'kamis');

        // Harus menggunakan jadwal kelas, bukan global
        $this->assertEquals('05:00', $this->fmtTime($jadwal['jam_mulai_absensi']));
        $this->assertEquals('06:00', $this->fmtTime($jadwal['jam_masuk']));
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: isLibur
    // ════════════════════════════════════════════════════════════════════════

    public function test_is_libur_returns_true_when_libur(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'sabtu',
            'is_libur'   => true,
        ]);

        $result = JadwalAbsensiHelper::isLibur($this->kelas->id, 'sabtu');

        $this->assertTrue($result);
    }

    public function test_is_libur_returns_false_when_not_libur(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'is_libur'   => false,
        ]);

        $result = JadwalAbsensiHelper::isLibur($this->kelas->id, 'senin');

        $this->assertFalse($result);
    }

    public function test_is_libur_returns_false_when_no_record(): void
    {
        // Tidak ada record → default false
        $result = JadwalAbsensiHelper::isLibur($this->kelas->id, 'jumat');

        $this->assertFalse($result);
    }

    public function test_is_libur_with_all_days(): void
    {
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];

        foreach ($hariList as $hari) {
            KelasJadwalAbsensi::create([
                'kelas_id'   => $this->kelas->id,
                'hari'       => $hari,
                'is_libur'   => false,
            ]);

            $this->assertFalse(
                JadwalAbsensiHelper::isLibur($this->kelas->id, $hari),
                "{$hari} should not be libur"
            );
        }

        // Sabtu dan Minggu default libur
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'sabtu',
            'is_libur'   => true,
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'minggu',
            'is_libur'   => true,
        ]);

        $this->assertTrue(JadwalAbsensiHelper::isLibur($this->kelas->id, 'sabtu'));
        $this->assertTrue(JadwalAbsensiHelper::isLibur($this->kelas->id, 'minggu'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: formatTime
    // ════════════════════════════════════════════════════════════════════════

    public function test_format_time_returns_correct_format_h_i(): void
    {
        $result = JadwalAbsensiHelper::formatTime('07:30');

        $this->assertEquals('07:30', $result);
    }

    public function test_format_time_strips_seconds_from_h_i_s(): void
    {
        $result = JadwalAbsensiHelper::formatTime('07:30:00');

        $this->assertEquals('07:30', $result);
    }

    public function test_format_time_returns_null_for_null_input(): void
    {
        $result = JadwalAbsensiHelper::formatTime(null);

        $this->assertNull($result);
    }

    public function test_format_time_returns_original_for_other_formats(): void
    {
        // Format pendek misal "7:3" atau format lain
        $result = JadwalAbsensiHelper::formatTime('7:3');

        $this->assertEquals('7:3', $result);
    }

    public function test_format_time_handles_edge_cases(): void
    {
        // Midnight
        $this->assertEquals('00:00', JadwalAbsensiHelper::formatTime('00:00'));
        $this->assertEquals('00:00', JadwalAbsensiHelper::formatTime('00:00:00'));

        // End of day
        $this->assertEquals('23:59', JadwalAbsensiHelper::formatTime('23:59'));
        $this->assertEquals('23:59', JadwalAbsensiHelper::formatTime('23:59:00'));

        // Single digit hour — '9:00:00' has length 7, not 8, so it won't be truncated
        $this->assertEquals('9:00', JadwalAbsensiHelper::formatTime('9:00'));
        $this->assertEquals('9:00:00', JadwalAbsensiHelper::formatTime('9:00:00'));
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: HARI_LIST constant
    // ════════════════════════════════════════════════════════════════════════

    public function test_hari_list_contains_all_days(): void
    {
        $expected = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

        $this->assertEquals($expected, JadwalAbsensiHelper::HARI_LIST);
    }

    public function test_hari_list_has_exactly_7_items(): void
    {
        $this->assertCount(7, JadwalAbsensiHelper::HARI_LIST);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST: Multiple Kelas — Data Isolation
    // ════════════════════════════════════════════════════════════════════════

    public function test_different_kelas_can_have_different_schedules(): void
    {
        // Buat kelas kedua
        $kelas2 = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Jadwal berbeda untuk senin
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:00',
            'jam_masuk'          => '07:00',
            'is_libur'           => false,
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id'           => $kelas2->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '08:00',
            'jam_masuk'          => '09:00',
            'is_libur'           => false,
        ]);

        $jadwal1 = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');
        $jadwal2 = JadwalAbsensiHelper::getJadwalForKelas($kelas2->id, 'senin');

        $this->assertEquals('07:00', $this->fmtTime($jadwal1['jam_masuk']));
        $this->assertEquals('09:00', $this->fmtTime($jadwal2['jam_masuk']));
        $this->assertNotEquals($this->fmtTime($jadwal1['jam_masuk']), $this->fmtTime($jadwal2['jam_masuk']));
    }

    public function test_same_kelas_different_days_different_schedules(): void
    {
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '07:00',
            'is_libur'   => false,
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'jumat',
            'jam_masuk'  => '07:30',
            'is_libur'   => false,
        ]);

        $jadwalSenin = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'senin');
        $jadwalJumat = JadwalAbsensiHelper::getJadwalForKelas($this->kelas->id, 'jumat');

        $this->assertEquals('07:00', $this->fmtTime($jadwalSenin['jam_masuk']));
        $this->assertEquals('07:30', $this->fmtTime($jadwalJumat['jam_masuk']));
    }
}
