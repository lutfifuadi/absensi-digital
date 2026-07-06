<?php

namespace Tests\Feature\Admin;

use App\Models\Guru;
use App\Models\User;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;
use App\Models\EkskulAbsensi;
use App\Models\AbsensiGuru;
use App\Models\EkskulPembina;
use App\Models\Assignment;
use App\Models\IzinSakit;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HapusSemuaGuruTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat super admin user
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
            'password' => Hash::make('password_admin_123'),
        ]);

        // Buat Tahun Akademik aktif
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'genap',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'is_aktif' => true,
        ]);
    }

    /**
     * Test route admin.guru.destroy-all mengarah ke controller dengan benar dan sukses ketika data valid.
     */
    public function test_destroy_all_guru_success_with_valid_credentials()
    {
        // Buat beberapa user guru & data guru
        $userGuru1 = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru1 = Guru::create([
            'user_id' => $userGuru1->id,
            'nip' => '111111',
            'nama_lengkap' => 'Guru Satu',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        $userGuru2 = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru2 = Guru::create([
            'user_id' => $userGuru2->id,
            'nip' => '222222',
            'nama_lengkap' => 'Guru Dua',
            'jenis_kelamin' => 'P',
            'mata_pelajaran' => 'Fisika',
            'status' => 'aktif',
        ]);

        // Relasikan dengan kelas (wali_kelas_id)
        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'wali_kelas_id' => $guru1->id,
            'is_aktif_absensi' => true,
            'kustomisasi_jam' => false,
        ]);

        // Kirim request hapus semua guru
        $response = $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->delete(route('admin.guru.destroy-all'), [
                'konfirmasi' => 'HAPUS SEMUA GURU',
            ]);

        // Harus dialihkan ke index guru dengan success message
        $response->assertRedirect(route('admin.guru.index'));
        $response->assertSessionHas('success', 'Semua data guru berhasil dihapus.');

        // Pastikan tabel guru kosong
        $this->assertDatabaseCount('guru', 0);

        // Pastikan user terkait dihapus
        $this->assertDatabaseMissing('users', ['id' => $userGuru1->id]);
        $this->assertDatabaseMissing('users', ['id' => $userGuru2->id]);

        // Pastikan relasi di kelas di-set ke NULL
        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'wali_kelas_id' => null,
        ]);

        // Pastikan user admin tetap ada
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test penanganan error ketika input konfirmasi salah.
     */
    public function test_destroy_all_guru_fails_with_invalid_confirmation()
    {
        // Buat user guru & data guru
        $userGuru = User::factory()->create(['role' => User::ROLE_GURU]);
        $guru = Guru::create([
            'user_id' => $userGuru->id,
            'nip' => '111111',
            'nama_lengkap' => 'Guru Satu',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.guru.index'))
            ->delete(route('admin.guru.destroy-all'), [
                'konfirmasi' => 'HAPUS SEMUA', // Salah
            ]);

        // Harus gagal validasi dan kembali ke halaman sebelumnya
        $response->assertRedirect(route('admin.guru.index'));
        $response->assertSessionHasErrors(['konfirmasi']);

        // Data guru dan user guru harus tetap ada
        $this->assertDatabaseHas('guru', ['id' => $guru->id]);
        $this->assertDatabaseHas('users', ['id' => $userGuru->id]);
    }
}
