<?php

namespace Tests\Feature;

use App\Models\JadwalAbsensi;
use App\Models\Kelas;
use App\Models\KelasJadwalAbsensi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Cases untuk JadwalAbsensiController.
 *
 * PRD-016: Kelola Jam Absensi Per Kelas Per Hari.
 * Menguji semua method controller: index, show, store, storeAll, bulkApply, destroy.
 */
class JadwalAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected TahunAkademik $tahunAkademik;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat User Admin
        $this->admin = User::create([
            'name'             => 'Admin Sekolah',
            'username'         => 'admin_sekolah',
            'email'            => 'admin@sekolah.sch.id',
            'password'         => bcrypt('password123'),
            'role'             => User::ROLE_ADMIN_SEKOLAH,
            'email_verified_at' => now(),
        ]);

        // Buat Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama'            => '2026/2027',
            'semester'        => 'ganjil',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-12-31',
            'is_aktif'        => true,
        ]);

        // Buat Kelas
        $this->kelas = Kelas::create([
            'nama'               => 'X MIPA 1',
            'tingkat'            => 'X',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Set session tahun akademik untuk controller index
        $this->withSession([
            'tahun_akademik_id'  => $this->tahunAkademik->id,
            'active_role'        => User::ROLE_ADMIN_SEKOLAH,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST INDEX
    // ════════════════════════════════════════════════════════════════════════

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('admin.jadwal-absensi.index'));

        $response->assertRedirect();
    }

    public function test_index_requires_admin_role(): void
    {
        $siswaUser = User::create([
            'name'             => 'Siswa Test',
            'username'         => 'siswa_test',
            'email'            => 'siswa@test.com',
            'password'         => bcrypt('password'),
            'role'             => User::ROLE_SISWA,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($siswaUser)
            ->withSession(['active_role' => User::ROLE_SISWA])
            ->get(route('admin.jadwal-absensi.index'));

        $response->assertStatus(403);
    }

    public function test_index_displays_kelas_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal-absensi.index'));

        $response->assertStatus(200);
        $response->assertSee('X MIPA 1');
    }

    public function test_index_supports_pagination(): void
    {
        // Buat 15 kelas tambahan
        for ($i = 0; $i < 15; $i++) {
            Kelas::create([
                'nama'               => "Kelas Test {$i}",
                'tingkat'            => 'X',
                'jurusan'            => 'MIPA',
                'tahun_akademik_id'  => $this->tahunAkademik->id,
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal-absensi.index', ['per_page' => 10]));

        $response->assertStatus(200);
    }

    public function test_index_supports_search(): void
    {
        // Buat kelas dengan nama unik
        Kelas::create([
            'nama'               => 'XI TKJ 1',
            'tingkat'            => 'XI',
            'jurusan'            => 'TKJ',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal-absensi.index', ['search' => 'TKJ']));

        $response->assertStatus(200);
        $response->assertSee('XI TKJ 1');
    }

    public function test_index_supports_filter_tingkat(): void
    {
        // Buat kelas tingkat XI
        Kelas::create([
            'nama'               => 'XI MIPA 1',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.jadwal-absensi.index', ['tingkat' => 'XI']));

        $response->assertStatus(200);
        $response->assertSee('XI MIPA 1');
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST SHOW
    // ════════════════════════════════════════════════════════════════════════

    public function test_show_requires_authentication(): void
    {
        $response = $this->get(route('admin.jadwal-absensi.show', $this->kelas));

        $response->assertRedirect();
    }

    public function test_show_displays_jadwal_7_hari(): void
    {
        // Buat jadwal untuk Senin
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:00',
            'jam_masuk'          => '07:00',
            'jam_pulang'         => '15:00',
            'jam_akhir_pulang'   => '17:00',
            'is_libur'           => false,
        ]);

        // Gunakan JSON request karena view belum tersedia (frontend belum dikerjakan)
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jadwal-absensi.show', $this->kelas));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        // Pastikan data senin ada di response
        $jadwal = $response->json('data.jadwal');
        $this->assertNotNull($jadwal['senin']['id']);
    }

    public function test_show_returns_json_for_ajax(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jadwal-absensi.show', $this->kelas));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'kelas' => ['id', 'nama'],
                'jadwal',
            ],
        ]);
    }

    public function test_show_returns_empty_when_no_jadwal(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.jadwal-absensi.show', $this->kelas));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Semua hari harusnya null/default
        $jadwal = $response->json('data.jadwal');
        $this->assertArrayHasKey('senin', $jadwal);
        $this->assertArrayHasKey('minggu', $jadwal);
        // Untuk hari tanpa jadwal, id harus null
        $this->assertNull($jadwal['senin']['id']);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST STORE
    // ════════════════════════════════════════════════════════════════════════

    public function test_store_requires_authentication(): void
    {
        $response = $this->postJson(route('admin.jadwal-absensi.store'), [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'senin',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kelas_id', 'hari']);
    }

    public function test_store_validates_hari_enum(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id' => $this->kelas->id,
                'hari'     => 'invalid_day',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['hari']);
    }

    public function test_store_validates_time_format(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'          => $this->kelas->id,
                'hari'              => 'senin',
                'jam_mulai_absensi' => 'invalid-time',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jam_mulai_absensi']);
    }

    public function test_store_creates_new_jadwal(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'          => $this->kelas->id,
                'hari'              => 'senin',
                'jam_mulai_absensi' => '06:00',
                'jam_masuk'         => '07:00',
                'jam_pulang'        => '15:00',
                'jam_akhir_pulang'  => '17:00',
                'is_libur'          => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'senin',
            'jam_masuk' => '07:00',
        ]);
    }

    public function test_store_updates_existing_jadwal(): void
    {
        // Buat jadwal awal
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:00',
            'jam_masuk'          => '07:00',
            'jam_pulang'         => '15:00',
            'jam_akhir_pulang'   => '17:00',
            'is_libur'           => false,
        ]);

        // Update jadwal
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'          => $this->kelas->id,
                'hari'              => 'senin',
                'jam_mulai_absensi' => '06:30',
                'jam_masuk'         => '07:30',
                'jam_pulang'        => '15:30',
                'jam_akhir_pulang'  => '17:30',
                'is_libur'          => false,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Pastikan hanya 1 record (update, bukan create baru)
        $count = KelasJadwalAbsensi::where('kelas_id', $this->kelas->id)
            ->where('hari', 'senin')
            ->count();
        $this->assertEquals(1, $count);

        // Pastikan data ter-update
        $jadwal = KelasJadwalAbsensi::where('kelas_id', $this->kelas->id)
            ->where('hari', 'senin')
            ->first();
        $this->assertEquals('07:30', $jadwal->jam_masuk);
    }

    public function test_store_returns_json_response(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'          => $this->kelas->id,
                'hari'              => 'senin',
                'jam_mulai_absensi' => '06:00',
                'jam_masuk'         => '07:00',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }

    public function test_store_validates_time_sequence_jam_mulai_lebih_besar_dari_jam_masuk(): void
    {
        // BR-03: jam_mulai_absensi harus <= jam_masuk
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'          => $this->kelas->id,
                'hari'              => 'senin',
                'jam_mulai_absensi' => '08:00',
                'jam_masuk'         => '07:00',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_store_validates_time_sequence_jam_pulang_lebih_besar_dari_jam_akhir(): void
    {
        // BR-04: jam_pulang harus <= jam_akhir_pulang
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id'         => $this->kelas->id,
                'hari'             => 'senin',
                'jam_pulang'       => '18:00',
                'jam_akhir_pulang' => '17:00',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_store_sets_is_libur_true(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id' => $this->kelas->id,
                'hari'     => 'sabtu',
                'is_libur' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id'  => $this->kelas->id,
            'hari'      => 'sabtu',
            'is_libur'  => 1,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST STORE ALL
    // ════════════════════════════════════════════════════════════════════════

    public function test_store_all_requires_authentication(): void
    {
        $response = $this->postJson(route('admin.jadwal-absensi.store-all'), [
            'kelas_id' => $this->kelas->id,
            'jadwal'   => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_store_all_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kelas_id', 'jadwal']);
    }

    public function test_store_all_validates_array(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => 'not-an-array',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jadwal']);
    }

    public function test_store_all_creates_multiple_jadwal(): void
    {
        $jadwalData = [
            ['hari' => 'senin', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['hari' => 'selasa', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
            ['hari' => 'rabu', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => $jadwalData,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseCount('kelas_jadwal_absensi', 3);
        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'senin',
            'jam_masuk' => '07:00',
        ]);
        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'selasa',
        ]);
        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'rabu',
        ]);
    }

    public function test_store_all_handles_existing_jadwal(): void
    {
        // Buat jadwal awal untuk senin
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
            'is_libur'   => false,
        ]);

        // Simpan semua hari termasuk senin (harus update)
        $jadwalData = [
            ['hari' => 'senin', 'jam_masuk' => '08:00', 'jam_pulang' => '16:00'],
            ['hari' => 'selasa', 'jam_masuk' => '07:00', 'jam_pulang' => '15:00'],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => $jadwalData,
            ]);

        $response->assertStatus(200);

        // Harusnya tetap 2 record (update senin, create selasa)
        $this->assertDatabaseCount('kelas_jadwal_absensi', 2);

        // Senin harus ter-update
        $jadwalSenin = KelasJadwalAbsensi::where('kelas_id', $this->kelas->id)
            ->where('hari', 'senin')
            ->first();
        $this->assertEquals('08:00', $jadwalSenin->jam_masuk);
    }

    public function test_store_all_returns_json_response(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => [
                    ['hari' => 'senin', 'jam_masuk' => '07:00'],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST BULK APPLY
    // ════════════════════════════════════════════════════════════════════════

    public function test_bulk_apply_requires_authentication(): void
    {
        $response = $this->postJson(route('admin.jadwal-absensi.bulk-apply'), [
            'source_kelas_id'  => $this->kelas->id,
            'target_kelas_ids' => [],
        ]);

        $response->assertStatus(401);
    }

    public function test_bulk_apply_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_kelas_id', 'target_kelas_ids']);
    }

    public function test_bulk_apply_validates_source_kelas_exists(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => 99999,
                'target_kelas_ids' => [$this->kelas->id],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source_kelas_id']);
    }

    public function test_bulk_apply_validates_target_kelas_exists(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [99999],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['target_kelas_ids.0']);
    }

    public function test_bulk_apply_copies_jadwal_to_target_kelas(): void
    {
        // Buat kelas tujuan
        $targetKelas = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Buat jadwal di kelas sumber
        KelasJadwalAbsensi::create([
            'kelas_id'           => $this->kelas->id,
            'hari'               => 'senin',
            'jam_mulai_absensi'  => '06:30',
            'jam_masuk'          => '07:30',
            'jam_pulang'         => '15:30',
            'jam_akhir_pulang'   => '17:30',
            'is_libur'           => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [$targetKelas->id],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Cek jadwal tersalin ke kelas tujuan
        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id'  => $targetKelas->id,
            'hari'      => 'senin',
            'jam_masuk' => '07:30',
        ]);
    }

    public function test_bulk_apply_does_not_copy_to_source(): void
    {
        // Buat kelas tujuan = kelas sumber (sama)
        // Harus skip dan tidak error

        // Buat jadwal di kelas sumber
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
            'is_libur'   => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [$this->kelas->id],
            ]);

        $response->assertStatus(200);

        // Harusnya tidak ada duplikasi
        $count = KelasJadwalAbsensi::where('kelas_id', $this->kelas->id)
            ->where('hari', 'senin')
            ->count();
        $this->assertEquals(1, $count);
    }

    public function test_bulk_apply_fails_when_source_has_no_jadwal(): void
    {
        $targetKelas = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [$targetKelas->id],
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_bulk_apply_returns_json_response(): void
    {
        $targetKelas = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
            'is_libur'   => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [$targetKelas->id],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_copied',
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST DESTROY
    // ════════════════════════════════════════════════════════════════════════

    public function test_destroy_requires_authentication(): void
    {
        $response = $this->deleteJson(route('admin.jadwal-absensi.destroy', $this->kelas));

        $response->assertStatus(401);
    }

    public function test_destroy_deletes_all_jadwal_for_kelas(): void
    {
        // Buat beberapa jadwal
        $hariList = ['senin', 'selasa', 'rabu'];
        foreach ($hariList as $hari) {
            KelasJadwalAbsensi::create([
                'kelas_id'   => $this->kelas->id,
                'hari'       => $hari,
                'jam_masuk'  => '07:00',
                'jam_pulang' => '15:00',
                'is_libur'   => false,
            ]);
        }

        $this->assertDatabaseCount('kelas_jadwal_absensi', 3);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.jadwal-absensi.destroy', $this->kelas));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Semua jadwal harus terhapus
        $this->assertDatabaseCount('kelas_jadwal_absensi', 0);
        $this->assertDatabaseMissing('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
        ]);
    }

    public function test_destroy_returns_json_response(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.jadwal-absensi.destroy', $this->kelas));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    public function test_destroy_does_not_affect_other_kelas(): void
    {
        // Buat kelas lain
        $otherKelas = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Buat jadwal untuk kedua kelas
        KelasJadwalAbsensi::create([
            'kelas_id'   => $this->kelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '07:00',
            'jam_pulang' => '15:00',
        ]);

        KelasJadwalAbsensi::create([
            'kelas_id'   => $otherKelas->id,
            'hari'       => 'senin',
            'jam_masuk'  => '08:00',
            'jam_pulang' => '16:00',
        ]);

        // Hapus jadwal kelas pertama
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.jadwal-absensi.destroy', $this->kelas));

        $response->assertStatus(200);

        // Jadwal kelas lain masih ada
        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $otherKelas->id,
            'hari'     => 'senin',
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // TEST EDGE CASES & BUSINESS RULES
    // ════════════════════════════════════════════════════════════════════════

    public function test_store_all_validates_hari_enum_in_array(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => [
                    ['hari' => 'invalid_day', 'jam_masuk' => '07:00'],
                ],
            ]);

        $response->assertStatus(422);
    }

    public function test_store_validates_kelas_exists(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id' => 99999,
                'hari'     => 'senin',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kelas_id']);
    }

    public function test_bulk_apply_returns_message_with_correct_count(): void
    {
        $target1 = Kelas::create([
            'nama'               => 'XI MIPA 2',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        $target2 = Kelas::create([
            'nama'               => 'XI MIPA 3',
            'tingkat'            => 'XI',
            'jurusan'            => 'MIPA',
            'tahun_akademik_id'  => $this->tahunAkademik->id,
        ]);

        // Buat 3 jadwal di kelas sumber
        foreach (['senin', 'selasa', 'rabu'] as $hari) {
            KelasJadwalAbsensi::create([
                'kelas_id'   => $this->kelas->id,
                'hari'       => $hari,
                'jam_masuk'  => '07:00',
                'jam_pulang' => '15:00',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.bulk-apply'), [
                'source_kelas_id'  => $this->kelas->id,
                'target_kelas_ids' => [$target1->id, $target2->id],
            ]);

        $response->assertStatus(200);

        // 3 jadwal x 2 target = 6 total copied
        $this->assertEquals(6, $response->json('data.total_copied'));
    }

    public function test_store_all_returns_error_on_invalid_time_sequence(): void
    {
        $jadwalData = [
            ['hari' => 'senin', 'jam_mulai_absensi' => '08:00', 'jam_masuk' => '07:00'],
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store-all'), [
                'kelas_id' => $this->kelas->id,
                'jadwal'   => $jadwalData,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_store_all_saturday_default_is_libur(): void
    {
        // Test bahwa admin bisa set is_libur untuk sabtu
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.jadwal-absensi.store'), [
                'kelas_id' => $this->kelas->id,
                'hari'     => 'sabtu',
                'is_libur' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kelas_jadwal_absensi', [
            'kelas_id' => $this->kelas->id,
            'hari'     => 'sabtu',
            'is_libur' => 1,
        ]);
    }
}
