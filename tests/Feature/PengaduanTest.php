<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\LogPengaduan;
use App\Models\User;
use App\Services\WhatsAppValidatorService;
use App\Services\WhatsAppPengaduanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class PengaduanTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock WhatsAppValidatorService — default return true
        $waValidatorMock = Mockery::mock(WhatsAppValidatorService::class);
        $waValidatorMock->shouldReceive('validateNomor')
            ->andReturn(true);
        $this->app->instance(WhatsAppValidatorService::class, $waValidatorMock);

        // Mock WhatsAppPengaduanService — supaya jobs sync tidak beneran kirim WA
        $waServiceMock = Mockery::mock(WhatsAppPengaduanService::class);
        $waServiceMock->shouldReceive('sendKodeUnik')
            ->andReturn(true);
        $waServiceMock->shouldReceive('sendToGroupAdmin')
            ->andReturn(true);
        $waServiceMock->shouldReceive('sendStatusUpdate')
            ->andReturn(true);
        $this->app->instance(WhatsAppPengaduanService::class, $waServiceMock);

        // Buat admin user
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);
    }

    // ──────────────────── API PUBLIK ────────────────────

    /**
     * Test submit pengaduan sukses → 201.
     *
     * NOTE: Saat ini menghadapi BUG-001 (log_pengaduan.status_dari NOT NULL).
     * Ketika bug diperbaiki, ubah assertion menjadi assertStatus(201).
     */
    public function test_submit_pengaduan_success(): void
    {
        $payload = [
            'nama_lengkap'   => 'Ahmad Fauzi',
            'status_pelapor' => 'siswa',
            'kategori'       => 'Nama tidak sesuai',
            'deskripsi'      => 'Nama saya di data sekolah tertulis Ahmad Fauzi, seharusnya Ahmad Fauzi Rahman.',
            'nomor_wa'       => '081234567890',
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        // BUG-001: log_pengaduan.status_dari NOT NULL menyebabkan 500.
        // Saat bug diperbaiki, ubah assertion menjadi assertStatus(201)
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Pengaduan berhasil dikirim. Silakan catat kode unik untuk mengecek status.',
            ]);
    }

    /**
     * Test submit dengan nomor WA tidak valid → 422.
     */
    public function test_submit_with_invalid_wa_number(): void
    {
        // Override mock untuk return false
        $waValidatorMock = Mockery::mock(WhatsAppValidatorService::class);
        $waValidatorMock->shouldReceive('validateNomor')
            ->andReturn(false);
        $this->app->instance(WhatsAppValidatorService::class, $waValidatorMock);

        $payload = [
            'nama_lengkap'   => 'Budi Santoso',
            'status_pelapor' => 'siswa',
            'kategori'       => 'Kelas tidak sesuai',
            'deskripsi'      => 'Kelas saya X IPA 1, tapi di data tertulis X IPA 2.',
            'nomor_wa'       => '081111111111',
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nomor_wa']);
    }

    /**
     * Test submit tanpa field required → 422.
     */
    public function test_submit_missing_required_fields(): void
    {
        // Kirim data kosong
        $response = $this->postJson('/api/pengaduan', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'nama_lengkap',
                'status_pelapor',
                'kategori',
                'deskripsi',
                'nomor_wa',
            ]);
    }

    /**
     * Test submit dengan deskripsi terlalu pendek → 422.
     */
    public function test_submit_description_too_short(): void
    {
        $payload = [
            'nama_lengkap'   => 'Citra Dewi',
            'status_pelapor' => 'orang_tua',
            'kategori'       => 'Data orang tua',
            'deskripsi'      => 'Salah', // min 10 karakter
            'nomor_wa'       => '081234567890',
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['deskripsi']);
    }

    /**
     * Test submit dengan nomor WA format salah → 422.
     */
    public function test_submit_invalid_wa_format(): void
    {
        $payload = [
            'nama_lengkap'   => 'Doni Prasetyo',
            'status_pelapor' => 'siswa',
            'kategori'       => 'NIS salah',
            'deskripsi'      => 'NIS saya 12345, tapi di data tertulis 67890.',
            'nomor_wa'       => '12345', // tidak diawali 08, kurang dari 10 digit
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nomor_wa']);
    }

    /**
     * Test submit dengan status_pelapor tidak valid → 422.
     */
    public function test_submit_invalid_status_pelapor(): void
    {
        $payload = [
            'nama_lengkap'   => 'Eko Prasetyo',
            'status_pelapor' => 'guru', // hanya 'siswa' atau 'orang_tua'
            'kategori'       => 'Data tidak valid',
            'deskripsi'      => 'Test deskripsi untuk pengaduan data tidak valid.',
            'nomor_wa'       => '081234567890',
        ];

        $response = $this->postJson('/api/pengaduan', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status_pelapor']);
    }

    // ──────────────────── CEK STATUS ────────────────────

    /**
     * Test validation of WhatsApp number via AJAX.
     */
    public function test_cek_wa_validation_success(): void
    {
        $response = $this->getJson('/api/pengaduan/cek-wa?nomor_wa=081234567890');

        $response->assertStatus(200)
            ->assertJson([
                'valid'   => true,
                'message' => 'Nomor WhatsApp valid dan aktif.',
            ]);
    }

    /**
     * Test validation of WhatsApp number when validator service returns false.
     */
    public function test_cek_wa_validation_fails_not_registered(): void
    {
        // Override mock untuk return false
        $waValidatorMock = Mockery::mock(WhatsAppValidatorService::class);
        $waValidatorMock->shouldReceive('validateNomor')
            ->andReturn(false);
        $this->app->instance(WhatsAppValidatorService::class, $waValidatorMock);

        $response = $this->getJson('/api/pengaduan/cek-wa?nomor_wa=081111111111');

        $response->assertStatus(200)
            ->assertJson([
                'valid'   => false,
                'message' => 'Nomor WhatsApp tidak terdaftar atau tidak aktif.',
            ]);
    }

    /**
     * Test validation of WhatsApp number with invalid regex format.
     */
    public function test_cek_wa_validation_invalid_format(): void
    {
        $response = $this->getJson('/api/pengaduan/cek-wa?nomor_wa=12345');

        $response->assertStatus(422)
            ->assertJson([
                'valid'   => false,
                'message' => 'Nomor WhatsApp tidak valid. Format harus diawali dengan 08 dan memiliki panjang 10-15 digit.',
            ]);
    }

    /**
     * Test validation of WhatsApp number when missing parameter.
     */
    public function test_cek_wa_validation_missing_parameter(): void
    {
        $response = $this->getJson('/api/pengaduan/cek-wa');

        $response->assertStatus(422)
            ->assertJson([
                'valid'   => false,
                'message' => 'Nomor WhatsApp wajib diisi.',
            ]);
    }

    // ──────────────────── CEK STATUS ────────────────────

    /**
     * Test cek status dengan kode unik valid → 200 + data.
     */
    public function test_cek_status_with_valid_kode(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->getJson('/api/pengaduan/cek?kode=' . $pengaduan->kode_unik);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'kode_unik',
                    'nama_lengkap',
                    'status_pelapor',
                    'kategori',
                    'deskripsi',
                    'status',
                    'status_label',
                    'status_color',
                    'created_at',
                    'updated_at',
                ],
                'logs',
            ]);

        $response->assertJsonPath('data.kode_unik', $pengaduan->kode_unik);
        $response->assertJsonPath('data.status', 'baru');
    }

    /**
     * Test cek status dengan kode unik tidak ditemukan → 404.
     */
    public function test_cek_status_with_invalid_kode(): void
    {
        $response = $this->getJson('/api/pengaduan/cek?kode=PGN-20260716-999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Pengaduan tidak ditemukan.',
            ]);
    }

    /**
     * Test cek status tanpa parameter kode → 422.
     */
    public function test_cek_status_without_kode(): void
    {
        $response = $this->getJson('/api/pengaduan/cek');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['kode']);
    }

    /**
     * Test cek status dengan kode unik yang sudah diproses - melihat log.
     */
    public function test_cek_status_shows_logs(): void
    {
        $pengaduan = Pengaduan::factory()->diproses()->create();

        // Buat log tambahan
        LogPengaduan::factory()->forPengaduan($pengaduan)->create([
            'status_dari' => 'baru',
            'status_ke'   => 'diproses',
            'diubah_oleh' => 'admin:1',
        ]);

        $response = $this->getJson('/api/pengaduan/cek?kode=' . $pengaduan->kode_unik);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['kode_unik', 'status'],
            'logs' => [
                '*' => ['status_dari', 'status_ke', 'catatan', 'diubah_oleh', 'created_at'],
            ],
        ]);
    }

    // ──────────────────── ADMIN ────────────────────

    /**
     * Test akses admin tanpa login → 302 (redirect ke login oleh auth middleware).
     */
    public function test_admin_access_without_login(): void
    {
        $response = $this->get('/admin/pengaduan');

        $response->assertStatus(302);
    }

    /**
     * Test akses admin sebagai super_admin → 200.
     */
    public function test_admin_access_as_super_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/pengaduan');

        $response->assertStatus(200);
    }

    /**
     * Test akses admin sebagai user dengan role tidak berhak → 403.
     */
    public function test_admin_access_as_wrong_role(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_GURU, // bukan super_admin, admin_sekolah, atau operator
        ]);

        $response = $this->actingAs($user)
            ->get('/admin/pengaduan');

        $response->assertStatus(403);
    }

    /**
     * Test admin lihat daftar pengaduan.
     */
    public function test_admin_list_pengaduan(): void
    {
        Pengaduan::factory(5)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/pengaduan');

        $response->assertStatus(200);
        $response->assertViewHas('pengaduan');
        $response->assertViewHas('stats');
    }

    /**
     * Test admin lihat daftar dengan filter status.
     */
    public function test_admin_list_pengaduan_with_status_filter(): void
    {
        Pengaduan::factory(3)->baru()->create();
        Pengaduan::factory(2)->diproses()->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/pengaduan?status=baru');

        $response->assertStatus(200);
    }

    /**
     * Test admin lihat detail pengaduan.
     */
    public function test_admin_show_pengaduan(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->get("/admin/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
        $response->assertViewHas('pengaduan');
        $response->assertViewHas('availableStatuses');
    }

    /**
     * Test admin update status dari 'baru' ke 'diproses' via updateStatus().
     */
    public function test_admin_update_status_to_diproses(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'diproses',
                'catatan' => 'Sedang diverifikasi.',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $pengaduan->refresh();
        $this->assertEquals('diproses', $pengaduan->status);
        $this->assertNull($pengaduan->verified_at); // verified_at hanya untuk final status

        // Verifikasi log tercatat
        $this->assertDatabaseHas('log_pengaduan', [
            'pengaduan_id' => $pengaduan->id,
            'status_dari'  => 'baru',
            'status_ke'    => 'diproses',
        ]);
    }

    /**
     * Test admin update status dari 'baru' → 'diproses' → 'selesai' (flow valid).
     */
    public function test_admin_update_status_to_selesai_valid_flow(): void
    {
        // Step 1: Buat pengaduan baru
        $pengaduan = Pengaduan::factory()->baru()->create([
            'catatan_admin' => null,
        ]);

        // Step 2: Update ke diproses
        $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'diproses',
                'catatan' => 'Sedang diverifikasi.',
            ]);

        $pengaduan->refresh();
        $this->assertEquals('diproses', $pengaduan->status);

        // Step 3: Update ke selesai
        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'selesai',
                'catatan' => 'Data sudah diperbaiki.',
            ]);

        $response->assertSessionHas('success');
        $pengaduan->refresh();
        $this->assertEquals('selesai', $pengaduan->status);
        $this->assertNotNull($pengaduan->verified_at);
    }

    /**
     * Test admin update status tanpa catatan untuk status selesai → harus error.
     */
    public function test_admin_update_status_without_catatan_for_selesai(): void
    {
        $pengaduan = Pengaduan::factory()->diproses()->create();

        // Catatan required untuk status selesai
        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status' => 'selesai',
                // catatan tidak diisi
            ]);

        $response->assertSessionHasErrors(['catatan']);
        $pengaduan->refresh();
        $this->assertEquals('diproses', $pengaduan->status); // status tidak berubah
    }

    /**
     * Test admin update status tanpa catatan untuk status ditolak → harus error.
     */
    public function test_admin_update_status_without_catatan_for_ditolak(): void
    {
        $pengaduan = Pengaduan::factory()->diproses()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status' => 'ditolak',
                // catatan tidak diisi
            ]);

        $response->assertSessionHasErrors(['catatan']);
        $pengaduan->refresh();
        $this->assertEquals('diproses', $pengaduan->status);
    }

    /**
     * Test admin update status tidak valid: dari 'baru' langsung ke 'selesai'.
     */
    public function test_admin_update_status_invalid_transition_baru_to_selesai(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'selesai',
                'catatan' => 'Data sudah diperbaiki.',
            ]);

        $response->assertSessionHas('error');
        $pengaduan->refresh();
        $this->assertEquals('baru', $pengaduan->status); // status tidak berubah
    }

    /**
     * Test admin update status dari 'baru' ke 'ditolak' via updateStatus().
     * BUG-003: Transisi baru → ditolak sekarang valid.
     */
    public function test_admin_update_status_baru_to_ditolak_web(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'ditolak',
                'catatan' => 'Data sudah valid.',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $pengaduan->refresh();
        $this->assertEquals('ditolak', $pengaduan->status);
        $this->assertNotNull($pengaduan->verified_at);
    }

    /**
     * Test admin update status dari status final 'selesai' → tidak bisa.
     */
    public function test_admin_update_status_from_final_status(): void
    {
        $pengaduan = Pengaduan::factory()->selesai()->create();

        $response = $this->actingAs($this->admin)
            ->post("/admin/pengaduan/{$pengaduan->id}/update-status", [
                'status'  => 'diproses',
                'catatan' => 'Mau diproses ulang.',
            ]);

        $response->assertSessionHas('error');
        $pengaduan->refresh();
        $this->assertEquals('selesai', $pengaduan->status);
    }

    /**
     * Test admin update status via API/put (UpdatePengaduanRequest) — BUG-002.
     *
     * NOTE: Endpoint menggunakan \DB::transaction() tetapi 'DB' alias tidak
     * terdaftar di config/app.php, menyebabkan error "Class DB not found".
     * Saat bug diperbaiki, ubah assertion menjadi assertStatus(200).
     */
    public function test_admin_api_update_baru_to_diproses(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/pengaduan/{$pengaduan->id}", [
                'status'  => 'diproses',
                'catatan' => 'Sedang diverifikasi.',
            ]);

        // BUG-002: \DB facade alias tidak terdaftar → 500 (sudah diperbaiki)
        $response->assertStatus(200);
    }

    /**
     * Test admin API update status tidak valid: baru → selesai.
     * Di method `update()` (API), dari 'baru' tidak bisa ke 'selesai'.
     */
    public function test_admin_api_update_invalid_transition(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/pengaduan/{$pengaduan->id}", [
                'status'  => 'selesai',
                'catatan' => 'Data sudah diperbaiki.',
            ]);

        $response->assertStatus(422)
            ->assertJson(['message' => "Tidak dapat mengubah status dari 'baru' ke 'selesai'."]);

        $pengaduan->refresh();
        $this->assertEquals('baru', $pengaduan->status);
    }

    /**
     * Test admin API update dengan status tidak valid (enum).
     */
    public function test_admin_api_update_invalid_status_value(): void
    {
        $pengaduan = Pengaduan::factory()->baru()->create();

        $response = $this->actingAs($this->admin)
            ->putJson("/admin/pengaduan/{$pengaduan->id}", [
                'status'  => 'invalid_status',
                'catatan' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Test admin bisa melihat detail pengaduan dengan logs.
     */
    public function test_admin_show_pengaduan_with_logs(): void
    {
        $pengaduan = Pengaduan::factory()->diproses()->create();

        // Tambah log — gunakan status_dari valid karena kolom NOT NULL
        LogPengaduan::create([
            'pengaduan_id' => $pengaduan->id,
            'status_dari'  => 'baru',
            'status_ke'    => 'diproses',
            'catatan'      => 'Pengaduan baru dibuat.',
            'diubah_oleh'  => 'sistem',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/pengaduan/{$pengaduan->id}");

        $response->assertStatus(200);
        $response->assertViewHas('availableStatuses');

        // Untuk status 'diproses', availableStatuses ['selesai', 'ditolak']
        $availableStatuses = $response->viewData('availableStatuses');
        $this->assertContains('selesai', $availableStatuses);
        $this->assertContains('ditolak', $availableStatuses);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
