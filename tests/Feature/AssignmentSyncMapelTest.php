<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Mapel;
use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentSyncMapelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_load_active_mapel_options_on_create_page()
    {
        // 1. Create a guru user
        $user = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '123456789',
            'nama_lengkap' => 'Guru Test',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        // 2. Create mapels (active and inactive)
        $mapelActive1 = Mapel::create([
            'kode_mapel' => 'M01',
            'nama_mapel' => 'Matematika',
            'status' => true,
        ]);
        $mapelActive2 = Mapel::create([
            'kode_mapel' => 'M02',
            'nama_mapel' => 'Fisika',
            'status' => true,
        ]);
        $mapelInactive = Mapel::create([
            'kode_mapel' => 'M03',
            'nama_mapel' => 'Kimia',
            'status' => false,
        ]);

        // 3. Act as guru and visit create page
        $response = $this->actingAs($user)->get(route('assignments.create'));

        $response->assertStatus(200);
        $response->assertViewHas('mapelOptions');
        
        $mapelOptions = $response->viewData('mapelOptions');
        $this->assertCount(2, $mapelOptions);
        $this->assertEquals('Fisika', $mapelOptions->first()->nama_mapel); // Sorted alphabetically: Fisika, Matematika
        $this->assertEquals('Matematika', $mapelOptions->last()->nama_mapel);
        $this->assertFalse($mapelOptions->contains('nama_mapel', 'Kimia'));
    }

    /** @test */
    public function it_can_load_active_mapel_options_on_edit_page()
    {
        // 1. Create a guru user
        $user = User::factory()->create(['role' => 'guru']);
        $guru = Guru::create([
            'user_id' => $user->id,
            'nip' => '123456789',
            'nama_lengkap' => 'Guru Test',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'status' => 'aktif',
        ]);

        // 2. Create TahunAkademik, Kelas and Mapels
        $ta = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'jurusan' => 'Umum',
            'tahun_akademik_id' => $ta->id,
        ]);

        $mapelActive = Mapel::create([
            'kode_mapel' => 'M01',
            'nama_mapel' => 'Matematika',
            'status' => true,
        ]);

        // 3. Create an assignment
        $assignment = Assignment::create([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran' => 'Matematika',
            'judul' => 'Tugas 1',
            'deskripsi' => 'Deskripsi tugas 1',
            'tanggal_tugas' => now()->format('Y-m-d'),
        ]);

        // 4. Act as guru and visit edit page
        $response = $this->actingAs($user)->get(route('assignments.edit', $assignment->id));

        $response->assertStatus(200);
        $response->assertViewHas('mapelOptions');
        $response->assertViewHas('assignment');
    }
}
