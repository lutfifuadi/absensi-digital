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

    public function test_search_by_staff_name_and_nip(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $staffUser1 = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff1 = StaffTataUsaha::create([
            'user_id' => $staffUser1->id,
            'nama_lengkap' => 'Budi Sudarsono',
            'nip' => '999111',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        $staffUser2 = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff2 = StaffTataUsaha::create([
            'user_id' => $staffUser2->id,
            'nama_lengkap' => 'Cici Paramida',
            'nip' => '888222',
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff1->id,
            'tanggal' => '2026-07-21',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff2->id,
            'tanggal' => '2026-07-21',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        // Search for 'Budi'
        $response = $this->actingAs($admin)->get(route('admin.absensi-staff.index', ['search' => 'Budi']));
        $response->assertStatus(200);
        $response->assertSee('Budi Sudarsono');
        $response->assertDontSee('Cici Paramida');

        // Search for NIP '888222'
        $response2 = $this->actingAs($admin)->get(route('admin.absensi-staff.index', ['search' => '888222']));
        $response2->assertStatus(200);
        $response2->assertSee('Cici Paramida');
        $response2->assertDontSee('Budi Sudarsono');
    }

    public function test_filter_by_status_and_date(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $staffUser = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff = StaffTataUsaha::create([
            'user_id' => $staffUser->id,
            'nama_lengkap' => 'Dedi Kempot',
            'nip' => '777333',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff->id,
            'tanggal' => '2026-07-21',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff->id,
            'tanggal' => '2026-07-22',
            'status' => 'sakit',
            'metode' => 'manual',
        ]);

        // Filter status 'sakit'
        $response = $this->actingAs($admin)->get(route('admin.absensi-staff.index', ['status' => 'sakit']));
        $response->assertStatus(200);
        $response->assertSee('22 Jul 2026');
        $response->assertDontSee('21 Jul 2026');

        // Filter date '2026-07-21'
        $response2 = $this->actingAs($admin)->get(route('admin.absensi-staff.index', ['tanggal' => '2026-07-21']));
        $response2->assertStatus(200);
        $response2->assertSee('21 Jul 2026');
        $response2->assertDontSee('22 Jul 2026');
    }

    public function test_ajax_pagination_and_per_page(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        $staffUser = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff = StaffTataUsaha::create([
            'user_id' => $staffUser->id,
            'nama_lengkap' => 'Eka Gustiwana',
            'nip' => '666444',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        // Create 15 records
        for ($i = 1; $i <= 15; $i++) {
            AbsensiStaff::create([
                'staff_id' => $staff->id,
                'tanggal' => "2026-07-" . sprintf("%02d", $i),
                'status' => 'hadir',
                'metode' => 'manual',
            ]);
        }

        // Test normal request returns full index page
        $response = $this->actingAs($admin)->get(route('admin.absensi-staff.index'));
        $response->assertStatus(200);
        $response->assertSee('<h4 class="das-hero__title text-gradient-gold">Absensi Staff TU</h4>', false);

        // Test AJAX request returns only table partial
        $ajaxResponse = $this->actingAs($admin)->get(
            route('admin.absensi-staff.index', ['per_page' => 10, 'page' => 1]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );
        $ajaxResponse->assertStatus(200);
        $ajaxResponse->assertDontSee('<h4 class="das-hero__title text-gradient-gold">Absensi Staff TU</h4>', false);
        $ajaxResponse->assertSee('15 Jul 2026'); // ordered desc, so top items are latest
        
        // Assert pagination links exist
        $ajaxResponse->assertSee('href="http://127.0.0.1:8000/admin/absensi-staff?per_page=10&amp;page=2', false);
    }

    public function test_staff_tu_can_only_see_their_own_attendance(): void
    {
        // Staff 1
        $staffUser1 = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff1 = StaffTataUsaha::create([
            'user_id' => $staffUser1->id,
            'nama_lengkap' => 'Staff Satu',
            'nip' => '111',
            'status' => 'aktif',
            'jenis_kelamin' => 'L',
        ]);

        // Staff 2
        $staffUser2 = User::factory()->create(['role' => User::ROLE_STAFF_TU]);
        $staff2 = StaffTataUsaha::create([
            'user_id' => $staffUser2->id,
            'nama_lengkap' => 'Staff Dua',
            'nip' => '222',
            'status' => 'aktif',
            'jenis_kelamin' => 'P',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff1->id,
            'tanggal' => '2026-07-21',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        AbsensiStaff::create([
            'staff_id' => $staff2->id,
            'tanggal' => '2026-07-22',
            'status' => 'hadir',
            'metode' => 'manual',
        ]);

        // Login as Staff 1, should only see Staff 1's attendance
        $response = $this->actingAs($staffUser1)->get(route('admin.absensi-staff.index'));
        $response->assertStatus(200);
        $response->assertSee('Staff Satu');
        $response->assertDontSee('Staff Dua');
    }
}

