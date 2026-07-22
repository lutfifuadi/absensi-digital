<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pengaturan;
use App\Models\WaAutoreplyKeyword;
use App\Models\NotificationTemplate;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Support\Carbon;

class WhatsAppAutoreplyWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup setting default
        Pengaturan::create(['key' => 'wa_autoreply_webhook_token', 'value' => 'rahasia123']);
        Pengaturan::create(['key' => 'wa_autoreply_enabled', 'value' => 'Ya']);
        Pengaturan::create(['key' => 'wa_autoreply_sender', 'value' => '62811111111']);
        Pengaturan::create(['key' => 'wa_nomor_notifikasi', 'value' => '62822222222']);
        Pengaturan::create(['key' => 'link_portal_ortu', 'value' => 'https://portal.ortu.test']);

        // Seed templates
        NotificationTemplate::create([
            'type' => 'autoreply_bantuan',
            'content' => 'Butuh bantuan? Gunakan keyword: #absen, #rekap, #link'
        ]);

        NotificationTemplate::create([
            'type' => 'autoreply_absen',
            'content' => 'Halo {nama}, tanggal {tanggal} status Anda: {status} jam {waktu}. Link: {link_portal}'
        ]);

        NotificationTemplate::create([
            'type' => 'autoreply_rekap',
            'content' => 'Halo {nama}, berikut rekap mingguan Anda: {rekap_detail}'
        ]);

        NotificationTemplate::create([
            'type' => 'autoreply_nomor_tak_dikenal',
            'content' => 'Nomor Anda tidak dikenal.'
        ]);

        // Buat Tahun Akademik
        $ta = \App\Models\TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true
        ]);

        // Buat Kelas
        $kelas = \App\Models\Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $ta->id
        ]);
    }

    public function test_webhook_token_validation()
    {
        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=salah', [
            'sender' => '628123456789',
            'message' => 'bantuan'
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_autoreply_disabled()
    {
        Pengaturan::where('key', 'wa_autoreply_enabled')->update(['value' => 'Tidak']);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => 'bantuan'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Autoreply dinonaktifkan.']);
    }

    public function test_webhook_prevent_infinite_loop()
    {
        Queue::fake();

        // Kirim dari nomor bot
        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62811111111',
            'message' => 'bantuan'
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => 'Pesan berasal dari nomor bot sendiri, diabaikan untuk mencegah infinite loop.']);
    }

    public function test_webhook_unauthenticated_keyword_bantuan()
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62899999999',
            'message' => 'apapun'
        ]);

        $response->assertStatus(200);
    }

    public function test_webhook_authenticated_keyword_absen_not_found()
    {
        Queue::fake();

        // Daftarkan keyword #absen butuh validasi
        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Kirim dari nomor ortu yang tidak terdaftar
        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62899999999',
            'message' => '#absen'
        ]);

        $response->assertStatus(200);
    }

    public function test_webhook_authenticated_keyword_absen_success()
    {
        Queue::fake();

        // Daftarkan keyword #absen butuh validasi
        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Buat siswa terdaftar
        $siswa = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'no_hp_ortu' => '08123456789',
            'nis' => '12345',
            'nisn' => '1234567890',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => 1,
            'tahun_akademik_id' => 1,
            'user_id' => \App\Models\User::factory()->create()->id
        ]);

        // Absen hari ini
        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => 1,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '07:15',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789', // Pengirim dalam format internasional
            'message' => '#absen'
        ]);

        $response->assertStatus(200);
    }

    public function test_webhook_authenticated_keyword_absen_success_using_from_parameter()
    {
        Queue::fake();

        // Daftarkan keyword #absen butuh validasi
        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Buat siswa terdaftar
        $siswa = Siswa::create([
            'nama_lengkap' => 'Budi Santoso',
            'no_hp_ortu' => '08123456789',
            'nis' => '12345',
            'nisn' => '1234567890',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => 1,
            'tahun_akademik_id' => 1,
            'user_id' => \App\Models\User::factory()->create()->id
        ]);

        // Absen hari ini
        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => 1,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '07:15',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        // Menggunakan parameter 'from' alih-alih 'sender'
        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'from' => '628123456789',
            'message' => '#absen'
        ]);

        $response->assertStatus(200);
    }
}
