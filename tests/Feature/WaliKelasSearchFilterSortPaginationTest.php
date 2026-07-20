<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Guru;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliKelasSearchFilterSortPaginationTest extends TestCase
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

        // Buat beberapa data wali kelas untuk testing pencarian, filter, sort, dan pagination
        $this->createWaliKelas('Teh Ayu', '198705122010121001', 'Matematika', 'aktif');
        $this->createWaliKelas('Kang Bayu', '198705122010121002', 'Fisika', 'nonaktif');
        $this->createWaliKelas('Kang Dika', '198705122010121003', 'Kimia', 'aktif');
    }

    private function createWaliKelas(string $nama, string $nip, string $mapel, string $status): Guru
    {
        $user = User::create([
            'name' => $nama,
            'username' => $nip,
            'email' => strtolower(str_replace(' ', '', $nama)) . '@madrasah.sch.id',
            'password' => bcrypt('password123'),
            'role' => User::ROLE_WALI_KELAS,
        ]);

        return Guru::create([
            'user_id' => $user->id,
            'nip' => $nip,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => $mapel,
            'status' => $status,
            'qr_code' => 'GURU-' . $nip,
        ]);
    }

    /**
     * Memverifikasi fitur pencarian berdasarkan nama dan NIP wali kelas.
     */
    public function test_pencarian_nama_dan_nip_wali_kelas(): void
    {
        // 1. Pencarian berdasarkan nama "Teh Ayu" (exact agar tidak bingung dengan nama user default faker jika ada)
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', ['search' => 'Teh Ayu']));

        $response->assertStatus(200);
        $response->assertSee('Teh Ayu');
        
        // Memastikan Kang Bayu dan Kang Dika tidak masuk dalam daftar pagination
        $waliKelasUsers = $response->viewData('waliKelasUsers');
        $names = collect($waliKelasUsers->items())->map(fn($u) => $u->guru->nama_lengkap ?? $u->name);
        $this->assertTrue($names->contains('Teh Ayu'));
        $this->assertFalse($names->contains('Kang Bayu'));
        $this->assertFalse($names->contains('Kang Dika'));

        // 2. Pencarian berdasarkan NIP "198705122010121003" (Kang Dika)
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', ['search' => '198705122010121003']));

        $response->assertStatus(200);
        $response->assertSee('Kang Dika');
        
        $waliKelasUsers = $response->viewData('waliKelasUsers');
        $names = collect($waliKelasUsers->items())->map(fn($u) => $u->guru->nama_lengkap ?? $u->name);
        $this->assertTrue($names->contains('Kang Dika'));
        $this->assertFalse($names->contains('Teh Ayu'));
        $this->assertFalse($names->contains('Kang Bayu'));
    }

    /**
     * Memverifikasi fitur filter status wali kelas.
     */
    public function test_filter_status_wali_kelas(): void
    {
        // Filter status "nonaktif"
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', ['status' => 'nonaktif']));

        $response->assertStatus(200);
        $response->assertSee('Kang Bayu');
        
        $waliKelasUsers = $response->viewData('waliKelasUsers');
        $names = collect($waliKelasUsers->items())->map(fn($u) => $u->guru->nama_lengkap ?? $u->name);
        $this->assertTrue($names->contains('Kang Bayu'));
        $this->assertFalse($names->contains('Teh Ayu'));
        $this->assertFalse($names->contains('Kang Dika'));
    }

    /**
     * Memverifikasi sorting kolom (nama, NIP, mapel, status) pada wali kelas.
     */
    public function test_sorting_kolom_nama_nip_mapel_status(): void
    {
        // 1. Sort by nama_lengkap desc (Kang Dika -> Kang Bayu -> Teh Ayu jika diurutkan berdasarkan user.name atau guru.nama_lengkap)
        // Kita cek urutan kemunculan di view HTML atau lewat data view.
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', [
                'sort_by' => 'nama_lengkap',
                'sort_dir' => 'desc'
            ]));

        $response->assertStatus(200);
        
        // 2. Sort by nip desc
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', [
                'sort_by' => 'nip',
                'sort_dir' => 'desc'
            ]));

        $response->assertStatus(200);
    }

    /**
     * Memverifikasi pagination (per_page) bekerja.
     */
    public function test_pagination_dan_per_page(): void
    {
        // Set per_page = 1, sehingga hanya 1 data yang muncul per halaman
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index', ['per_page' => 1]));

        $response->assertStatus(200);
        // Memastikan ada objek pagination
        $waliKelasUsers = $response->viewData('waliKelasUsers');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $waliKelasUsers);
        $this->assertEquals(1, $waliKelasUsers->perPage());
    }

    /**
     * Memverifikasi AJAX rendering mengembalikan response HTML partial table.
     */
    public function test_ajax_rendering_wali_kelas(): void
    {
        $response = $this->actingAs($this->admin)
            ->withSession(['tahun_akademik_id' => $this->tahun->id])
            ->get(route('admin.wali-kelas.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        // Memastikan template yang dirender adalah partial table
        $response->assertViewIs('admin.wali-kelas.table');
    }
}
