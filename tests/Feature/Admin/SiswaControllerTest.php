<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\Pengaturan;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class SiswaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;
    protected $kelas;
    protected $mockGoogleDriveService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_test'
        ]);

        Pengaturan::create([
            'key' => 'website_lembaga',
            'value' => 'madrasah.sch.id',
            'group' => 'umum'
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => '10',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->mockGoogleDriveService = Mockery::mock(GoogleDriveService::class);
        $this->app->instance(GoogleDriveService::class, $this->mockGoogleDriveService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_siswa_with_photo()
    {
        $file = UploadedFile::fake()->image('siswa.jpg');

        $this->mockGoogleDriveService->shouldReceive('uploadPhoto')
            ->once()
            ->with(Mockery::on(function ($uploadedFile) use ($file) {
                return $uploadedFile->getClientOriginalName() === $file->getClientOriginalName();
            }))
            ->andReturn('new_google_drive_file_id_123');

        $siswaData = [
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama_lengkap' => 'Ahmad Siswa Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'alamat' => 'Jl. Merdeka No. 1',
            'no_hp' => '08123456789',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'foto' => $file,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.siswa.store'), $siswaData);

        $response->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345678',
            'nama_lengkap' => 'Ahmad Siswa Test',
            'foto' => 'new_google_drive_file_id_123',
        ]);
    }

    public function test_update_siswa_with_new_photo()
    {
        $user = User::factory()->create([
            'name' => 'Ahmad Asli',
            'username' => '0012345679',
            'role' => User::ROLE_SISWA,
        ]);

        $siswa = Siswa::create([
            'user_id' => $user->id,
            'nis' => '12346',
            'nisn' => '0012345679',
            'nama_lengkap' => 'Ahmad Asli',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'foto' => 'old_google_drive_file_id_whose_length_is_more_than_thirty_characters_1234567890',
        ]);

        $file = UploadedFile::fake()->image('siswa_updated.jpg');

        $this->mockGoogleDriveService->shouldReceive('uploadPhoto')
            ->once()
            ->with(
                Mockery::on(function ($uploadedFile) use ($file) {
                    return $uploadedFile->getClientOriginalName() === $file->getClientOriginalName();
                }),
                'old_google_drive_file_id_whose_length_is_more_than_thirty_characters_1234567890'
            )
            ->andReturn('new_google_drive_file_id_999');

        $updateData = [
            'nis' => '12346',
            'nisn' => '0012345679',
            'nama_lengkap' => 'Ahmad Diperbarui',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'alamat' => 'Jl. Merdeka No. 2',
            'no_hp' => '08123456789',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'foto' => $file,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.siswa.update', $siswa->id), $updateData);

        $response->assertRedirect(route('admin.siswa.index'));

        $this->assertDatabaseHas('siswa', [
            'id' => $siswa->id,
            'nama_lengkap' => 'Ahmad Diperbarui',
            'foto' => 'new_google_drive_file_id_999',
        ]);
    }
}
