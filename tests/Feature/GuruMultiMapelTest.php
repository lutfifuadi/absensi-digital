<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruMultiMapelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Mapel $mapelMatematika;
    protected Mapel $mapelFisika;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $this->actingAs($this->admin);

        $this->mapelMatematika = Mapel::create([
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'umum',
            'status' => true,
        ]);

        $this->mapelFisika = Mapel::create([
            'kode_mapel' => 'FIS',
            'nama_mapel' => 'Fisika',
            'kelompok' => 'umum',
            'status' => true,
        ]);
    }

    public function test_can_view_create_guru_form_with_mapel_options(): void
    {
        $response = $this->get(route('admin.guru.create'));
        $response->assertStatus(200);
        $response->assertSee('Matematika');
        $response->assertSee('Fisika');
    }

    public function test_can_store_guru_with_multiple_mapels(): void
    {
        $data = [
            'nama_lengkap' => 'Budi Sudarsono, S.Pd',
            'nip' => '198808082010011001',
            'jenis_kelamin' => 'L',
            'mapel_ids' => [(string) $this->mapelMatematika->id, (string) $this->mapelFisika->id],
            'jabatan' => 'Guru Pertama',
            'no_hp' => '081234567890',
            'status' => 'aktif',
            'email' => 'budi@sekolah.sch.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('admin.guru.store'), $data);
        $response->assertRedirect(route('admin.guru.index'));

        // Pastikan guru tersimpan di DB
        $guru = Guru::where('nip', '198808082010011001')->first();
        $this->assertNotNull($guru);
        $this->assertEquals('Budi Sudarsono, S.Pd', $guru->nama_lengkap);

        // Pastikan backward compatibility kolom mata_pelajaran
        $this->assertEquals('Matematika, Fisika', $guru->mata_pelajaran);

        // Pastikan relasi many-to-many tersinkronisasi
        $this->assertCount(2, $guru->mapels);
        $this->assertTrue($guru->mapels->contains($this->mapelMatematika));
        $this->assertTrue($guru->mapels->contains($this->mapelFisika));
    }

    public function test_can_update_guru_with_multiple_mapels(): void
    {
        // 1. Buat guru terlebih dahulu
        $user = User::create([
            'name' => 'Budi Sudarsono, S.Pd',
            'username' => '198808082010011001',
            'email' => 'budi@sekolah.sch.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_GURU,
        ]);

        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '198808082010011001',
            'nama_lengkap' => 'Budi Sudarsono, S.Pd',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
            'qr_code' => 'GURU-BUDI',
        ]);

        $guru->mapels()->attach($this->mapelMatematika->id);

        // 2. Lakukan update mapel ke Fisika saja
        $data = [
            'nama_lengkap' => 'Budi Sudarsono, S.Pd (Updated)',
            'nip' => '198808082010011001',
            'jenis_kelamin' => 'L',
            'mapel_ids' => [(string) $this->mapelFisika->id],
            'jabatan' => 'Guru Madya',
            'no_hp' => '081234567890',
            'status' => 'aktif',
            'email' => 'budi.new@sekolah.sch.id',
            'roles' => ['guru'],
        ];

        $response = $this->put(route('admin.guru.update', $guru->id), $data);
        $response->assertRedirect(route('admin.guru.index'));

        $guru->refresh();
        $this->assertEquals('Budi Sudarsono, S.Pd (Updated)', $guru->nama_lengkap);
        $this->assertEquals('Fisika', $guru->mata_pelajaran);

        // Pastikan many-to-many tersinkronisasi (sekarang hanya Fisika)
        $this->assertCount(1, $guru->mapels);
        $this->assertTrue($guru->mapels->contains($this->mapelFisika));
        $this->assertFalse($guru->mapels->contains($this->mapelMatematika));
    }
}
