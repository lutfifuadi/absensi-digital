<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesAbsensiPerJamData;
use Tests\TestCase;

/**
 * PRD-006 (P0) — Feature test: index, show, store, otorisasi F-3, rekap F-5.
 */
class AbsensiPerJamTest extends TestCase
{
    use RefreshDatabase;
    use CreatesAbsensiPerJamData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAbsensiPerJamData();
    }

    protected function tearDown(): void
    {
        $this->unfreeze();
        parent::tearDown();
    }

    private function storePayload(array $rows = [], string $tanggal = null, string $metode = 'manual'): array
    {
        $tanggal ??= $this->today;

        return [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'tanggal'             => $tanggal,
            'metode'              => $metode,
            'rows'                => $rows !== [] ? $rows : [
                ['siswa_id' => $this->siswaA1->id, 'status' => 'hadir'],
                ['siswa_id' => $this->siswaA2->id, 'status' => 'hadir'],
            ],
        ];
    }

    // ── B.1 Index ─────────────────────────────────────────────────────────

    public function test_guru_index_hanya_menampilkan_jadwal_miliknya()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->get(route('guru.absensi-per-jam'));

        $response->assertStatus(200);
        $response->assertSee('Informatika');
        $response->assertSee('Fisika');
        $response->assertDontSee('Matematika');
    }

    public function test_piket_index_menampilkan_semua_jadwal()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->piketUser)
            ->get(route('admin.absensi-per-jam.index'));

        $response->assertStatus(200);
        $response->assertSee('Informatika');
        $response->assertSee('Matematika');
        $response->assertSee('Fisika');
    }

    public function test_operator_index_menampilkan_semua_jadwal()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->operatorUser)
            ->get(route('admin.absensi-per-jam.index'));

        $response->assertStatus(200);
        $response->assertSee('Matematika');
    }

    public function test_index_tanggal_tanpa_jadwal_menampilkan_empty_state()
    {
        $this->freezeAt();
        $kemarin = now()->subDay()->toDateString();

        $response = $this->actingAs($this->piketUser)
            ->get(route('admin.absensi-per-jam.index', ['tanggal' => $kemarin]));

        $response->assertStatus(200);
        $response->assertSee('Tidak ada jadwal pada tanggal ini.');
    }

    public function test_wali_kelas_diblokir_route_middleware()
    {
        $this->freezeAt();

        $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.index'))
            ->assertStatus(403);
    }

    // ── B.2 Show (form roster) ────────────────────────────────────────────

    public function test_guru_dapat_membuka_form_jadwal_miliknya()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalA1));

        $response->assertStatus(200);
        $response->assertSee('Siswa A1');
        $response->assertSee('Siswa A2');
        $response->assertSee('Simpan Absensi');
    }

    public function test_guru_tidak_dapat_membuka_form_jadwal_guru_lain()
    {
        $this->freezeAt();

        $this->actingAs($this->guruAUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalA2))
            ->assertStatus(403);

        $this->actingAs($this->guruBUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalB1))
            ->assertStatus(403);
    }

    public function test_piket_dapat_membuka_form_hari_ini()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->piketUser)
            ->get(route('admin.absensi-per-jam.show', ['jadwal' => $this->jadwalA1, 'tanggal' => $this->today]));

        $response->assertStatus(200);
        $response->assertSee('Siswa A1');
    }

    public function test_piket_tidak_dapat_membuka_form_tanggal_selain_hari_ini()
    {
        $this->freezeAt();
        $kemarin = now()->subDay()->toDateString();

        $this->actingAs($this->piketUser)
            ->get(route('admin.absensi-per-jam.show', ['jadwal' => $this->jadwalA1, 'tanggal' => $kemarin]))
            ->assertStatus(403);
    }

    public function test_admin_dapat_membuka_form_jadwal_siapa_pun()
    {
        $this->freezeAt();

        $this->actingAs($this->adminUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalA2))
            ->assertStatus(200);
    }

    public function test_operator_tidak_dapat_membuka_form_jadwal()
    {
        $this->freezeAt();

        // Operator lolos route middleware tetapi DITOLAK policy → dead-end (temuan)
        $this->actingAs($this->operatorUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalA1))
            ->assertStatus(403);
    }

    public function test_wali_kelas_tidak_dapat_membuka_form_jadwal()
    {
        $this->freezeAt();

        // F-3: wali_kelas TIDAK boleh mengisi absensi → diblokir middleware Grup A
        $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.show', $this->jadwalA1))
            ->assertStatus(403);
    }

    public function test_wali_kelas_tidak_dapat_menyimpan_absensi()
    {
        $this->freezeAt();

        $this->actingAs($this->waliKelasUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload())
            ->assertStatus(403);

        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    // ── B.3 Store ─────────────────────────────────────────────────────────

    public function test_guru_dapat_menyimpan_absensi_jadwal_miliknya()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Absensi berhasil disimpan: 2 siswa.');

        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $this->today,
            'status'              => 'hadir',
            'metode'              => 'manual',
            'dicatat_oleh'        => $this->guruAUser->id,
        ]);
        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA2->id,
            'tanggal'             => $this->today,
            'status'              => 'hadir',
        ]);
    }

    public function test_guru_tidak_dapat_menyimpan_jadwal_guru_lain()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA2), $this->storePayload());

        // Policy AuthorizationException ditangkap → redirect + flash error (BUKAN 403)
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA2->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_guru_dapat_menyimpan_absensi_otomatis_single_row_via_ajax()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->postJson(route('admin.absensi-per-jam.store-single', $this->jadwalA1), [
                'tanggal'  => $this->today,
                'siswa_id' => $this->siswaA1->id,
                'status'   => 'hadir',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Absensi berhasil diperbarui.',
        ]);

        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $this->today,
            'status'              => 'hadir',
        ]);
    }

    public function test_guru_pengganti_dapat_menyimpan_jadwal_yang_digantikannya()
    {
        // jadwalA2 = 09:00-10:30 → window valid; freeze di jam pelajaran penggantian
        $this->freezeAt('09:30:00');

        $this->insertMonitoringPengganti($this->jadwalA2->id, $this->guruA->id, $this->today, $this->guruA->nama_lengkap);

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA2), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA2->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_piket_dapat_menyimpan_absensi_hari_ini()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->piketUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_guru_ditolak_di_luar_window_waktu()
    {
        $this->freezeAt('06:30:00');

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gagal menyimpan absensi: Pengisian hanya dapat dilakukan pada jam pelajaran (dengan toleransi waktu).');
        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_guru_ditolak_simpan_pada_hari_libur()
    {
        $this->freezeAt();

        Holiday::create([
            'tanggal'             => $this->today,
            'nama'                => 'Libur Nasional',
            'jenis'               => 'national',
            'is_national_holiday' => true,
        ]);

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gagal menyimpan absensi: Hari ini merupakan hari libur kelas. (Admin dapat override dengan konfirmasi.)');
        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_admin_tetap_bisa_menyimpan_saat_hari_libur()
    {
        $this->freezeAt();

        Holiday::create([
            'tanggal'             => $this->today,
            'nama'                => 'Libur Nasional',
            'jenis'               => 'national',
            'is_national_holiday' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $this->today,
        ]);
    }

    public function test_guru_ditolak_menyimpan_tanggal_masa_depan()
    {
        $this->freezeAt();
        $besok = now()->addDay()->toDateString();

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload([], $besok));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Gagal menyimpan absensi: Tidak dapat mengisi absensi untuk tanggal yang akan datang.');
        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'tanggal'             => $besok,
        ]);
    }

    public function test_admin_bebas_menyimpan_tanggal_masa_depan()
    {
        $this->freezeAt();
        $besok = now()->addDay()->toDateString();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), $this->storePayload([], $besok));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaA1->id,
            'tanggal'             => $besok,
        ]);
    }

    public function test_store_siswa_beda_kelas_dilewati_dengan_peringatan()
    {
        $this->freezeAt();

        $response = $this->actingAs($this->guruAUser)
            ->post(route('admin.absensi-per-jam.store', $this->jadwalA1), [
                'jadwal_pelajaran_id' => $this->jadwalA1->id,
                'tanggal'             => $this->today,
                'metode'              => 'manual',
                'rows'                => [
                    ['siswa_id' => $this->siswaA1->id, 'status' => 'hadir'],
                    ['siswa_id' => $this->siswaB1->id, 'status' => 'hadir'], // kelas B ≠ kelas A
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertStringContainsString('1 baris dilewati', session('success'));
        $this->assertDatabaseMissing('absensi_siswa_per_jadwal', [
            'jadwal_pelajaran_id' => $this->jadwalA1->id,
            'siswa_id'            => $this->siswaB1->id,
        ]);
    }

    // ── B.4 Rekap (F-5) ───────────────────────────────────────────────────

    public function test_rekap_page_tanpa_filter_tetap_200()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.absensi-per-jam.rekap'));

        $response->assertStatus(200);
    }

    public function test_guru_dapat_melihat_rekap_kelas_yang_diajar()
    {
        $response = $this->actingAs($this->guruAUser)
            ->get(route('admin.absensi-per-jam.rekap', ['kelas_id' => $this->kelasA->id]));

        $response->assertStatus(200);
    }

    public function test_guru_tidak_dapat_melihat_rekap_kelas_yang_tidak_diajar()
    {
        // guruB hanya mengajar kelasA → rekap kelasB harus 403
        $response = $this->actingAs($this->guruBUser)
            ->get(route('admin.absensi-per-jam.rekap', ['kelas_id' => $this->kelasB->id]));

        $response->assertStatus(403);
    }

    public function test_wali_kelas_dapat_melihat_rekap_kelas_asuhannya()
    {
        // BUG-1 sudah diperbaiki Kang Bayu: middleware Grup B kini menyertakan wali_kelas.
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap', ['kelas_id' => $this->kelasA->id]));

        $response->assertStatus(200);
        // Dropdown hanya kelas asuhan → kelasA tampil
        $response->assertSee($this->kelasA->nama);
    }

    public function test_wali_kelas_dropdown_rekap_hanya_kelas_asuhan()
    {
        // AC-F5-4: dropdown kelas di-scope ke kelas asuhan (wali_kelas_id == guru->id)
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap'));

        $response->assertStatus(200);
        $response->assertSee($this->kelasA->nama);   // kelas asuhan tampil
        $response->assertDontSee($this->kelasB->nama); // kelas lain TIDAK tampil
    }

    public function test_wali_kelas_tidak_dapat_melihat_rekap_kelas_lain()
    {
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap', ['kelas_id' => $this->kelasB->id]));

        $response->assertStatus(403);
    }

    public function test_operator_dapat_melihat_rekap_semua_kelas()
    {
        $response = $this->actingAs($this->operatorUser)
            ->get(route('admin.absensi-per-jam.rekap', ['kelas_id' => $this->kelasB->id]));

        $response->assertStatus(200);
    }

    public function test_rekap_menampilkan_matriks_dan_akumulasi()
    {
        $mingguLalu = now()->subDays(7)->toDateString();

        $this->insertAbsensiPerJadwal($this->jadwalA1->id, $this->siswaA1->id, $mingguLalu, 'hadir');
        $this->insertAbsensiPerJadwal($this->jadwalA1->id, $this->siswaA1->id, $this->today, 'alpha');

        $dari = now()->subDays(14)->toDateString();
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.absensi-per-jam.rekap', [
                'kelas_id' => $this->kelasA->id,
                'dari'     => $dari,
                'sampai'   => $this->today,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Siswa A1');
        // 1 hadir + 0 terlambat dari 2 total → 50%
        $response->assertSee('50.0%');
        $response->assertSee('H');
    }

    // ── B.5 Rekap per Siswa ───────────────────────────────────────────────

    public function test_guru_dapat_melihat_rekap_siswa_di_kelas_yang_diajar()
    {
        $response = $this->actingAs($this->guruAUser)
            ->get(route('admin.absensi-per-jam.rekap.siswa', $this->siswaA1));

        $response->assertStatus(200);
        $response->assertSee('Siswa A1');
    }

    public function test_wali_kelas_tidak_dapat_melihat_rekap_siswa_kelas_lain()
    {
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap.siswa', $this->siswaB1));

        $response->assertStatus(403);
    }

    public function test_wali_kelas_dapat_melihat_rekap_siswa_kelas_asuhan()
    {
        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap.siswa', $this->siswaA1));

        $response->assertStatus(200);
        $response->assertSee('Siswa A1');
    }

    // ── B.6 Export Excel ──────────────────────────────────────────────────

    public function test_export_tanpa_filter_dikembalikan_ke_rekap()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.absensi-per-jam.rekap.export'));

        $response->assertRedirect(route('admin.absensi-per-jam.rekap'));
        $response->assertSessionHas('error', 'Pilih kelas dan rentang tanggal dulu.');
    }

    public function test_export_excel_mengembalikan_file()
    {
        $dari = now()->subDays(14)->toDateString();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.absensi-per-jam.rekap.export', [
                'kelas_id' => $this->kelasA->id,
                'dari'     => $dari,
                'sampai'   => $this->today,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_export_ditolak_untuk_kelas_yang_tidak_diajar_guru()
    {
        $dari = now()->subDays(14)->toDateString();

        $response = $this->actingAs($this->guruBUser)
            ->get(route('admin.absensi-per-jam.rekap.export', [
                'kelas_id' => $this->kelasB->id,
                'dari'     => $dari,
                'sampai'   => $this->today,
            ]));

        $response->assertStatus(403);
    }

    public function test_wali_kelas_dapat_export_rekap_kelas_asuhan()
    {
        $dari = now()->subDays(14)->toDateString();

        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap.export', [
                'kelas_id' => $this->kelasA->id,
                'dari'     => $dari,
                'sampai'   => $this->today,
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_wali_kelas_tidak_dapat_export_rekap_kelas_lain()
    {
        $dari = now()->subDays(14)->toDateString();

        $response = $this->actingAs($this->waliKelasUser)
            ->get(route('admin.absensi-per-jam.rekap.export', [
                'kelas_id' => $this->kelasB->id,
                'dari'     => $dari,
                'sampai'   => $this->today,
            ]));

        $response->assertStatus(403);
    }
}
