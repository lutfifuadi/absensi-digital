<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\IzinSakit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalOrangTuaIzinSakitCancelTest extends TestCase
{
    use RefreshDatabase;

    protected $tahunAkademik;
    protected $kelas;
    protected $userOrangTua;
    protected $siswaAnak;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        $this->userOrangTua = User::create([
            'name' => 'Orang Tua Test',
            'username' => 'ortu_test',
            'email' => 'ortu@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
        ]);

        $userSiswa = User::create([
            'name' => 'Siswa Anak Test',
            'username' => 'siswa_anak_test',
            'email' => 'siswa@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
            'status' => 'aktif',
        ]);

        $this->siswaAnak = Siswa::create([
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Anak Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456789',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'user_id' => $userSiswa->id,
            'ortu_user_id' => $this->userOrangTua->id
        ]);
    }

    public function test_parent_can_cancel_pending_izin_sakit_of_their_child()
    {
        $izin = IzinSakit::create([
            'tipe' => 'siswa',
            'reference_id' => $this->siswaAnak->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis' => 'izin',
            'keterangan' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->userOrangTua)->delete(route('ortu.izin-sakit.destroy', $izin->id));

        $response->assertRedirect(route('ortu.izin-sakit.index'));
        $response->assertSessionHas('success', 'Pengajuan izin/sakit berhasil dibatalkan.');
        $this->assertDatabaseMissing('izin_sakit', ['id' => $izin->id]);
    }

    public function test_parent_cannot_cancel_non_pending_izin_sakit_of_their_child()
    {
        $izinDisetujui = IzinSakit::create([
            'tipe' => 'siswa',
            'reference_id' => $this->siswaAnak->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis' => 'izin',
            'keterangan' => 'Keperluan keluarga',
            'status' => 'disetujui',
        ]);

        $response = $this->actingAs($this->userOrangTua)->delete(route('ortu.izin-sakit.destroy', $izinDisetujui->id));

        $response->assertRedirect(route('ortu.izin-sakit.index'));
        $response->assertSessionHas('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses (disetujui/ditolak).');
        $this->assertDatabaseHas('izin_sakit', ['id' => $izinDisetujui->id]);
    }

    public function test_parent_cannot_cancel_izin_sakit_of_other_students()
    {
        $otherOrangTua = User::create([
            'name' => 'Orang Tua Lain',
            'username' => 'ortu_lain',
            'email' => 'ortulain@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
        ]);

        $otherUserSiswa = User::create([
            'name' => 'Siswa Lain',
            'username' => 'siswa_lain',
            'email' => 'siswalain@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_SISWA,
            'status' => 'aktif',
        ]);

        $otherSiswa = Siswa::create([
            'nisn' => '0099999999',
            'nama_lengkap' => 'Siswa Lain',
            'jenis_kelamin' => 'P',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-02-02',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
            'user_id' => $otherUserSiswa->id,
            'ortu_user_id' => $otherOrangTua->id
        ]);

        $izinOther = IzinSakit::create([
            'tipe' => 'siswa',
            'reference_id' => $otherSiswa->id,
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->toDateString(),
            'jenis' => 'izin',
            'keterangan' => 'Keperluan keluarga',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->userOrangTua)->delete(route('ortu.izin-sakit.destroy', $izinOther->id));

        $response->assertStatus(404);
        $this->assertDatabaseHas('izin_sakit', ['id' => $izinOther->id]);
    }
}
