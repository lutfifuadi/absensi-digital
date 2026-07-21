<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasBelumAbsenTest extends TestCase
{
    use RefreshDatabase;

    protected User $userWali;
    protected Guru $guruWali;
    protected Kelas $kelasWali;
    protected TahunAkademik $tahunAkademik;
    protected Siswa $siswa1;
    protected Siswa $siswa2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Tahun Akademik Aktif
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        // 2. Buat User & Guru (Wali Kelas)
        $this->userWali = User::create([
            'name' => 'Wali Kelas 1',
            'username' => '199001012015011001',
            'email' => 'wali@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_WALI_KELAS,
            'email_verified_at' => now(),
        ]);

        $this->guruWali = Guru::create([
            'user_id' => $this->userWali->id,
            'nip' => '199001012015011001',
            'nama_lengkap' => 'Wali Kelas 1',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        // Buat Jurusan
        $jurusan = \App\Models\Jurusan::create([
            'kode' => 'MIPA',
            'nama' => 'Matematika dan Ilmu Pengetahuan Alam',
        ]);

        // 3. Buat Kelas Bimbingan Wali
        $this->kelasWali = Kelas::create([
            'nama' => 'X MIPA 1',
            'tingkat' => 'X',
            'jurusan_id' => $jurusan->id,
            'wali_kelas_id' => $this->guruWali->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // 4. Buat Siswa
        $userSiswa1 = User::create([
            'name' => 'Siswa Satu',
            'username' => '11111',
            'email' => 'siswa1@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'siswa',
            'email_verified_at' => now(),
        ]);

        $this->siswa1 = Siswa::create([
            'user_id' => $userSiswa1->id,
            'nis' => '11111',
            'nisn' => '1111111111',
            'nama_lengkap' => 'Siswa Satu',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $this->kelasWali->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $userSiswa2 = User::create([
            'name' => 'Siswa Dua',
            'username' => '22222',
            'email' => 'siswa2@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => 'siswa',
            'email_verified_at' => now(),
        ]);

        $this->siswa2 = Siswa::create([
            'user_id' => $userSiswa2->id,
            'nis' => '22222',
            'nisn' => '2222222222',
            'nama_lengkap' => 'Siswa Dua',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'kelas_id' => $this->kelasWali->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);
    }

    public function test_guest_cannot_access_belum_absen_page(): void
    {
        $response = $this->get(route('wali-kelas.belum-absen'));
        $response->assertRedirect('/login');
    }

    public function test_non_wali_kelas_cannot_access_belum_absen_page(): void
    {
        $userSiswa = User::where('role', 'siswa')->first();
        $response = $this->actingAs($userSiswa)
            ->withSession(['active_role' => 'siswa'])
            ->get(route('wali-kelas.belum-absen'));
        $response->assertStatus(403);
    }

    public function test_wali_kelas_can_access_belum_absen_page(): void
    {
        $response = $this->actingAs($this->userWali)
            ->withSession([
                'active_role' => User::ROLE_WALI_KELAS,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->get(route('wali-kelas.belum-absen'));
        $response->assertStatus(200);
        $response->assertSee('Siswa Satu');
        $response->assertSee('Siswa Dua');
    }

    public function test_shows_rekap_belum_absen_correctly(): void
    {
        $tanggal = now()->toDateString();

        // Absen siswa1 dulu
        AbsensiSiswa::create([
            'siswa_id' => $this->siswa1->id,
            'kelas_id' => $this->kelasWali->id,
            'tanggal' => $tanggal,
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        // Rekap belum absen harus hanya menampilkan siswa2
        $response = $this->actingAs($this->userWali)
            ->withSession([
                'active_role' => User::ROLE_WALI_KELAS,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->get(route('wali-kelas.belum-absen', ['tanggal' => $tanggal]));

        $response->assertStatus(200);
        $response->assertDontSee('Siswa Satu'); // Sudah absen, tidak boleh muncul
        $response->assertSee('Siswa Dua');     // Belum absen, harus muncul
    }

    public function test_wali_kelas_can_access_manual_attendance_page(): void
    {
        $response = $this->actingAs($this->userWali)
            ->withSession([
                'active_role' => User::ROLE_WALI_KELAS,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->get(route('wali-kelas.absensi-manual.create', ['siswa_id' => $this->siswa1->id]));

        $response->assertStatus(200);
        $response->assertSee('Absensi Manual Murid');
        $response->assertSee('Siswa Satu');
    }

    public function test_wali_kelas_can_store_manual_attendance(): void
    {
        $tanggal = now()->toDateString();
        $data = [
            'siswa_id' => $this->siswa1->id,
            'tanggal' => $tanggal,
            'status' => 'sakit',
            'keterangan' => 'Sakit demam',
        ];

        $response = $this->actingAs($this->userWali)
            ->withSession([
                'active_role' => User::ROLE_WALI_KELAS,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->post(route('wali-kelas.absensi-manual.store'), $data);

        $response->assertRedirect(route('wali-kelas.belum-absen', ['tanggal' => $tanggal]));
        $response->assertSessionHas('success', 'Absensi berhasil disimpan secara manual.');

        // Pastikan tersimpan di DB
        $absensi = AbsensiSiswa::where('siswa_id', $this->siswa1->id)
            ->whereDate('tanggal', $tanggal)
            ->first();

        $this->assertNotNull($absensi);
        $this->assertEquals('sakit', $absensi->status);
        $this->assertEquals('Sakit demam', $absensi->keterangan);
        $this->assertEquals($this->guruWali->id, $absensi->guru_id);
        $this->assertEquals('manual', $absensi->metode);
    }

    public function test_prevent_duplicate_manual_attendance(): void
    {
        $tanggal = now()->toDateString();
        
        // Absen siswa1 dulu
        AbsensiSiswa::create([
            'siswa_id' => $this->siswa1->id,
            'kelas_id' => $this->kelasWali->id,
            'tanggal' => $tanggal,
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        $data = [
            'siswa_id' => $this->siswa1->id,
            'tanggal' => $tanggal,
            'status' => 'sakit',
            'keterangan' => 'Sakit demam',
        ];

        $response = $this->actingAs($this->userWali)
            ->withSession([
                'active_role' => User::ROLE_WALI_KELAS,
                'tahun_akademik_id' => $this->tahunAkademik->id
            ])
            ->from(route('wali-kelas.absensi-manual.create'))
            ->post(route('wali-kelas.absensi-manual.store'), $data);

        $response->assertRedirect(route('wali-kelas.absensi-manual.create'));
        $response->assertSessionHasErrors(['siswa_id']);

        // Data di DB tidak boleh berubah (tetap hadir)
        $absensi = AbsensiSiswa::where('siswa_id', $this->siswa1->id)
            ->whereDate('tanggal', $tanggal)
            ->first();
        $this->assertEquals('hadir', $absensi->status);
    }
}
