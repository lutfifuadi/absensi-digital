<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pengaturan;
use App\Models\WaAutoreplyKeyword;
use App\Models\NotificationTemplate;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendWhatsAppMessage;
use Illuminate\Support\Carbon;

class WhatsAppAutoreplyTest extends TestCase
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

        // Buat Tahun Akademik default untuk relasi siswa
        $ta = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true
        ]);

        // Buat Kelas default untuk relasi siswa
        Kelas::create([
            'nama' => 'X RPL 1',
            'tingkat' => 'X',
            'jurusan' => 'RPL',
            'tahun_akademik_id' => $ta->id
        ]);
    }

    /**
     * 1. Validasi Token Webhook
     * Pastikan request dengan token salah diblok (HTTP 403) dan token benar diizinkan (HTTP 200).
     */
    public function test_webhook_token_validation(): void
    {
        // Token salah -> 403
        $responseSalah = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=salah', [
            'sender' => '628123456789',
            'message' => 'bantuan'
        ]);
        $responseSalah->assertStatus(403);

        // Token benar -> 200
        $responseBenar = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => 'bantuan'
        ]);
        $responseBenar->assertStatus(200);
    }

    /**
     * 2. Validasi Global Status
     * Pastikan ketika wa_autoreply_enabled = Tidak, sistem langsung return status false dengan HTTP 200 OK.
     */
    public function test_webhook_autoreply_disabled(): void
    {
        Pengaturan::where('key', 'wa_autoreply_enabled')->update(['value' => 'Tidak']);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => 'bantuan'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => false,
            'message' => 'Autoreply dinonaktifkan.'
        ]);
    }

    /**
     * 3. Pencegahan Infinite Loop
     * Kirim request dengan sender sama dengan nomor bot (wa_autoreply_sender atau wa_nomor_notifikasi),
     * pastikan sistem mem-bypass dan tidak memproses kirim pesan balik.
     */
    public function test_webhook_prevent_infinite_loop(): void
    {
        Queue::fake();

        // Coba dari wa_autoreply_sender
        $responseBot = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62811111111',
            'message' => 'bantuan'
        ]);
        $responseBot->assertStatus(200);
        $responseBot->assertJsonFragment(['status' => false]);
        
        // Coba dari wa_nomor_notifikasi
        $responseNotif = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62822222222',
            'message' => 'bantuan'
        ]);
        $responseNotif->assertStatus(200);
        $responseNotif->assertJsonFragment(['status' => false]);

        // Pastikan tidak ada job yang dikirim
        Queue::assertNotPushed(SendWhatsAppMessage::class);
    }

    /**
     * 4. Pencocokan Keyword (Exact)
     * Kirim keyword #absen (exact match, abaikan case sensitif/str_to_lower),
     * pastikan sistem mendeteksi keyword tersebut.
     */
    public function test_webhook_exact_keyword_matching(): void
    {
        Queue::fake();

        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => false,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Cek case insensitive: #ABSEN
        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => '#ABSEN'
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return $job->number === '628123456789' && str_contains($job->message, 'tanggal');
        });
    }

    /**
     * 5. Pencocokan Keyword (Contains)
     * Kirim pesan "tolong info rekap dong" (mengandung keyword rekap),
     * pastikan sistem mendeteksi keyword rekap.
     */
    public function test_webhook_contains_keyword_matching(): void
    {
        Queue::fake();

        WaAutoreplyKeyword::create([
            'keyword' => 'rekap',
            'match_type' => 'contains',
            'is_validation_required' => false,
            'is_active' => true,
            'notification_template_type' => 'autoreply_rekap'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => 'tolong info rekap dong'
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return $job->number === '628123456789' && str_contains($job->message, 'rekap mingguan Anda');
        });
    }

    /**
     * 6. Keyword Default (Bantuan)
     * Kirim keyword acak (misal "halo halo"), pastikan sistem me-fallback ke menu bantuan.
     */
    public function test_webhook_keyword_fallback_to_bantuan(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => 'halo halo'
        ]);

        $response->assertStatus(200);
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return $job->number === '628123456789' && str_contains($job->message, 'Butuh bantuan? Gunakan keyword');
        });
    }

    /**
     * 7. Akses Nomor HP Terdaftar (Sukses)
     * Buat user siswa dengan nomor HP ortu tertentu, kirim webhook dari nomor tersebut dengan keyword #absen.
     * Pastikan sistem me-render pesan absensi siswa bersangkutan secara dinamis dan mendispatch job SendWhatsAppMessage dengan data siswa tersebut.
     */
    public function test_webhook_registered_number_success(): void
    {
        Queue::fake();

        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Buat siswa terdaftar
        $user = User::factory()->create();
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
            'user_id' => $user->id
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
            'sender' => '628123456789',
            'message' => '#absen'
        ]);

        $response->assertStatus(200);
        
        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($siswa) {
            return $job->number === '628123456789' 
                && $job->siswaId === $siswa->id
                && str_contains($job->message, 'Budi Santoso')
                && str_contains(strtolower($job->message), 'hadir')
                && str_contains($job->message, '07:15');
        });
    }

    /**
     * 8. Multi-Siswa untuk Satu Nomor HP
     * Buat 2 siswa dengan nomor HP ortu yang sama. Kirim request webhook.
     * Pastikan sistem mendispatch 2 Job (1 untuk masing-masing anak).
     */
    public function test_webhook_multi_student_same_number(): void
    {
        Queue::fake();

        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        // Siswa 1
        $user1 = User::factory()->create();
        $siswa1 = Siswa::create([
            'nama_lengkap' => 'Anak Pertama',
            'no_hp_ortu' => '08123456789',
            'nis' => '11111',
            'nisn' => '1111111111',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => 1,
            'tahun_akademik_id' => 1,
            'user_id' => $user1->id
        ]);

        // Siswa 2
        $user2 = User::factory()->create();
        $siswa2 = Siswa::create([
            'nama_lengkap' => 'Anak Kedua',
            'no_hp_ortu' => '08123456789',
            'nis' => '22222',
            'nisn' => '2222222222',
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2012-02-02',
            'kelas_id' => 1,
            'tahun_akademik_id' => 1,
            'user_id' => $user2->id
        ]);

        // Absen Anak Pertama hari ini
        AbsensiSiswa::create([
            'siswa_id' => $siswa1->id,
            'kelas_id' => 1,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '07:05',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        // Absen Anak Kedua hari ini
        AbsensiSiswa::create([
            'siswa_id' => $siswa2->id,
            'kelas_id' => 1,
            'tanggal' => Carbon::today(),
            'jam_masuk' => '07:12',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => '#absen'
        ]);

        $response->assertStatus(200);

        // Pastikan job dikirim sebanyak 2 kali (1 untuk Anak Pertama, 1 untuk Anak Kedua)
        Queue::assertPushed(SendWhatsAppMessage::class, 2);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($siswa1) {
            return $job->number === '628123456789' 
                && $job->siswaId === $siswa1->id
                && str_contains($job->message, 'Anak Pertama');
        });

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($siswa2) {
            return $job->number === '628123456789' 
                && $job->siswaId === $siswa2->id
                && str_contains($job->message, 'Anak Kedua');
        });
    }

    /**
     * 9. Akses Nomor HP Tidak Terdaftar (Gagal)
     * Kirim webhook dari nomor HP acak, pastikan sistem merespon dengan template autoreply_nomor_tak_dikenal.
     */
    public function test_webhook_unregistered_number_failed(): void
    {
        Queue::fake();

        WaAutoreplyKeyword::create([
            'keyword' => '#absen',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_absen'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628999999999', // nomor tidak terdaftar
            'message' => '#absen'
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return $job->number === '628999999999' 
                && str_contains($job->message, 'Nomor Anda tidak dikenal');
        });
    }

    /**
     * 10. Uji placeholder dinamis & rekap detail pada template dinamis
     */
    public function test_webhook_dynamic_placeholders_and_rekap(): void
    {
        Queue::fake();

        // Buat setting nama_lembaga
        Pengaturan::create(['key' => 'nama_lembaga', 'value' => 'SMK Negeri 1 Bandung']);

        // Update template autoreply_rekap dengan placeholder kustom
        NotificationTemplate::where('type', 'autoreply_rekap')->update([
            'content' => 'Lembaga: {lembaga}, Siswa: {nama}, Kelas: {kelas}, Jam Masuk Hari Ini: {jam_masuk}, Jam Pulang Hari Ini: {jam_pulang}, Rekap: {rekap_kehadiran}, Hadir: {total_hadir}, Terlambat: {total_terlambat}, Izin/Sakit: {total_izin_sakit}, Alpha: {total_alpha}'
        ]);

        WaAutoreplyKeyword::create([
            'keyword' => '#rekap',
            'match_type' => 'exact',
            'is_validation_required' => true,
            'is_active' => true,
            'notification_template_type' => 'autoreply_rekap'
        ]);

        // Buat siswa terdaftar
        $user = User::factory()->create();
        $siswa = Siswa::create([
            'nama_lengkap' => 'Ahmad Fauzi',
            'no_hp_ortu' => '08123456789',
            'nis' => '12346',
            'nisn' => '1234567891',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'kelas_id' => 1,
            'tahun_akademik_id' => 1,
            'user_id' => $user->id
        ]);

        // Cari awal minggu ini (Senin)
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Tambah absensi minggu ini:
        // Senin: HADIR (07:00)
        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => 1,
            'tanggal' => $startOfWeek->copy()->toDateString(),
            'jam_masuk' => '07:00',
            'jam_pulang' => '15:00',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        // Selasa: TERLAMBAT (07:45)
        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => 1,
            'tanggal' => $startOfWeek->copy()->addDays(1)->toDateString(),
            'jam_masuk' => '07:45',
            'status' => 'terlambat',
            'metode' => 'QR'
        ]);

        // Rabu: SAKIT
        AbsensiSiswa::create([
            'siswa_id' => $siswa->id,
            'kelas_id' => 1,
            'tanggal' => $startOfWeek->copy()->addDays(2)->toDateString(),
            'status' => 'sakit',
            'metode' => 'Manual'
        ]);

        // Hari ini (misal Kamis/Jumat) kita set absensi hari ini untuk test jam_masuk & jam_pulang
        // Gunakan $startOfWeek + 3 hari agar tidak bentrok jika Carbon::today() jatuh pada hari Senin/Selasa/Rabu yang sudah di-insert di atas
        $hariIni = $startOfWeek->copy()->addDays(3);
        Carbon::setTestNow($hariIni);

        AbsensiSiswa::updateOrCreate([
            'siswa_id' => $siswa->id,
            'tanggal' => $hariIni->toDateString(),
        ], [
            'kelas_id' => 1,
            'jam_masuk' => '07:10:00',
            'jam_pulang' => '16:00:00',
            'status' => 'hadir',
            'metode' => 'QR'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '628123456789',
            'message' => '#rekap'
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) use ($siswa) {
            return $job->number === '628123456789' 
                && $job->siswaId === $siswa->id
                && str_contains($job->message, 'Lembaga: SMK Negeri 1 Bandung')
                && str_contains($job->message, 'Siswa: Ahmad Fauzi')
                && str_contains($job->message, 'Kelas: X RPL 1')
                && str_contains($job->message, 'Jam Masuk Hari Ini: 07:10')
                && str_contains($job->message, 'Jam Pulang Hari Ini: 16:00')
                && str_contains($job->message, 'Hadir: ') // Ada info total_hadir
                && str_contains($job->message, 'Terlambat: 1') // total_terlambat = 1
                && str_contains($job->message, 'Izin/Sakit: 1'); // total_izin_sakit = 1 (sakit)
        });
    }

    /**
     * 11. Uji placeholder untuk template statis
     */
    public function test_webhook_static_placeholders(): void
    {
        Queue::fake();

        // Atur nama lembaga
        Pengaturan::create(['key' => 'nama_lembaga', 'value' => 'SMK Negeri 1 Bandung']);

        // Update template autoreply_bantuan
        NotificationTemplate::where('type', 'autoreply_bantuan')->update([
            'content' => 'Selamat datang di {lembaga}. Login: {portal_url}, Pengaduan: {link_pengaduan}'
        ]);

        $response = $this->postJson('/api/v1/webhook/whatsapp-autoreply?token=rahasia123', [
            'sender' => '62899999999',
            'message' => 'bantuan'
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendWhatsAppMessage::class, function ($job) {
            return $job->number === '62899999999' 
                && str_contains($job->message, 'Selamat datang di SMK Negeri 1 Bandung')
                && str_contains($job->message, 'Login: https://portal.ortu.test')
                && str_contains($job->message, '/pengaduan');
        });
    }
}
