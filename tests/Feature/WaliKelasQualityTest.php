<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guru;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasQualityTest extends TestCase
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
     * Memastikan view index wali kelas dapat dirender dengan parameter yang sesuai.
     */
    public function test_admin_can_access_wali_kelas_index(): void
    {
        $userWk = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);
        $guru = Guru::create([
            'user_id' => $userWk->id,
            'nip' => '198705122010121003',
            'nama_lengkap' => 'Teh Ayu Wali Kelas',
            'jenis_kelamin' => 'P',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
            'qr_code' => 'GURU-WK-TEST',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN
            ])
            ->get(route('admin.wali-kelas.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.wali-kelas.index');
        $response->assertViewHas('waliKelasUsers');
        $response->assertSee('Teh Ayu Wali Kelas');
        $response->assertSee('198705122010121003');
    }

    /**
     * Memastikan view form tambah wali kelas dapat dirender dengan parameter mapelOptions.
     */
    public function test_admin_can_access_wali_kelas_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN
            ])
            ->get(route('admin.wali-kelas.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.wali-kelas.form');
        $response->assertViewHas('mapelOptions');
        $response->assertViewHas('guru');
    }

    /**
     * Memastikan view form edit wali kelas dapat dirender dengan parameter yang sesuai.
     */
    public function test_admin_can_access_wali_kelas_edit_form(): void
    {
        $userWk = User::factory()->create(['role' => User::ROLE_WALI_KELAS]);
        $guru = Guru::create([
            'user_id' => $userWk->id,
            'nip' => '198705122010121003',
            'nama_lengkap' => 'Teh Ayu Wali Kelas',
            'jenis_kelamin' => 'P',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
            'qr_code' => 'GURU-WK-TEST',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession([
                'tahun_akademik_id' => $this->tahun->id,
                'active_role' => User::ROLE_SUPER_ADMIN
            ])
            ->get(route('admin.wali-kelas.edit', $guru->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.wali-kelas.form');
        $response->assertViewHas('guru');
        $response->assertViewHas('mapelOptions');
    }
}
