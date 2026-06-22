<?php

namespace Tests\Feature;

use App\Models\StaffTataUsaha;
use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTataUsahaTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
            'username' => 'admin_test'
        ]);

        Pengaturan::create([
            'key' => 'website_lembaga',
            'value' => 'madrasah.sch.id',
            'group' => 'umum'
        ]);
        
        Pengaturan::create([
            'key' => 'nama_sekolah',
            'value' => 'MAN Test',
            'group' => 'umum'
        ]);
    }

    public function test_admin_can_view_staff_tu_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.staff-tata-usaha.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_staff_tu()
    {
        $staffData = [
            'nama_lengkap' => 'Staff TU Test',
            'nip' => '199001012020011001',
            'jenis_kelamin' => 'L',
            'jabatan' => 'Administrasi Umum',
            'no_hp' => '081234567890',
            'status' => 'aktif',
            'email' => 'staff_tu_test@madrasah.sch.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.staff-tata-usaha.store'), $staffData);

        $response->assertRedirect(route('admin.staff-tata-usaha.index'));
        
        $this->assertDatabaseHas('staff_tata_usaha', [
            'nip' => '199001012020011001',
            'nama_lengkap' => 'Staff TU Test',
            'jabatan' => 'Administrasi Umum',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => '199001012020011001',
            'role' => User::ROLE_STAFF_TU,
        ]);
    }

    public function test_admin_can_update_staff_tu()
    {
        $user = User::factory()->create([
            'name' => 'Staff Asli',
            'username' => '199001012020011002',
            'email' => 'staff_asli@madrasah.sch.id',
            'role' => User::ROLE_STAFF_TU,
        ]);

        $staff = StaffTataUsaha::create([
            'user_id' => $user->id,
            'nip' => '199001012020011002',
            'nama_lengkap' => 'Staff Asli',
            'jenis_kelamin' => 'L',
            'jabatan' => 'Staff Lama',
            'no_hp' => '081234567891',
            'status' => 'aktif',
            'qr_code' => 'STAFF-12345'
        ]);

        $updateData = [
            'nama_lengkap' => 'Staff Diperbarui',
            'nip' => '199001012020011002',
            'jenis_kelamin' => 'P',
            'jabatan' => 'Staff Baru',
            'no_hp' => '081234567899',
            'status' => 'nonaktif',
            'email' => 'staff_baru@madrasah.sch.id',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.staff-tata-usaha.update', $staff), $updateData);

        $response->assertRedirect(route('admin.staff-tata-usaha.index'));

        $this->assertDatabaseHas('staff_tata_usaha', [
            'id' => $staff->id,
            'nama_lengkap' => 'Staff Diperbarui',
            'jenis_kelamin' => 'P',
            'jabatan' => 'Staff Baru',
            'status' => 'nonaktif',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Staff Diperbarui',
            'email' => 'staff_baru@madrasah.sch.id',
        ]);
    }
}
