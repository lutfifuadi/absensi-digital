<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guru;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected TahunAkademik $tahun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $this->tahun = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
    }

    /**
     * Test delete wali kelas (with profile/guru) via AJAX.
     */
    public function test_admin_can_delete_wali_kelas_via_ajax(): void
    {
        $userWk = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);
        $guru = Guru::create([
            'user_id' => $userWk->id,
            'nip' => '123456789',
            'nama_lengkap' => 'Wali Kelas Test',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
            'qr_code' => 'GURU-123456789',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN
            ])
            ->deleteJson(route('admin.wali-kelas.destroy', $guru->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Wali kelas berhasil dihapus.'
        ]);

        $this->assertDatabaseMissing('guru', ['id' => $guru->id]);
    }

    /**
     * Test delete user wali kelas only (no profile/guru yet) via AJAX.
     */
    public function test_admin_can_delete_user_wali_kelas_via_ajax(): void
    {
        $userWk = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);

        $response = $this->actingAs($this->admin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN
            ])
            ->deleteJson(route('admin.wali-kelas.destroy-user', $userWk->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Akun wali kelas berhasil dihapus.'
        ]);

        $this->assertDatabaseMissing('users', ['id' => $userWk->id]);
    }
}
