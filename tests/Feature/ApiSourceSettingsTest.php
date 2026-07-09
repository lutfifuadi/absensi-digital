<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\Pengaturan;
use App\Jobs\SyncMasterDataJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiSourceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected TahunAkademik $tahunAkademik;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'username' => 'super_admin_test'
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->startOfYear(),
            'tanggal_selesai' => now()->endOfYear(),
            'is_aktif' => true
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum',
            'tingkat' => 'X'
        ]);

        Pengaturan::updateOrCreate(['key' => 'master_db_api_url'], ['value' => 'https://api.test/siswa', 'group' => 'api_source']);
        Pengaturan::updateOrCreate(['key' => 'master_db_api_key'], ['value' => 'test-api-key', 'group' => 'api_source']);
        Pengaturan::updateOrCreate(['key' => 'master_db_sync_enabled'], ['value' => 'Ya', 'group' => 'api_source']);
    }

    public function test_super_admin_can_access_pengaturan_with_ta_and_class_data()
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.pengaturan.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('tahunAkademikList');
        $response->assertViewHas('kelasList');
    }

    public function test_sync_now_dispatches_sync_master_data_job_with_forced_parameters()
    {
        Queue::fake();

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('admin.pengaturan.api-source.sync-now'), [
                'tahun_akademik_id' => $this->tahunAkademik->id,
                'kelas_id' => $this->kelas->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Sinkronisasi data master telah dijadwalkan dan akan diproses di latar belakang.'
        ]);

        Queue::assertPushed(SyncMasterDataJob::class, function ($job) {
            // Kita perlu memeriksa properti terproteksi/private atau menggunakan reflection,
            // tetapi cara paling mudah adalah dengan merefleksikan property kelas
            $ref = new \ReflectionClass($job);
            $tahunAkademikIdProp = $ref->getProperty('tahunAkademikId');
            $tahunAkademikIdProp->setAccessible(true);
            $kelasIdProp = $ref->getProperty('kelasId');
            $kelasIdProp->setAccessible(true);

            return $tahunAkademikIdProp->getValue($job) == $this->tahunAkademik->id
                && $kelasIdProp->getValue($job) == $this->kelas->id;
        });
    }

    public function test_sync_master_siswa_command_with_forced_options_overrides_values()
    {
        Http::fake([
            'https://api.test/siswa' => Http::response([
                'data' => [
                    [
                        'nisn' => '1234567890',
                        'nis' => '12345',
                        'nama_lengkap' => 'Siswa Test Forced',
                        'status' => 'lulus',
                        'daftar_ulang_selesai' => true,
                        'wawancara_selesai' => true,
                        'jenis_kelamin' => 'L',
                        'tempat_lahir' => 'Bandung',
                        'tanggal_lahir' => '2010-02-02',
                        'nama_kelas' => 'XII-A', // Akan kita override
                        'tahun_akademik_nama' => '2024-2025', // Akan kita override
                    ]
                ]
            ], 200)
        ]);

        // Buat kelas & TA tujuan override
        $targetTa = TahunAkademik::create([
            'nama' => '2027-2028',
            'semester' => 'Ganjil',
            'tanggal_mulai' => now()->addYears(2)->startOfYear(),
            'tanggal_selesai' => now()->addYears(2)->endOfYear(),
            'is_aktif' => false
        ]);

        $targetKelas = Kelas::create([
            'nama' => 'X-FORCED',
            'tahun_akademik_id' => $targetTa->id,
            'jurusan' => 'Umum',
            'tingkat' => 'X'
        ]);

        // Pastikan setting URL API di database cocok dengan endpoint yang difake
        Pengaturan::updateOrCreate(['key' => 'master_db_api_url'], ['value' => 'https://api.test/siswa', 'group' => 'api_source']);

        // Jalankan Artisan Command dengan options
        $exitCode = Artisan::call('sync:master-siswa', [
            '--tahun_akademik_id' => $targetTa->id,
            '--kelas_id' => $targetKelas->id,
        ]);

        $output = Artisan::output();
        if ($exitCode !== 0 || empty(\App\Models\Siswa::where('nisn', '1234567890')->first())) {
            echo "\nArtisan Exit Code: " . $exitCode;
            echo "\nArtisan Output: " . $output;
        }

        $this->assertEquals(0, $exitCode);

        // Cari siswa yang tersinkronisasi
        $siswa = \App\Models\Siswa::where('nisn', '1234567890')->first();
        $this->assertNotNull($siswa);
        $this->assertEquals($targetTa->id, $siswa->tahun_akademik_id);
        $this->assertEquals($targetKelas->id, $siswa->kelas_id);
    }
}
