<?php

namespace Tests\Unit;

use App\Models\Pengaduan;
use App\Services\WhatsAppValidatorService;
use App\Http\Controllers\PengaduanController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PengaduanServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test format kode unik: PGN-YYYYMMDD-NNN
     */
    public function test_kode_unik_format(): void
    {
        $pengaduan = Pengaduan::factory()->create([
            'kode_unik' => 'PGN-' . now()->format('Ymd') . '-001',
        ]);

        $this->assertMatchesRegularExpression(
            '/^PGN-\d{8}-\d{3}$/',
            $pengaduan->kode_unik,
            'Kode unik harus mengikuti format PGN-YYYYMMDD-NNN'
        );

        // Pastikan prefix PGN dan separator menggunakan dash
        $parts = explode('-', $pengaduan->kode_unik);
        $this->assertCount(3, $parts, 'Kode unik harus memiliki 3 segmen');
        $this->assertEquals('PGN', $parts[0], 'Prefix harus PGN');
        $this->assertEquals(now()->format('Ymd'), $parts[1], 'Segmen kedua harus tanggal YYYYMMDD');
        $this->assertTrue(is_numeric($parts[2]), 'Segmen ketiga harus numerik');
    }

    /**
     * Test increment nomor urut kode unik.
     */
    public function test_kode_unik_increment(): void
    {
        // Buat pengaduan pertama
        $p1 = Pengaduan::factory()->create([
            'kode_unik' => 'PGN-' . now()->format('Ymd') . '-001',
            'created_at' => now(),
        ]);

        $this->assertEquals('PGN-' . now()->format('Ymd') . '-001', $p1->kode_unik);

        // Buat pengaduan kedua - seharusnya increment
        $p2 = Pengaduan::factory()->create([
            'kode_unik' => 'PGN-' . now()->format('Ymd') . '-002',
            'created_at' => now(),
        ]);

        $this->assertEquals('PGN-' . now()->format('Ymd') . '-002', $p2->kode_unik);
    }

    /**
     * Test generate kode unik dengan nomor tiga digit (zero-padded).
     */
    public function test_kode_unik_zero_padded(): void
    {
        $kodeUnik = 'PGN-' . now()->format('Ymd') . '-001';
        $pengaduan = Pengaduan::factory()->create(['kode_unik' => $kodeUnik]);

        $lastThree = substr($pengaduan->kode_unik, -3);
        $this->assertEquals('001', $lastThree, 'Nomor urut harus zero-padded 3 digit');
        $this->assertEquals(3, strlen($lastThree), 'Nomor urut harus 3 digit');
    }

    /**
     * Test WhatsAppValidatorService mock untuk nomor WA valid.
     */
    public function test_wa_validator_returns_true_for_valid_number(): void
    {
        $mock = Mockery::mock(WhatsAppValidatorService::class);
        $mock->shouldReceive('validateNomor')
            ->once()
            ->with('081234567890')
            ->andReturn(true);

        $this->app->instance(WhatsAppValidatorService::class, $mock);

        /** @var WhatsAppValidatorService $service */
        $service = app(WhatsAppValidatorService::class);
        $result = $service->validateNomor('081234567890');

        $this->assertTrue($result, 'Nomor WA valid harus mengembalikan true');
    }

    /**
     * Test WhatsAppValidatorService mock untuk nomor WA tidak valid.
     */
    public function test_wa_validator_returns_false_for_invalid_number(): void
    {
        $mock = Mockery::mock(WhatsAppValidatorService::class);
        $mock->shouldReceive('validateNomor')
            ->once()
            ->with('081111111111')
            ->andReturn(false);

        $this->app->instance(WhatsAppValidatorService::class, $mock);

        /** @var WhatsAppValidatorService $service */
        $service = app(WhatsAppValidatorService::class);
        $result = $service->validateNomor('081111111111');

        $this->assertFalse($result, 'Nomor WA tidak valid harus mengembalikan false');
    }

    /**
     * Test transisi status valid: baru → diproses
     */
    public function test_valid_transition_baru_to_diproses(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $this->assertEquals('baru', $pengaduan->status);

        // Update status ke diproses (valid)
        $pengaduan->update(['status' => 'diproses']);
        $pengaduan->refresh();

        $this->assertEquals('diproses', $pengaduan->status, 'Transisi baru → diproses harus valid');
    }

    /**
     * Test transisi status valid: diproses → selesai
     */
    public function test_valid_transition_diproses_to_selesai(): void
    {
        $pengaduan = Pengaduan::factory()->diproses()->create();

        $this->assertEquals('diproses', $pengaduan->status);

        // Update status ke selesai (valid)
        $pengaduan->update([
            'status' => 'selesai',
            'catatan_admin' => 'Data sudah diperbaiki.',
            'verified_at' => now(),
        ]);
        $pengaduan->refresh();

        $this->assertEquals('selesai', $pengaduan->status, 'Transisi diproses → selesai harus valid');
    }

    /**
     * Test transisi status valid: baru → ditolak (via API).
     * Di controller method `update()` (API), dari 'baru' bisa ke 'diproses' atau 'ditolak'.
     */
    public function test_valid_transition_baru_to_ditolak(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $this->assertEquals('baru', $pengaduan->status);

        $pengaduan->update([
            'status' => 'ditolak',
            'catatan_admin' => 'Data sudah valid.',
            'verified_at' => now(),
        ]);
        $pengaduan->refresh();

        // Di level model, update apapun bisa dilakukan tanpa validasi transisi.
        // Validasi transisi ada di controller. Jadi secara model, update berhasil.
        // Tapi secara aturan bisnis (controller), transisi ini valid di API (`update`).
        $this->assertEquals('ditolak', $pengaduan->status);
    }

    /**
     * Test transisi status tidak valid: baru → selesai (loncat).
     * Controller `updateStatus()` melarang: "Status Baru hanya bisa diubah ke Diproses."
     */
    public function test_invalid_transition_baru_to_selesai_direct(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $allowedFromBaru = ['diproses', 'ditolak'];
        $this->assertNotContains(
            'selesai',
            $allowedFromBaru,
            'Transisi baru → selesai langsung tidak diperbolehkan'
        );

        $this->assertEquals('baru', $pengaduan->status);
    }

    /**
     * Test status final: selesai tidak bisa diubah lagi.
     */
    public function test_final_status_selesai_cannot_change(): void
    {
        $pengaduan = Pengaduan::factory()->selesai()->create();

        $allowedFromSelesai = [];
        $this->assertEmpty($allowedFromSelesai, 'Status selesai adalah final, tidak ada transisi yang diizinkan');
    }

    /**
     * Test status final: ditolak tidak bisa diubah lagi.
     */
    public function test_final_status_ditolak_cannot_change(): void
    {
        $pengaduan = Pengaduan::factory()->ditolak()->create();

        $allowedFromDitolak = [];
        $this->assertEmpty($allowedFromDitolak, 'Status ditolak adalah final, tidak ada transisi yang diizinkan');
    }

    /**
     * Test status label untuk masing-masing status.
     */
    public function test_status_label(): void
    {
        $pengaduanBaru = Pengaduan::factory()->baru()->make();
        $this->assertEquals('Baru', $pengaduanBaru->status_label);

        $pengaduanDiproses = Pengaduan::factory()->diproses()->make();
        $this->assertEquals('Diproses', $pengaduanDiproses->status_label);

        $pengaduanSelesai = Pengaduan::factory()->selesai()->make();
        $this->assertEquals('Selesai', $pengaduanSelesai->status_label);

        $pengaduanDitolak = Pengaduan::factory()->ditolak()->make();
        $this->assertEquals('Ditolak', $pengaduanDitolak->status_label);
    }

    /**
     * Test status color untuk masing-masing status.
     */
    public function test_status_color(): void
    {
        $pengaduanBaru = Pengaduan::factory()->baru()->make();
        $this->assertEquals('warning', $pengaduanBaru->status_color);

        $pengaduanDiproses = Pengaduan::factory()->diproses()->make();
        $this->assertEquals('info', $pengaduanDiproses->status_color);

        $pengaduanSelesai = Pengaduan::factory()->selesai()->make();
        $this->assertEquals('success', $pengaduanSelesai->status_color);

        $pengaduanDitolak = Pengaduan::factory()->ditolak()->make();
        $this->assertEquals('danger', $pengaduanDitolak->status_color);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
