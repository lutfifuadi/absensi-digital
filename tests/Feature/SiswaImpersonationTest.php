<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use App\Models\ImpersonationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiswaImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $studentUser;
    protected Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard admin
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Create academic setup
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
            'tahun_akademik_id' => $ta->id,
        ]);

        // Create student user
        $this->studentUser = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        // Create student record
        $this->siswa = Siswa::create([
            'user_id' => $this->studentUser->id,
            'nis' => '12345',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-01-01',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $ta->id,
            'status' => 'aktif',
        ]);
    }

    /** @test */
    public function admin_can_start_and_leave_student_impersonation()
    {
        // 1. Start Impersonation
        $response = $this->actingAs($this->admin)
            ->post(route('admin.siswa.impersonate', $this->siswa));

        $response->assertRedirect(route('siswa.dashboard'));
        $response->assertSessionHas('impersonated_by', $this->admin->id);
        $response->assertSessionHas('active_role', User::ROLE_SISWA);

        // Verify log is created
        $this->assertDatabaseHas('impersonation_logs', [
            'admin_id' => $this->admin->id,
            'siswa_id' => $this->siswa->id,
            'status' => 'started',
        ]);

        // Verify logged in user is now the student
        $this->assertEquals($this->studentUser->id, auth()->guard('web')->id());

        // 2. Access Admin page during impersonation should redirect to student dashboard
        $adminResponse = $this->get('/admin/siswa');
        $adminResponse->assertRedirect(route('siswa.dashboard'));
        $adminResponse->assertSessionHas('error');

        // 3. Sensitive operations should be blocked
        $passwordResponse = $this->post('/user/password', [
            'current_password' => 'secret',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $passwordResponse->assertSessionHas('error');

        // 4. Leave Impersonation
        $leaveResponse = $this->post(route('impersonate.leave'));
        $leaveResponse->assertRedirect(route('admin.siswa.index'));
        $leaveResponse->assertSessionMissing('impersonated_by');
        $leaveResponse->assertSessionHas('active_role', User::ROLE_SUPER_ADMIN);

        // Verify logged in user is restored to admin
        $this->assertEquals($this->admin->id, auth()->guard('web')->id());

        // Verify log is updated to ended
        $this->assertDatabaseHas('impersonation_logs', [
            'admin_id' => $this->admin->id,
            'siswa_id' => $this->siswa->id,
            'status' => 'ended',
        ]);
    }

    /** @test */
    public function non_admin_cannot_impersonate_student()
    {
        $nonAdmin = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);

        $response = $this->actingAs($nonAdmin)
            ->post(route('admin.siswa.impersonate', $this->siswa));

        $response->assertStatus(403);
    }
}
