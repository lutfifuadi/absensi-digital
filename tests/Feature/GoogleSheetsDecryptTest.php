<?php

namespace Tests\Feature;

use App\Jobs\GoogleSheetsSyncJob;
use App\Models\GoogleSheetSetting;
use App\Models\User;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSheetsDecryptTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }

    public function test_case_1_save_google_sheets_setting_with_encrypted_credentials()
    {
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.pengaturan.google-sheets.update'), [
                'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
                'sheet_range' => 'Sheet1!A:Z',
                'credentials_json' => $credentials,
                'column_mapping' => json_encode(['nis' => 'NIS', 'nama_lengkap' => 'Nama Lengkap']),
            ]);

        $response->assertStatus(302);

        $setting = GoogleSheetSetting::first();
        $this->assertNotNull($setting);
        $this->assertEquals($credentials, $setting->credentials_json);
        $this->assertNotEquals($credentials, $setting->getRawOriginal('credentials_json'));
    }

    public function test_case_2_and_3_change_app_key_and_check_settings_page()
    {
        // 1. Simpan settings dengan APP_KEY lama
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
        ]);

        // 2. Ubah APP_KEY secara dinamis di runtime
        $newKey = 'base64:'.base64_encode(random_bytes(32));
        config(['app.key' => $newKey]);

        // Mengubah key pada Encrypter di runtime
        $encrypter = new Encrypter(
            base64_decode(substr($newKey, 7)),
            config('app.cipher')
        );
        $this->app->instance('encrypter', $encrypter);

        // 3. Akses halaman setting, pastikan HTTP 200 (tidak crash 500)
        $response = $this->actingAs($this->admin)
            ->get(route('admin.pengaturan.google-sheets.index'));

        $response->assertStatus(200);

        // 4. Pastikan alert warning ada di HTML render
        $response->assertSee('Kunci aplikasi (APP_KEY) sistem telah berubah');
    }

    public function test_case_4_test_connection_returns_422_when_credentials_invalid()
    {
        // 1. Simpan settings dengan APP_KEY lama
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
        ]);

        // 2. Ubah APP_KEY secara dinamis di runtime
        $newKey = 'base64:'.base64_encode(random_bytes(32));
        config(['app.key' => $newKey]);

        $encrypter = new Encrypter(
            base64_decode(substr($newKey, 7)),
            config('app.cipher')
        );
        $this->app->instance('encrypter', $encrypter);

        // 3. Panggil endpoint test connection tanpa mengirim credentials_json baru
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pengaturan.google-sheets.test'), [
                'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
                'sheet_range' => 'Sheet1!A:Z',
            ]);

        // 4. Pastikan status 422 dan pesan error yang ramah
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Credentials JSON tidak valid atau rusak (kemungkinan karena perubahan kunci aplikasi APP_KEY). Silakan isi/upload ulang Service Account JSON Anda.',
        ]);
    }

    public function test_case_5_sync_now_rejected_when_credentials_invalid()
    {
        // 1. Simpan settings dengan APP_KEY lama
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
        ]);

        // 2. Ubah APP_KEY secara dinamis di runtime
        $newKey = 'base64:'.base64_encode(random_bytes(32));
        config(['app.key' => $newKey]);

        $encrypter = new Encrypter(
            base64_decode(substr($newKey, 7)),
            config('app.cipher')
        );
        $this->app->instance('encrypter', $encrypter);

        // 3. Panggil endpoint sync-now via JSON
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.pengaturan.google-sheets.sync-now'));

        // 4. Pastikan ditolak dengan error message yang ramah
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.',
        ]);

        // 5. Panggil endpoint sync-now via Web request (back with error)
        $responseWeb = $this->actingAs($this->admin)
            ->post(route('admin.pengaturan.google-sheets.sync-now'));

        $responseWeb->assertStatus(302);
        $responseWeb->assertSessionHas('sync_error', 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.');
    }

    public function test_case_6_siswa_page_sync_rejected_when_credentials_invalid()
    {
        // 1. Simpan settings dengan APP_KEY lama
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
            'column_mapping' => ['nis' => 'NIS'],
        ]);

        // 2. Ubah APP_KEY secara dinamis di runtime
        $newKey = 'base64:'.base64_encode(random_bytes(32));
        config(['app.key' => $newKey]);

        $encrypter = new Encrypter(
            base64_decode(substr($newKey, 7)),
            config('app.cipher')
        );
        $this->app->instance('encrypter', $encrypter);

        // 3. Panggil endpoint sync-google-sheet halaman siswa
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.siswa.sync-google-sheet'));

        // 4. Pastikan status 200 dan respon success: false dengan pesan error credentials rusak
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => false,
            'message' => 'Gagal menjadwalkan sinkronisasi: Credentials JSON rusak atau tidak dikonfigurasi. Silakan upload kembali Service Account JSON di halaman pengaturan.',
        ]);
    }

    public function test_case_7_sync_fails_gracefully_when_column_mapping_empty()
    {
        // 1. Simpan settings dengan credentials valid tapi column_mapping NULL/kosong
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        $setting = GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
            'column_mapping' => null, // KOSONG
            'last_sync_status' => 'in_progress',
            'last_sync_message' => 'Menjadwalkan...',
        ]);

        // 2. Jalankan Job secara manual (synchronous)
        $job = new GoogleSheetsSyncJob($setting->id, 0);
        $job->handle();

        // 3. Muat kembali data setting dari database
        $setting->refresh();

        // 4. Pastikan status di-update menjadi failed dengan pesan error mapping kolom
        $this->assertEquals('failed', $setting->last_sync_status);
        $this->assertStringContainsString('Tidak ada kolom yang dikenal di header sheet', $setting->last_sync_message);
    }

    public function test_case_8_settings_page_returns_json_when_requested_via_ajax()
    {
        // 1. Simpan settings dengan data status in_progress
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
            'last_sync_status' => 'in_progress',
            'last_sync_message' => '50/400 - Sedang memproses...',
        ]);

        // 2. Panggil endpoint index via GET JSON
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.pengaturan.google-sheets.index'));

        // 3. Pastikan status 200 dan mengembalikan struktur data JSON yang valid
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'last_sync_at',
            'last_sync_status',
            'status_badge_text',
            'last_sync_message',
            'sync_total_rows',
            'sync_processed_rows',
        ]);
        $response->assertJsonFragment([
            'last_sync_status' => 'in_progress',
            'last_sync_message' => '50/400 - Sedang memproses...',
        ]);
    }

    public function test_case_9_settings_page_renders_troubleshoot_panel_when_status_is_failed()
    {
        // 1. Simpan settings dengan data status failed dan pesan error mapping kolom
        $credentials = json_encode(['client_email' => 'test@example.com', 'private_key' => 'some_key']);
        GoogleSheetSetting::create([
            'spreadsheet_id' => '1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgVE2upms',
            'sheet_range' => 'Sheet1!A:Z',
            'credentials_json' => $credentials,
            'is_active' => true,
            'last_sync_status' => 'failed',
            'last_sync_message' => 'Sinkronisasi gagal: Mapping kolom belum dikonfigurasi.',
        ]);

        // 2. Panggil endpoint index via GET normal
        $response = $this->actingAs($this->admin)
            ->get(route('admin.pengaturan.google-sheets.index'));

        // 3. Pastikan status 200
        $response->assertStatus(200);

        // 4. Pastikan teks Troubleshooting Panel dan instruksi langkah perbaikan ter-render di HTML
        $response->assertSee('Panduan Pemecahan Masalah');
        $response->assertSee('Konfigurasi Mapping Kolom Kosong');
        $response->assertSee('Gulir ke bagian <b>Mapping Kolom (JSON)</b>', false);
    }
}
