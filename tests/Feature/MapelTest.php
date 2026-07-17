<?php

namespace Tests\Feature;

use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $admin = User::factory()->create([
            'role' => 'super_admin',
        ]);
        $this->actingAs($admin);
    }

    public function test_can_view_mapel_index(): void
    {
        $response = $this->get(route('admin.mapel.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_mapel(): void
    {
        $data = [
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'umum',
            'status' => '1',
        ];

        $response = $this->post(route('admin.mapel.store'), $data);
        $response->assertRedirect(route('admin.mapel.index'));

        $this->assertDatabaseHas('mapels', [
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Matematika',
        ]);
    }

    public function test_can_update_mapel(): void
    {
        $mapel = Mapel::create([
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'umum',
            'status' => true,
        ]);

        $data = [
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Matematika Modern',
            'kelompok' => 'kejuruan',
            'status' => '0',
        ];

        $response = $this->put(route('admin.mapel.update', $mapel->id), $data);
        $response->assertRedirect(route('admin.mapel.index'));

        $this->assertDatabaseHas('mapels', [
            'id' => $mapel->id,
            'nama_mapel' => 'Matematika Modern',
            'status' => false,
        ]);
    }

    public function test_can_soft_delete_mapel(): void
    {
        $mapel = Mapel::create([
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Matematika',
            'kelompok' => 'umum',
            'status' => true,
        ]);

        $response = $this->delete(route('admin.mapel.destroy', $mapel->id));
        $response->assertRedirect(route('admin.mapel.index'));

        $this->assertSoftDeleted('mapels', [
            'id' => $mapel->id,
        ]);
    }
}
