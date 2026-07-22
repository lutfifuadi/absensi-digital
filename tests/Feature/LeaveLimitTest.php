<?php

namespace Tests\Feature;

use App\Models\LeaveBalance;
use App\Models\LeaveLimit;
use App\Models\User;
use App\Services\LeaveLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaveLimitTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $admin;
    protected User $siswa;
    protected User $guru;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->siswa = User::factory()->create([
            'role' => User::ROLE_SISWA,
        ]);

        $this->guru = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TESTS: LeaveLimit Model
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function it_can_create_a_leave_limit()
    {
        $limit = LeaveLimit::create([
            'name'         => 'Batas Izin Siswa Bulanan',
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'target_roles' => ['siswa'],
            'is_active'    => true,
        ]);

        $this->assertDatabaseHas('leave_limits', [
            'id'           => $limit->id,
            'name'         => 'Batas Izin Siswa Bulanan',
            'max_days'     => 3,
            'is_active'    => true,
        ]);

        $this->assertEquals(['siswa'], $limit->target_roles);
        $this->assertTrue($limit->is_active);
    }

    /** @test */
    public function it_casts_target_roles_as_array()
    {
        $limit = LeaveLimit::create([
            'name'         => 'Batas Guru',
            'leave_type'   => 'sick',
            'max_days'     => 5,
            'period'       => 'yearly',
            'action_type'  => 'warning',
            'target_roles' => ['guru', 'wali_kelas'],
            'is_active'    => true,
        ]);

        $this->assertIsArray($limit->target_roles);
        $this->assertContains('guru', $limit->target_roles);
        $this->assertContains('wali_kelas', $limit->target_roles);
    }

    /** @test */
    public function it_has_leave_balances_relationship()
    {
        $limit = LeaveLimit::create([
            'name'         => 'Test Limit',
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'target_roles' => ['siswa'],
            'is_active'    => true,
        ]);

        $balance = LeaveBalance::create([
            'user_id'       => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'period_code'   => '2026-07',
            'used_days'     => 1,
        ]);

        $this->assertTrue($limit->leaveBalances()->exists());
        $this->assertEquals(1, $limit->leaveBalances->count());
        $this->assertEquals($balance->id, $limit->leaveBalances->first()->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TESTS: LeaveBalance Model
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function it_can_create_leave_balance()
    {
        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $balance = LeaveBalance::create([
            'user_id'        => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'period_code'    => '2026-07',
            'used_days'      => 2,
            'extra_days'     => 1,
            'dispensation_reason' => 'Dispensasi khusus',
        ]);

        $this->assertDatabaseHas('leave_balances', [
            'id'          => $balance->id,
            'used_days'   => 2,
            'extra_days'  => 1,
        ]);
    }

    /** @test */
    public function leave_balance_belongs_to_user_and_leave_limit()
    {
        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $balance = LeaveBalance::create([
            'user_id'        => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'period_code'    => '2026-07',
        ]);

        $this->assertInstanceOf(User::class, $balance->user);
        $this->assertEquals($this->siswa->id, $balance->user->id);

        $this->assertInstanceOf(LeaveLimit::class, $balance->leaveLimit);
        $this->assertEquals($limit->id, $balance->leaveLimit->id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TESTS: LeaveLimitService
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function get_applicable_limits_returns_only_limits_matching_user_role()
    {
        $service = app(LeaveLimitService::class);

        // Buat aturan untuk siswa
        $siswaLimit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        // Buat aturan untuk guru (tidak berlaku untuk siswa)
        LeaveLimit::factory()->create([
            'target_roles' => ['guru'],
            'leave_type'   => 'all',
            'max_days'     => 5,
            'period'       => 'monthly',
            'action_type'  => 'warning',
            'is_active'    => true,
        ]);

        $applicable = $service->getApplicableLimits($this->siswa);

        $this->assertCount(1, $applicable);
        $this->assertEquals($siswaLimit->id, $applicable->first()->id);
    }

    /** @test */
    public function get_applicable_limits_excludes_inactive_limits()
    {
        $service = app(LeaveLimitService::class);

        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => false,
        ]);

        $applicable = $service->getApplicableLimits($this->siswa);

        $this->assertCount(0, $applicable);
    }

    /** @test */
    public function get_period_code_returns_monthly_format()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'period' => 'monthly',
        ]);

        $code = $service->getPeriodCode($limit);

        // Format: YYYY-MM
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $code);
        $this->assertEquals(now()->format('Y-m'), $code);
    }

    /** @test */
    public function get_period_code_returns_yearly_format()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'period' => 'yearly',
        ]);

        $code = $service->getPeriodCode($limit);

        // Format: YYYY-YYYY
        $this->assertMatchesRegularExpression('/^\d{4}-\d{4}$/', $code);
    }

    /** @test */
    public function get_period_code_returns_semester_format()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'period' => 'semester',
        ]);

        $code = $service->getPeriodCode($limit);

        // Format: YYYY-ganjil atau YYYY-genap
        $this->assertStringContainsString('ganjil', $code);
        $this->assertMatchesRegularExpression('/^\d{4}-(ganjil|genap)$/', $code);
    }

    /** @test */
    public function validate_quota_allows_when_under_limit()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 5,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $result = $service->validateQuota($this->siswa, 'all', 2);

        $this->assertTrue($result['allowed']);
        $this->assertFalse($result['is_overlimit']);
    }

    /** @test */
    public function validate_quota_blocks_when_exceeding_limit_with_block_action()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        // Gunakan 4 hari, melebihi max_days = 3
        $result = $service->validateQuota($this->siswa, 'all', 4);

        $this->assertFalse($result['allowed']);
        $this->assertTrue($result['is_overlimit']);
        $this->assertEquals('block', $result['action_type']);
    }

    /** @test */
    public function validate_quota_returns_warning_when_exceeding_limit_with_warning_action()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['guru'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'warning',
            'is_active'    => true,
        ]);

        $result = $service->validateQuota($this->guru, 'all', 4);

        $this->assertFalse($result['allowed']);
        $this->assertTrue($result['is_overlimit']);
        $this->assertEquals('warning', $result['action_type']);
    }

    /** @test */
    public function validate_quota_allows_when_no_applicable_limit()
    {
        $service = app(LeaveLimitService::class);

        // Tidak ada aturan yang applicable untuk role ini
        $result = $service->validateQuota($this->siswa, 'all', 100);

        $this->assertTrue($result['allowed']);
        $this->assertFalse($result['is_overlimit']);
    }

    /** @test */
    public function deduct_quota_increases_used_days()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 5,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $service->deductQuota($this->siswa, 'all', 2);

        $this->assertDatabaseHas('leave_balances', [
            'user_id'        => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'used_days'      => 2,
        ]);

        // Deduct lagi
        $service->deductQuota($this->siswa, 'all', 1);

        $this->assertDatabaseHas('leave_balances', [
            'user_id'        => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'used_days'      => 3,
        ]);
    }

    /** @test */
    public function add_dispensation_increases_extra_days()
    {
        $service = app(LeaveLimitService::class);

        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 2,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $balance = $service->addDispensation($this->siswa, $limit, 3, 'Dispensasi karena keperluan mendesak');

        $this->assertEquals(3, $balance->extra_days);
        $this->assertEquals('Dispensasi karena keperluan mendesak', $balance->dispensation_reason);

        // Total quota sekarang = max_days(2) + extra_days(3) = 5, jadi 4 hari masih allowed
        $result = $service->validateQuota($this->siswa, 'all', 4);
        $this->assertTrue($result['allowed']);
    }

    /** @test */
    public function get_user_summary_returns_correct_structure()
    {
        $service = app(LeaveLimitService::class);

        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $summary = $service->getUserSummary($this->siswa);

        $this->assertCount(1, $summary);
        $this->assertArrayHasKey('limit_id', $summary[0]);
        $this->assertArrayHasKey('name', $summary[0]);
        $this->assertArrayHasKey('leave_type', $summary[0]);
        $this->assertArrayHasKey('period', $summary[0]);
        $this->assertArrayHasKey('period_code', $summary[0]);
        $this->assertArrayHasKey('max_days', $summary[0]);
        $this->assertArrayHasKey('extra_days', $summary[0]);
        $this->assertArrayHasKey('used_days', $summary[0]);
        $this->assertArrayHasKey('remaining', $summary[0]);
        $this->assertArrayHasKey('action_type', $summary[0]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TESTS: IzinSakit Controller Integration
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function izin_sakit_store_rejects_when_quota_blocked()
    {
        // Buat aturan block untuk siswa
        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 1,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        // Buat siswa record dengan user_id
        $siswaRecord = \App\Models\Siswa::create([
            'user_id'      => $this->siswa->id,
            'nis'          => '12345',
            'nisn'         => '67890',
            'nama_lengkap' => 'Siswa Test',
            'jenis_kelamin'=> 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir'=> '2000-01-01',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.izin-sakit.store'), [
            'tipe'           => 'siswa',
            'reference_id'   => $siswaRecord->id,
            'tanggal_mulai'  => now()->format('Y-m-d'),
            'tanggal_selesai'=> now()->addDays(2)->format('Y-m-d'), // 3 hari > max 1
            'jenis'          => 'izin',
            'keterangan'     => 'Test overlimit',
        ]);

        // Store mengembalikan redirect back dengan flash error (bukan validation error)
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TESTS: LeaveLimitController
    // ─────────────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_view_leave_limits_index()
    {
        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.leave-limits.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_create_leave_limit()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.leave-limits.store'), [
            'name'         => 'Batas Izin Baru',
            'leave_type'   => 'all',
            'max_days'     => 5,
            'period'       => 'monthly',
            'action_type'  => 'warning',
            'target_roles' => ['siswa', 'guru'],
            'is_active'    => true,
        ]);

        $response->assertRedirect(route('admin.leave-limits.index'));
        $this->assertDatabaseHas('leave_limits', [
            'name' => 'Batas Izin Baru',
        ]);
    }

    /** @test */
    public function admin_can_update_leave_limit()
    {
        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('admin.leave-limits.update', $limit->id), [
            'name'         => 'Batas Diperbarui',
            'leave_type'   => 'sick',
            'max_days'     => 7,
            'period'       => 'yearly',
            'action_type'  => 'warning',
            'target_roles' => ['guru'],
            'is_active'    => true,
        ]);

        $response->assertRedirect(route('admin.leave-limits.index'));
        $this->assertDatabaseHas('leave_limits', [
            'id'   => $limit->id,
            'name' => 'Batas Diperbarui',
            'max_days' => 7,
        ]);
    }

    /** @test */
    public function admin_can_delete_leave_limit()
    {
        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $this->actingAs($this->admin);

        $response = $this->delete(route('admin.leave-limits.destroy', $limit->id));

        $response->assertRedirect(route('admin.leave-limits.index'));
        $this->assertDatabaseMissing('leave_limits', ['id' => $limit->id]);
    }

    /** @test */
    public function admin_can_grant_dispensation()
    {
        $limit = LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 2,
            'period'       => 'monthly',
            'action_type'  => 'block',
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.leave-limits.grant-dispensation', $this->siswa->id), [
            'leave_limit_id' => $limit->id,
            'extra_days'     => 5,
            'reason'         => 'Keperluan mendesak keluarga',
        ]);

        $response->assertRedirect(route('admin.leave-limits.index'));
        $this->assertDatabaseHas('leave_balances', [
            'user_id'        => $this->siswa->id,
            'leave_limit_id' => $limit->id,
            'extra_days'     => 5,
        ]);
    }

    /** @test */
    public function check_quota_ajax_returns_correct_json()
    {
        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 3,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.izin-sakit.check-quota', [
            'user_id'    => $this->siswa->id,
            'leave_type' => 'all',
            'start_date' => now()->format('Y-m-d'),
            'end_date'   => now()->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'request_days',
                     'allowed',
                     'is_overlimit',
                     'action_type',
                     'balances',
                     'message',
                 ]);

        $this->assertTrue($response->json('allowed'));
        $this->assertEquals(2, $response->json('request_days'));
    }

    /** @test */
    public function check_quota_returns_not_allowed_when_over_limit()
    {
        LeaveLimit::factory()->create([
            'target_roles' => ['siswa'],
            'leave_type'   => 'all',
            'max_days'     => 1,
            'period'       => 'monthly',
            'action_type'  => 'block',
            'is_active'    => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->getJson(route('admin.izin-sakit.check-quota', [
            'user_id'    => $this->siswa->id,
            'leave_type' => 'all',
            'start_date' => now()->format('Y-m-d'),
            'end_date'   => now()->addDays(2)->format('Y-m-d'), // 3 hari > max 1
        ]));

        $response->assertStatus(200);
        $this->assertFalse($response->json('allowed'));
        $this->assertTrue($response->json('is_overlimit'));
    }
}
