<?php

namespace Tests\Feature;

use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JurusanTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup role dan admin user jika belum ada
        $role = Role::where('slug', 'super_admin')->first();
        if (!$role) {
            $role = Role::create(['name' => 'Super Admin', 'slug' => 'super_admin']);
        }
        $this->admin = User::factory()->create(['role' => 'super_admin']);
        
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2026/2027',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_jurusan_index()
    {
        $jurusan = Jurusan::create(['kode' => 'TKJ', 'nama' => 'Teknik Komputer & Jaringan']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jurusan.index'));

        $response->assertStatus(200);
        $response->assertSee('Teknik Komputer & Jaringan');
        $response->assertSee('TKJ');
    }

    /** @test */
    public function admin_can_create_jurusan()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.jurusan.store'), [
                'kode' => 'TBSM',
                'nama' => 'Teknik & Bisnis Sepeda Motor',
            ]);

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseHas('jurusan', [
            'kode' => 'TBSM',
            'nama' => 'Teknik & Bisnis Sepeda Motor',
        ]);
    }

    /** @test */
    public function create_jurusan_validation_fails_for_duplicate_kode()
    {
        Jurusan::create(['kode' => 'TKJ', 'nama' => 'Teknik Komputer']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.jurusan.index'))
            ->post(route('admin.jurusan.store'), [
                'kode' => 'TKJ',
                'nama' => 'Teknik Komputer & Jaringan Baru',
            ]);

        $response->assertRedirect(route('admin.jurusan.index'));
        $response->assertSessionHasErrors('kode');
    }

    /** @test */
    public function admin_can_update_jurusan()
    {
        $jurusan = Jurusan::create(['kode' => 'TKJ', 'nama' => 'Teknik Komputer']);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.jurusan.update', $jurusan), [
                'kode' => 'TKJ',
                'nama' => 'Teknik Komputer & Jaringan',
            ]);

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseHas('jurusan', [
            'id' => $jurusan->id,
            'nama' => 'Teknik Komputer & Jaringan',
        ]);
    }

    /** @test */
    public function admin_can_delete_unused_jurusan()
    {
        $jurusan = Jurusan::create(['kode' => 'TABUS', 'nama' => 'Tata Busana']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.jurusan.destroy', $jurusan));

        $response->assertRedirect(route('admin.jurusan.index'));
        $this->assertDatabaseMissing('jurusan', ['id' => $jurusan->id]);
    }

    /** @test */
    public function admin_cannot_delete_used_jurusan()
    {
        $jurusan = Jurusan::create(['kode' => 'TKJ', 'nama' => 'Teknik Komputer & Jaringan']);
        
        $kelas = Kelas::create([
            'nama' => 'X TKJ 1',
            'tingkat' => 'X',
            'jurusan_id' => $jurusan->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.jurusan.destroy', $jurusan));

        $response->assertRedirect(route('admin.jurusan.index'));
        $response->assertSessionHas('error', 'Jurusan tidak dapat dihapus karena masih digunakan oleh kelas.');
        $this->assertDatabaseHas('jurusan', ['id' => $jurusan->id]);
    }
}
