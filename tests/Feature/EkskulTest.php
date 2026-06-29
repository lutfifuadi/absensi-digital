<?php

namespace Tests\Feature;

use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\EkskulAbsensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EkskulTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $nonAdmin;
    protected TahunAkademik $tahunAkademik;
    protected Kelas $kelas;
    protected Siswa $siswa1;
    protected Siswa $siswa2;
    protected Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();

        // ── Data Referensi ──────────────────────────────────────────
        $this->tahunAkademik = TahunAkademik::create([
            'nama'           => '2025/2026',
            'semester'       => 'genap',
            'tanggal_mulai'  => '2026-01-05',
            'tanggal_selesai'=> '2026-06-20',
            'is_aktif'       => true,
        ]);

        $this->kelas = Kelas::create([
            'nama'              => 'XII RPL',
            'tingkat'           => 'XII',
            'jurusan'           => 'RPL',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // ── User Admin ──────────────────────────────────────────────
        $this->admin = User::factory()->create([
            'role'     => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_test',
        ]);

        // ── User Non-Admin (siswa) untuk test 403 ───────────────────
        $this->nonAdmin = User::factory()->create([
            'role'     => User::ROLE_SISWA,
            'username' => 'siswa_test',
        ]);

        // ── Guru (pembina) untuk absensi ────────────────────────────
        $pembinaUser = User::factory()->create([
            'role'     => User::ROLE_GURU,
            'username' => 'guru_budi',
        ]);

        $this->guru = Guru::create([
            'user_id'        => $pembinaUser->id,
            'nip'            => '198501012010011001',
            'nama_lengkap'   => 'Budi Santoso, S.Pd.',
            'jenis_kelamin'  => 'L',
            'mata_pelajaran' => 'Olahraga',
            'status'         => 'aktif',
        ]);

        // ── Siswa untuk anggota & absensi ───────────────────────────
        $this->siswa1 = Siswa::create([
            'nis'               => '2024001',
            'nisn'              => '0012345678',
            'nama_lengkap'      => 'Ahmad Fauzi',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Jakarta',
            'tanggal_lahir'     => '2008-01-01',
            'kelas_id'          => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status'            => 'aktif',
        ]);

        $this->siswa2 = Siswa::create([
            'nis'               => '2024002',
            'nisn'              => '0012345679',
            'nama_lengkap'      => 'Rina Marlina',
            'jenis_kelamin'     => 'P',
            'tempat_lahir'      => 'Jakarta',
            'tanggal_lahir'     => '2008-02-02',
            'kelas_id'          => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status'            => 'aktif',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  1. Admin bisa melihat daftar ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_view_ekskul_index(): void
    {
        Ekskul::create(['nama' => 'Pramuka', 'kategori' => 'wajib', 'status' => true]);
        Ekskul::create(['nama' => 'Futsal', 'kategori' => 'olahraga', 'status' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ekskul.index'));

        $response->assertStatus(200);
        $response->assertSee('Pramuka');
        $response->assertSee('Futsal');
    }

    // ─────────────────────────────────────────────────────────────────
    //  2. Admin bisa tambah ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_create_ekskul(): void
    {
        $data = [
            'nama'      => 'Pramuka',
            'kategori'  => 'wajib',
            'deskripsi' => 'Kegiatan pramuka untuk membentuk karakter disiplin.',
            'kuota'     => 50,
            'status'    => true,
            'icon'      => 'pramuka',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.store'), $data);

        $response->assertRedirect(route('admin.ekskul.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ekskul', [
            'nama'      => 'Pramuka',
            'kategori'  => 'wajib',
            'deskripsi' => 'Kegiatan pramuka untuk membentuk karakter disiplin.',
            'kuota'     => 50,
            'status'    => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  3. Admin bisa edit ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_update_ekskul(): void
    {
        $ekskul = Ekskul::create([
            'nama'      => 'Pramuka',
            'kategori'  => 'wajib',
            'deskripsi' => 'Deskripsi lama.',
            'kuota'     => 50,
            'status'    => true,
        ]);

        $updateData = [
            'nama'      => 'Pramuka Updated',
            'kategori'  => 'wajib',
            'deskripsi' => 'Deskripsi baru setelah update.',
            'kuota'     => 40,
            'status'    => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.ekskul.update', $ekskul->id), $updateData);

        $response->assertRedirect(route('admin.ekskul.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ekskul', [
            'id'        => $ekskul->id,
            'nama'      => 'Pramuka Updated',
            'deskripsi' => 'Deskripsi baru setelah update.',
            'kuota'     => 40,
        ]);

        $this->assertDatabaseMissing('ekskul', [
            'id'        => $ekskul->id,
            'nama'      => 'Pramuka',
            'deskripsi' => 'Deskripsi lama.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  4. Admin bisa hapus ekskul (soft delete)
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_delete_ekskul(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'status'   => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.ekskul.destroy', $ekskul->id));

        $response->assertRedirect(route('admin.ekskul.index'));
        $response->assertSessionHas('success');

        // Record masih ada tapi soft-deleted
        $this->assertSoftDeleted('ekskul', ['id' => $ekskul->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  5. Admin bisa toggle status ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_toggle_ekskul_status(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'status'   => true,
        ]);

        $this->assertTrue((bool) $ekskul->status);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.toggle-status', $ekskul->id));

        $response->assertRedirect(route('admin.ekskul.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ekskul', [
            'id'     => $ekskul->id,
            'status' => false,
        ]);

        // Toggle balik ke true
        $response2 = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.toggle-status', $ekskul->id));

        $response2->assertRedirect(route('admin.ekskul.index'));
        $this->assertDatabaseHas('ekskul', [
            'id'     => $ekskul->id,
            'status' => true,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  6. Validasi nama wajib diisi
    // ─────────────────────────────────────────────────────────────────
    public function test_ekskul_requires_nama(): void
    {
        $data = [
            'nama'     => '',
            'kategori' => 'wajib',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('nama');
    }

    // ─────────────────────────────────────────────────────────────────
    //  7. Tambah anggota ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_add_anggota_to_ekskul(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'kuota'    => 50,
            'status'   => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.anggota.store', $ekskul->id), [
                'siswa_id' => $this->siswa1->id,
            ]);

        $response->assertRedirect(); // back()
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ekskul_anggota', [
            'ekskul_id' => $ekskul->id,
            'siswa_id'  => $this->siswa1->id,
            'status'    => 'aktif',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  8. Simpan absensi ekskul
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_store_absensi(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'kuota'    => 50,
            'status'   => true,
        ]);

        // Daftarkan anggota dulu
        EkskulAnggota::create([
            'ekskul_id'     => $ekskul->id,
            'siswa_id'      => $this->siswa1->id,
            'status'        => 'aktif',
            'tanggal_masuk' => now()->toDateString(),
        ]);

        EkskulAnggota::create([
            'ekskul_id'     => $ekskul->id,
            'siswa_id'      => $this->siswa2->id,
            'status'        => 'aktif',
            'tanggal_masuk' => now()->toDateString(),
        ]);

        $tanggal = now()->toDateString();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.absensi.store', [$ekskul->id, $tanggal]), [
                'pembina_id' => $this->guru->id,
                'absensi' => [
                    [
                        'siswa_id'  => $this->siswa1->id,
                        'status'    => 'hadir',
                        'jam_absen' => '14:05',
                    ],
                    [
                        'siswa_id'  => $this->siswa2->id,
                        'status'    => 'izin',
                        'keterangan'=> 'Sakit',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ekskul_absensi', [
            'ekskul_id' => $ekskul->id,
            'siswa_id'  => $this->siswa1->id,
            'tanggal'   => $tanggal . ' 00:00:00',
            'status'    => 'hadir',
        ]);

        $this->assertDatabaseHas('ekskul_absensi', [
            'ekskul_id' => $ekskul->id,
            'siswa_id'  => $this->siswa2->id,
            'tanggal'   => $tanggal . ' 00:00:00',
            'status'    => 'izin',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  9. Lihat rekap absensi
    // ─────────────────────────────────────────────────────────────────
    public function test_admin_can_view_absensi_rekap(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'status'   => true,
        ]);

        // Buat beberapa data absensi
        EkskulAbsensi::create([
            'ekskul_id' => $ekskul->id,
            'siswa_id'  => $this->siswa1->id,
            'tanggal'   => now()->toDateString(),
            'status'    => 'hadir',
            'jam_absen' => '14:05',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ekskul.absensi.rekap', $ekskul->id));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────
    // 10. Non-admin tidak bisa akses (403 Forbidden)
    // ─────────────────────────────────────────────────────────────────
    public function test_non_admin_cannot_access_ekskul_management(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'status'   => true,
        ]);

        // Siswa tidak punya akses ke modul ekskul
        $response = $this->actingAs($this->nonAdmin)
            ->get(route('admin.ekskul.index'));

        $response->assertStatus(403);

        // Coba akses halaman lain juga harus 403
        $response2 = $this->actingAs($this->nonAdmin)
            ->post(route('admin.ekskul.store'), [
                'nama'     => 'Hack',
                'kategori' => 'wajib',
            ]);

        $response2->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────
    // BONUS: Test edge cases
    // ─────────────────────────────────────────────────────────────────

    #[Test]
    public function cannot_add_anggota_when_ekskul_is_nonactive(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'kuota'    => 50,
            'status'   => false, // nonaktif
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.anggota.store', $ekskul->id), [
                'siswa_id' => $this->siswa1->id,
            ]);

        $response->assertRedirect(); // back()
        $response->assertSessionHas('error');
    }

    #[Test]
    public function cannot_add_duplicate_anggota(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'kuota'    => 50,
            'status'   => true,
        ]);

        // Tambah anggota pertama kali — sukses
        $this->actingAs($this->admin)
            ->post(route('admin.ekskul.anggota.store', $ekskul->id), [
                'siswa_id' => $this->siswa1->id,
            ])
            ->assertSessionHas('success');

        // Tambah anggota yang sama — harus gagal
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.anggota.store', $ekskul->id), [
                'siswa_id' => $this->siswa1->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function absensi_requires_status_field(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'status'   => true,
        ]);

        $tanggal = now()->toDateString();

        // Kirim data absensi tanpa field status
        $response = $this->actingAs($this->admin)
            ->post(route('admin.ekskul.absensi.store', [$ekskul->id, $tanggal]), [
                'absensi' => [
                    [
                        'siswa_id' => $this->siswa1->id,
                        'status'   => '',
                    ],
                ],
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('absensi.0.status');
    }

    #[Test]
    public function ekskul_filter_by_kategori_works(): void
    {
        Ekskul::create(['nama' => 'Pramuka', 'kategori' => 'wajib', 'status' => true]);
        Ekskul::create(['nama' => 'Futsal', 'kategori' => 'olahraga', 'status' => true]);
        Ekskul::create(['nama' => 'PMR', 'kategori' => 'pilihan', 'status' => true]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ekskul.index', ['kategori' => 'olahraga']));

        $response->assertStatus(200);
        $response->assertSee('Futsal');
        $response->assertDontSee('Pramuka');
        $response->assertDontSee('PMR');
    }

    #[Test]
    public function admin_can_view_anggota_index(): void
    {
        $ekskul = Ekskul::create([
            'nama'     => 'Pramuka',
            'kategori' => 'wajib',
            'kuota'    => 50,
            'status'   => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.ekskul.anggota.index', $ekskul->id));

        $response->assertStatus(200);
        $response->assertSee('Pramuka');
    }
}
