<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\RiwayatKenaikanKelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\SiswaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AlumniTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdmin;

    protected $adminSekolah;

    protected $taAsal;

    protected $taTujuan;

    protected $kelasAsal;

    protected $kelasXiAsal;

    protected $kelasTujuan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
        ]);

        $this->taAsal = TahunAkademik::create([
            'nama' => '2024/2025',
            'semester' => 'Genap',
            'tanggal_mulai' => '2024-01-01',
            'tanggal_selesai' => '2024-06-30',
            'is_aktif' => false,
        ]);

        $this->taTujuan = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'Ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        // Kelas XII di TA Asal
        $this->kelasAsal = Kelas::create([
            'nama' => 'XII-IPA',
            'tingkat' => 'XII',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->taAsal->id,
        ]);

        // Kelas XI di TA Asal (untuk testing orang tua dengan anak lain yang masih aktif)
        $this->kelasXiAsal = Kelas::create([
            'nama' => 'XI-IPA',
            'tingkat' => 'XI',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->taAsal->id,
        ]);

        // Kelas XII di TA Tujuan
        $this->kelasTujuan = Kelas::create([
            'nama' => 'XII-IPA-Baru',
            'tingkat' => 'XII',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->taTujuan->id,
        ]);
    }

    public function test_siswa_aktif_index_excludes_alumni()
    {
        // Siswa Aktif
        $siswaAktif = Siswa::create([
            'nis' => '10001',
            'nisn' => '0000000001',
            'nama_lengkap' => 'Active Student',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2008-01-01',
            'no_hp_ortu' => '08123456781',
            'kelas_id' => $this->kelasTujuan->id,
            'tahun_akademik_id' => $this->taTujuan->id,
            'status' => 'aktif',
        ]);

        // Siswa Alumni
        $siswaAlumni = Siswa::create([
            'nis' => '10002',
            'nisn' => '0000000002',
            'nama_lengkap' => 'Alumni Student',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-01-01',
            'no_hp_ortu' => '08123456782',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.siswa.index'));
        $response->assertStatus(200);
        $response->assertSee('Active Student');
        $response->assertDontSee('Alumni Student');
    }

    public function test_naik_kelas_massal_deactivates_graduated_student_and_parent_accounts()
    {
        // 1. Setup Student 1 (graduating, parent only has this child)
        $userSiswa1 = User::create([
            'name' => 'Siswa 1',
            'username' => 'siswa1',
            'email' => 'siswa1@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
            'status' => 'aktif',
        ]);

        $userOrtu1 = User::create([
            'name' => 'Ortu 1',
            'username' => 'ortu1',
            'email' => 'ortu1@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
        ]);

        $siswa1 = Siswa::create([
            'nis' => '10003',
            'nisn' => '0000000003',
            'nama_lengkap' => 'Siswa Lulus 1',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2008-01-01',
            'no_hp_ortu' => '08123456783',
            'kelas_id' => $this->kelasAsal->id,
            'tahun_akademik_id' => $this->taAsal->id,
            'status' => 'aktif',
            'user_id' => $userSiswa1->id,
            'ortu_user_id' => $userOrtu1->id,
        ]);

        // 2. Setup Student 2 (graduating, parent HAS ANOTHER child that is XI (active))
        $userSiswa2 = User::create([
            'name' => 'Siswa 2',
            'username' => 'siswa2',
            'email' => 'siswa2@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
            'status' => 'aktif',
        ]);

        $userOrtu2 = User::create([
            'name' => 'Ortu 2 (Shared)',
            'username' => 'ortu2',
            'email' => 'ortu2@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
        ]);

        $siswa2 = Siswa::create([
            'nis' => '10004',
            'nisn' => '0000000004',
            'nama_lengkap' => 'Siswa Lulus 2',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2008-02-02',
            'no_hp_ortu' => '08123456784',
            'kelas_id' => $this->kelasAsal->id,
            'tahun_akademik_id' => $this->taAsal->id,
            'status' => 'aktif',
            'user_id' => $userSiswa2->id,
            'ortu_user_id' => $userOrtu2->id,
        ]);

        // Other active child for Ortu 2
        $siswaActiveChild = Siswa::create([
            'nis' => '10005',
            'nisn' => '0000000005',
            'nama_lengkap' => 'Active Child of Ortu 2',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2009-02-02',
            'no_hp_ortu' => '08123456784',
            'kelas_id' => $this->kelasXiAsal->id,
            'tahun_akademik_id' => $this->taAsal->id,
            'status' => 'aktif',
            'user_id' => null,
            'ortu_user_id' => $userOrtu2->id,
        ]);

        // Execute naikKelasMassal
        $service = app(SiswaService::class);
        $result = $service->naikKelasMassal($this->taAsal->id, $this->taTujuan->id);

        $this->assertEquals(3, $result['success']); // 2 siswa lulus (XII) + 1 siswa naik kelas (XI -> XII)

        // Assert Student 1 is alumni
        $siswa1->refresh();
        $this->assertEquals('alumni', $siswa1->status);
        $this->assertNull($siswa1->kelas_id);
        $this->assertNull($siswa1->tahun_akademik_id);

        // Assert Student 1 user account is nonaktif
        $userSiswa1->refresh();
        $this->assertEquals('nonaktif', $userSiswa1->status);

        // Assert Ortu 1 user account is nonaktif (since they have no other active child)
        $userOrtu1->refresh();
        $this->assertEquals('nonaktif', $userOrtu1->status);

        // Assert Student 2 user account is nonaktif
        $userSiswa2->refresh();
        $this->assertEquals('nonaktif', $userSiswa2->status);

        // Assert Ortu 2 user account is STILL aktif (since they have another active child)
        $userOrtu2->refresh();
        $this->assertEquals('aktif', $userOrtu2->status);
    }

    public function test_super_admin_can_access_alumni_index_search_and_filter()
    {
        // Create an Alumni Student
        $alumni1 = Siswa::create([
            'nis' => '10006',
            'nisn' => '0000000006',
            'nama_lengkap' => 'Alumni A',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-01-01',
            'no_hp_ortu' => '08123456786',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
        ]);

        // Create RiwayatKenaikanKelas for Alumni A
        RiwayatKenaikanKelas::create([
            'siswa_id' => $alumni1->id,
            'kelas_asal_id' => $this->kelasAsal->id,
            'kelas_tujuan_id' => null,
            'tahun_akademik_asal_id' => $this->taAsal->id,
            'tahun_akademik_tujuan_id' => null,
            'status_awal' => 'aktif',
            'status_akhir' => 'alumni',
            'keterangan' => 'Lulus',
        ]);

        $alumni2 = Siswa::create([
            'nis' => '10007',
            'nisn' => '0000000007',
            'nama_lengkap' => 'Alumni B',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-02-02',
            'no_hp_ortu' => '08123456787',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
        ]);

        // Test normal access
        $response = $this->actingAs($this->superAdmin)->get(route('admin.alumni.index'));
        $response->assertStatus(200);

        // Test non super_admin cannot access
        $responseNonAdmin = $this->actingAs($this->adminSekolah)->get(route('admin.alumni.index'));
        $responseNonAdmin->assertStatus(403); // Or redirect depending on middleware, usually abort 403. Let's see.

        // Test Search by name
        $responseSearch = $this->actingAs($this->superAdmin)->get(route('admin.alumni.index', ['search' => 'Alumni A']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Alumni A');
        $responseSearch->assertDontSee('Alumni B');

        // Test Filter by tahun_lulus
        $responseFilter1 = $this->actingAs($this->superAdmin)->get(route('admin.alumni.index', ['tahun_lulus' => $this->taAsal->id]));
        $responseFilter1->assertStatus(200);
        $responseFilter1->assertSee('Alumni A');
        $responseFilter1->assertDontSee('Alumni B'); // Alumni B has no Riwayat
    }

    public function test_super_admin_can_view_alumni_profile()
    {
        $alumni = Siswa::create([
            'nis' => '10008',
            'nisn' => '0000000008',
            'nama_lengkap' => 'Alumni Profile Test',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-03-03',
            'no_hp_ortu' => '08123456788',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.alumni.show', $alumni));
        $response->assertStatus(200);

        // Access with active student should abort 404
        $activeSiswa = Siswa::create([
            'nis' => '10009',
            'nisn' => '0000000009',
            'nama_lengkap' => 'Active Student for 404',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2008-01-01',
            'no_hp_ortu' => '08123456789',
            'kelas_id' => $this->kelasAsal->id,
            'tahun_akademik_id' => $this->taAsal->id,
            'status' => 'aktif',
        ]);

        $response404 = $this->actingAs($this->superAdmin)->get(route('admin.alumni.show', $activeSiswa));
        $response404->assertStatus(404);
    }

    public function test_super_admin_can_delete_alumni()
    {
        $userSiswa = User::create([
            'name' => 'Siswa Alumni delete',
            'username' => 'siswa_alumni_del',
            'email' => 'siswa_del@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
        ]);

        $alumni = Siswa::create([
            'nis' => '10010',
            'nisn' => '0000000010',
            'nama_lengkap' => 'Alumni Delete Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-04-04',
            'no_hp_ortu' => '08123456790',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
            'user_id' => $userSiswa->id,
        ]);

        // Attach ortu
        $userOrtu = User::create([
            'name' => 'Ortu Alumni delete',
            'username' => 'ortu_alumni_del',
            'email' => 'ortu_del@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORANG_TUA,
        ]);
        $alumni->ortu()->sync([$userOrtu->id]);

        // Add riwayat
        $riwayat = RiwayatKenaikanKelas::create([
            'siswa_id' => $alumni->id,
            'kelas_asal_id' => $this->kelasAsal->id,
            'tahun_akademik_asal_id' => $this->taAsal->id,
            'status_awal' => 'aktif',
            'status_akhir' => 'alumni',
            'keterangan' => 'Lulus',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('admin.alumni.destroy', $alumni));
        $response->assertRedirect(route('admin.alumni.index'));

        // Assert deleted from DB
        $this->assertDatabaseMissing('siswa', ['id' => $alumni->id]);
        $this->assertDatabaseMissing('users', ['id' => $userSiswa->id]);
        $this->assertDatabaseMissing('riwayat_kenaikan_kelas', ['id' => $riwayat->id]);
        $this->assertDatabaseMissing('siswa_ortu', ['siswa_id' => $alumni->id, 'ortu_user_id' => $userOrtu->id]);

        // Activity log recorded
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete',
            'module' => 'alumni',
        ]);
    }

    public function test_super_admin_can_delete_all_alumni()
    {
        // 1. Buat 2 objek alumni beserta user terkait.
        $userAlumni1 = User::create([
            'name' => 'User Alumni 1',
            'username' => 'user_alumni_1',
            'email' => 'alumni1@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
        ]);

        $alumni1 = Siswa::create([
            'nis' => '20001',
            'nisn' => '0000000021',
            'nama_lengkap' => 'Alumni Test 1',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-05-05',
            'no_hp_ortu' => '08123456791',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
            'user_id' => $userAlumni1->id,
        ]);

        $userAlumni2 = User::create([
            'name' => 'User Alumni 2',
            'username' => 'user_alumni_2',
            'email' => 'alumni2@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
        ]);

        $alumni2 = Siswa::create([
            'nis' => '20002',
            'nisn' => '0000000022',
            'nama_lengkap' => 'Alumni Test 2',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2006-06-06',
            'no_hp_ortu' => '08123456792',
            'kelas_id' => null,
            'tahun_akademik_id' => null,
            'status' => 'alumni',
            'user_id' => $userAlumni2->id,
        ]);

        // 2. Buat 1 objek siswa aktif (status = 'aktif') untuk memastikan data siswa aktif TIDAK ikut terhapus.
        $userSiswaAktif = User::create([
            'name' => 'Siswa Aktif',
            'username' => 'siswa_aktif',
            'email' => 'siswa_aktif@example.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
        ]);

        $siswaAktif = Siswa::create([
            'nis' => '30001',
            'nisn' => '0000000031',
            'nama_lengkap' => 'Active Student Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2008-01-01',
            'no_hp_ortu' => '08123456793',
            'kelas_id' => $this->kelasTujuan->id,
            'tahun_akademik_id' => $this->taTujuan->id,
            'status' => 'aktif',
            'user_id' => $userSiswaAktif->id,
        ]);

        // 3. Kirim HTTP DELETE request ke route('admin.alumni.destroy-all') sebagai Super Admin.
        $response = $this->actingAs($this->superAdmin)->delete(route('admin.alumni.destroy-all'));

        // 4. Assert respon status 200 dan respon JSON ['success' => true].
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 5. Assert database missing untuk data alumni & user alumni.
        $this->assertDatabaseMissing('siswa', ['id' => $alumni1->id]);
        $this->assertDatabaseMissing('users', ['id' => $userAlumni1->id]);
        $this->assertDatabaseMissing('siswa', ['id' => $alumni2->id]);
        $this->assertDatabaseMissing('users', ['id' => $userAlumni2->id]);

        // 6. Assert database has untuk siswa aktif.
        $this->assertDatabaseHas('siswa', ['id' => $siswaAktif->id]);
        $this->assertDatabaseHas('users', ['id' => $userSiswaAktif->id]);

        // 7. Assert activity_logs mencatat log 'delete' alumni.
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delete',
            'module' => 'alumni',
        ]);
    }
}
