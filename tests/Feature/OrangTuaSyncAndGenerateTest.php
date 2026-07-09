<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Pengaturan;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OrangTuaSyncAndGenerateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat user dengan role super_admin untuk otentikasi admin
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'roles' => [User::ROLE_SUPER_ADMIN],
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
            'tingkat' => 'X',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'jurusan' => 'Umum'
        ]);
    }

    /** @test */
    public function it_can_sync_data_orang_tua_from_siswa_without_parent_successfully()
    {
        // 1. Arrange: Buat siswa tanpa orang tua
        $siswa1 = Siswa::create([
            'nama_lengkap' => 'Siswa Test Satu',
            'nisn' => '1234567890',
            'nis' => '12345',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'ortu_user_id' => null,
            'no_hp_ortu' => '08123456789',
        ]);

        $siswa2 = Siswa::create([
            'nama_lengkap' => 'Siswa Test Dua',
            'nisn' => '5432109876',
            'nis' => '54321',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-02',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'ortu_user_id' => null,
            'no_hp_ortu' => '08123456788',
        ]);

        // Buat pengaturan domain
        Pengaturan::updateOrCreate(
            ['key' => 'website_lembaga'],
            ['value' => 'https://mansaba.sch.id']
        );

        // 2. Act: Panggil endpoint syncData via POST dengan login admin
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.orang-tua.sync'));

        // 3. Assert: Cek status & message response
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Pastikan akun user orang tua dibuat di database
        $this->assertDatabaseHas('users', [
            'username' => 'ortu.1234567890',
            'email' => 'ortu.1234567890@mansaba.sch.id',
            'name' => 'Wali Murid Siswa Test Satu',
            'role' => User::ROLE_ORANG_TUA,
            'no_hp' => '08123456789', // no_hp_ortu terisi dari generate
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'ortu.5432109876',
            'email' => 'ortu.5432109876@mansaba.sch.id',
            'name' => 'Wali Murid Siswa Test Dua',
            'role' => User::ROLE_ORANG_TUA,
        ]);

        // Pastikan ortu_user_id di update di tabel siswa
        $siswa1Fresh = $siswa1->fresh();
        $siswa2Fresh = $siswa2->fresh();

        $this->assertNotNull($siswa1Fresh->ortu_user_id);
        $this->assertNotNull($siswa2Fresh->ortu_user_id);

        // Pastikan relasi pivot siswa_ortu terbuat
        $this->assertDatabaseHas('siswa_ortu', [
            'siswa_id' => $siswa1->id,
            'ortu_user_id' => $siswa1Fresh->ortu_user_id
        ]);
        
        $this->assertDatabaseHas('siswa_ortu', [
            'siswa_id' => $siswa2->id,
            'ortu_user_id' => $siswa2Fresh->ortu_user_id
        ]);
    }

    /** @test */
    public function it_can_destroy_all_orang_tua_and_reset_relations()
    {
        // Arrange: Buat ortu, hubungkan ke siswa
        $ortu = User::factory()->create([
            'name' => 'Wali Murid Test',
            'role' => User::ROLE_ORANG_TUA,
            'roles' => [User::ROLE_ORANG_TUA],
        ]);

        $siswa = Siswa::create([
            'nama_lengkap' => 'Siswa Test',
            'nisn' => '9998887776',
            'nis' => '99988',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'ortu_user_id' => $ortu->id
        ]);

        $siswa->ortu()->sync([$ortu->id]);

        // Pastikan relasi awal terbentuk
        $this->assertDatabaseHas('siswa_ortu', [
            'siswa_id' => $siswa->id,
            'ortu_user_id' => $ortu->id
        ]);

        // Act: Panggil endpoint destroyAll via DELETE dengan login admin
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.orang-tua.destroy-all'));

        // Assert: Cek status & message response
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Pastikan user orang tua terhapus
        $this->assertDatabaseMissing('users', [
            'id' => $ortu->id
        ]);

        // Pastikan field ortu_user_id di table siswa di-reset menjadi null
        $this->assertDatabaseHas('siswa', [
            'id' => $siswa->id,
            'ortu_user_id' => null
        ]);

        // Pastikan relasi pivot dibersihkan
        $this->assertDatabaseMissing('siswa_ortu', [
            'siswa_id' => $siswa->id,
            'ortu_user_id' => $ortu->id
        ]);
    }

    /** @test */
    public function it_can_generate_ortu_massal_from_siswa_controller()
    {
        // 1. Arrange: Buat siswa tanpa orang tua
        $siswa = Siswa::create([
            'nama_lengkap' => 'Siswa Massal Test',
            'nisn' => '1234509876',
            'nis' => '12309',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-05-05',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'ortu_user_id' => null,
            'no_hp_ortu' => '08123456789'
        ]);

        // 2. Act: Ambil ID siswa
        $responseGetIds = $this->actingAs($this->admin)
            ->postJson(route('admin.siswa.generate-ortu-massal', ['get_ids' => 1]));

        $responseGetIds->assertStatus(200);
        $responseGetIds->assertJson([
            'success' => true,
        ]);
        
        $ids = $responseGetIds->json('ids');
        $this->assertContains($siswa->id, $ids);

        // 3. Act: Jalankan generate untuk ID tersebut
        $responseGenerate = $this->actingAs($this->admin)
            ->postJson(route('admin.siswa.generate-ortu-massal'), [
                'siswa_ids' => [$siswa->id]
            ]);

        $responseGenerate->assertStatus(200);
        $responseGenerate->assertJson([
            'success' => true,
        ]);

        // 4. Assert: Pastikan user orang tua dibuat dengan no_hp tersinkronisasi
        $this->assertDatabaseHas('users', [
            'username' => 'ortu.1234509876',
            'role' => User::ROLE_ORANG_TUA,
            'no_hp' => '08123456789'
        ]);

        $siswaFresh = $siswa->fresh();
        $this->assertNotNull($siswaFresh->ortu_user_id);
    }
}
