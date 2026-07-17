<?php

namespace Tests\Unit;

use App\Models\Pengaturan;
use App\Models\NotificationTemplate;
use App\Services\WhatsAppPengaduanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppPengaduanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WhatsAppPengaduanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Pengaturan::updateOrCreate(['key' => 'wa_pengaduan_api_key'], ['value' => 'test-api-key']);
        Pengaturan::updateOrCreate(['key' => 'wa_pengaduan_endpoint'], ['value' => 'https://wa.test-gateway.id']);
        Pengaturan::updateOrCreate(['key' => 'wa_pengaduan_sender'], ['value' => '62811111111']);
        Pengaturan::updateOrCreate(['key' => 'wa_pengaduan_group_id'], ['value' => '120363xxxxxx@g.us']);

        $this->service = new WhatsAppPengaduanService();
    }

    public function test_send_kode_unik(): void
    {
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendKodeUnik('081234567890', 'PGN-123', 'Budi');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://wa.test-gateway.id/send-message'
                && $request['number'] === '6281234567890'
                && str_contains($request['message'], 'PGN-123')
                && str_contains($request['message'], 'Budi');
        });
    }

    public function test_send_kode_unik_custom_template(): void
    {
        NotificationTemplate::updateOrCreate(['type' => 'pengaduan_kode_unik'], ['content' => 'Pesan unik: {nama} ({kode_unik})']);
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendKodeUnik('081234567890', 'PGN-123', 'Budi');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request['message'] === 'Pesan unik: Budi (PGN-123)';
        });
    }

    public function test_send_status_update(): void
    {
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendStatusUpdate('081234567890', 'PGN-123', 'diproses', 'Sedang dicek.');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://wa.test-gateway.id/send-message'
                && $request['number'] === '6281234567890'
                && str_contains($request['message'], 'PGN-123')
                && str_contains($request['message'], 'Diproses')
                && str_contains($request['message'], 'Sedang dicek.');
        });
    }

    public function test_send_status_update_custom_template(): void
    {
        NotificationTemplate::updateOrCreate(['type' => 'pengaduan_status_update'], ['content' => 'Update {kode_unik} jadi {status}. {catatan}']);
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendStatusUpdate('081234567890', 'PGN-123', 'diproses', 'Oke.');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request['message'] === 'Update PGN-123 jadi Diproses. Catatan: Oke.' . "\n";
        });
    }

    public function test_send_to_group_admin(): void
    {
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendToGroupAdmin('PGN-123', 'Budi', 'siswa', 'Akademik', 'Nilai salah input.');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://wa.test-gateway.id/send-message'
                && $request['number'] === '120363xxxxxx@g.us'
                && str_contains($request['message'], 'PGN-123')
                && str_contains($request['message'], 'Budi')
                && str_contains($request['message'], 'Siswa')
                && str_contains($request['message'], 'Akademik')
                && str_contains($request['message'], 'Nilai salah input.');
        });
    }

    public function test_send_to_group_admin_custom_template(): void
    {
        NotificationTemplate::updateOrCreate(['type' => 'pengaduan_group_admin'], ['content' => 'Admin info: {kode_unik} - {nama} - {status} - {kategori} - {deskripsi}']);
        Http::fake([
            'https://wa.test-gateway.id/*' => Http::response(['status' => true], 200),
        ]);

        $result = $this->service->sendToGroupAdmin('PGN-123', 'Budi', 'siswa', 'Akademik', 'Nilai salah input.');
        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request['message'] === 'Admin info: PGN-123 - Budi - Siswa - Akademik - Nilai salah input.';
        });
    }
}
