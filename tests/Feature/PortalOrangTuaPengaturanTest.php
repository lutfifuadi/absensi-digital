<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalOrangTuaPengaturanTest extends TestCase
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
            'password' => bcrypt('password123'),
            'role' => User::ROLE_ORANG_TUA,
            'status' => 'aktif',
            'no_hp' => '08123456789',
            'hubungan' => 'Ayah',
            'alamat' => 'Jl. Test No. 123',
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

    public function test_parent_can_view_pengaturan_page()
    {
        $response = $this->actingAs($this->userOrangTua)->get(route('ortu.pengaturan'));

        $response->assertStatus(200);
        $response->assertViewIs('portal-ortu.pengaturan');
        $response->assertSee('Orang Tua Test');
        $response->assertSee('Ayah');
        $response->assertSee('08123456789');
        $response->assertSee('ortu@test.com');
        $response->assertSee('Jl. Test No. 123');
    }

    public function test_parent_can_update_profile()
    {
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.profil'), [
            'name' => 'Orang Tua Baru',
            'hubungan' => 'Ibu',
            'no_hp' => '08987654321',
            'email' => 'ortubaru@test.com',
            'alamat' => 'Jl. Baru No. 456',
        ]);

        $response->assertRedirect(route('ortu.pengaturan'));
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->userOrangTua->refresh();

        $this->assertEquals('Orang Tua Baru', $this->userOrangTua->name);
        $this->assertEquals('Ibu', $this->userOrangTua->hubungan);
        $this->assertEquals('08987654321', $this->userOrangTua->no_hp);
        $this->assertEquals('ortubaru@test.com', $this->userOrangTua->email);
        $this->assertEquals('Jl. Baru No. 456', $this->userOrangTua->alamat);
    }

    public function test_parent_profile_update_validation()
    {
        // 1. WhatsApp tidak valid
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.profil'), [
            'name' => 'Orang Tua Baru',
            'hubungan' => 'Ibu',
            'no_hp' => 'abcd12345', // Ada huruf
            'email' => 'ortubaru@test.com',
            'alamat' => 'Jl. Baru No. 456',
        ]);

        $response->assertSessionHasErrors('no_hp');

        // 2. Email tidak valid
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.profil'), [
            'name' => 'Orang Tua Baru',
            'hubungan' => 'Ibu',
            'no_hp' => '08987654321',
            'email' => 'not-an-email',
            'alamat' => 'Jl. Baru No. 456',
        ]);

        $response->assertSessionHasErrors('email');

        // 3. Email duplikat (menggunakan email siswa)
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.profil'), [
            'name' => 'Orang Tua Baru',
            'hubungan' => 'Ibu',
            'no_hp' => '08987654321',
            'email' => 'siswa@test.com',
            'alamat' => 'Jl. Baru No. 456',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_parent_can_change_password()
    {
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.password'), [
            'password_lama' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('ortu.pengaturan'));
        $response->assertSessionHas('success', 'Password Anda berhasil diperbarui.');
        $response->assertSessionHas('password_success', true);

        $this->userOrangTua->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->userOrangTua->password));
    }

    public function test_parent_cannot_change_password_with_incorrect_old_password()
    {
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.password'), [
            'password_lama' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('password_lama');

        $this->userOrangTua->refresh();
        $this->assertTrue(Hash::check('password123', $this->userOrangTua->password));
    }

    public function test_parent_password_must_be_minimum_8_chars_and_contain_letters_and_numbers()
    {
        // Kurang dari 8 karakter
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.password'), [
            'password_lama' => 'password123',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response->assertSessionHasErrors('password');

        // Hanya huruf
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.password'), [
            'password_lama' => 'password123',
            'password' => 'onlyletters',
            'password_confirmation' => 'onlyletters',
        ]);

        $response->assertSessionHasErrors('password');

        // Hanya angka
        $response = $this->actingAs($this->userOrangTua)->put(route('ortu.pengaturan.password'), [
            'password_lama' => 'password123',
            'password' => '1234567890',
            'password_confirmation' => '1234567890',
        ]);

        $response->assertSessionHasErrors('password');

        $this->userOrangTua->refresh();
        $this->assertTrue(Hash::check('password123', $this->userOrangTua->password));
    }
}
