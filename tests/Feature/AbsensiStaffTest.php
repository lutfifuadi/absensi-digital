<?php

namespace Tests\Feature;

use App\Models\AbsensiStaff;
use App\Models\StaffTataUsaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiStaffTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access the staff attendance index and there is no
     * "Call to a member function format() on string" error since the tanggal attribute is casted.
     */
    public function test_admin_can_access_absensi_staff_index_and_renders_correctly(): void
    {
        // 1. Arrange: Create Admin user
        $admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // 2. Arrange: Create Staff
        $staffUser = User::factory()->create([
            'role' => User::ROLE_STAFF_TU,
        ]);
        $staff = StaffTataUsaha::create([
            'user_id' => $staffUser->id,
            'nama_lengkap' => 'Asep Staff TU',
            'nip' => '1234567890',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        // 3. Arrange: Create Absensi Staff record with string date
        // (database driver will store it, and Eloquent should cast it to Carbon instance on retrieval)
        AbsensiStaff::create([
            'staff_id' => $staff->id,
            'tanggal' => '2026-07-21',
            'jam_masuk' => '08:00',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        // Verify the cast works at Model level
        $absensi = AbsensiStaff::first();
        $this->assertInstanceOf(\Carbon\Carbon::class, $absensi->tanggal);
        $this->assertEquals('21 Jul 2026', $absensi->tanggal->format('d M Y'));

        // 4. Act: Request the index page
        $response = $this->actingAs($admin)->get(route('admin.absensi-staff.index'));

        // 5. Assert: Successful rendering of view
        $response->assertStatus(200);
        $response->assertSee('Asep Staff TU');
        $response->assertSee('21 Jul 2026');
    }
}
