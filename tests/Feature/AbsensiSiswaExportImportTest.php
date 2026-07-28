<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AbsensiSiswaExportImportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $kelas;
    protected $siswa1;
    protected $siswa2;
    protected $tahunAkademik;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tahunAkademik = TahunAkademik::create([
            'nama'            => '2026/2027 Ganjil',
            'is_aktif'        => true,
            'tanggal_mulai'   => now()->subMonth(),
            'tanggal_selesai' => now()->addMonths(5),
        ]);
        session(['tahun_akademik_id' => $this->tahunAkademik->id]);
        session(['tahun_ajaran_id' => $this->tahunAkademik->id]);

        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->kelas = Kelas::create([
            'nama'              => 'X RPL 1',
            'tingkat'           => '10',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi'  => true,
        ]);

        $this->siswa1 = Siswa::create([
            'nama_lengkap'      => 'Budi Santoso',
            'nis'               => '11111',
            'nisn'              => '0051234001',
            'jenis_kelamin'     => 'L',
            'tempat_lahir'      => 'Bandung',
            'tanggal_lahir'     => '2008-05-15',
            'kelas_id'          => $this->kelas->id,
            'status'            => 'aktif',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        $this->siswa2 = Siswa::create([
            'nama_lengkap'      => 'Ani Retno',
            'nis'               => '22222',
            'nisn'              => '0051234002',
            'jenis_kelamin'     => 'P',
            'tempat_lahir'      => 'Bandung',
            'tanggal_lahir'     => '2008-06-15',
            'kelas_id'          => $this->kelas->id,
            'status'            => 'aktif',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);
    }

    public function test_admin_can_export_absensi_siswa()
    {
        Excel::fake();

        AbsensiSiswa::create([
            'siswa_id' => $this->siswa1->id,
            'kelas_id' => $this->kelas->id,
            'tanggal' => '2026-07-28',
            'status' => 'hadir',
            'jam_masuk' => '07:00',
            'metode' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.absensi-siswa.export', [
                'kelas_id' => $this->kelas->id,
                'tanggal_from' => '2026-07-28',
                'tanggal_to' => '2026-07-28',
            ]));

        $response->assertStatus(200);
        Excel::assertDownloaded('absensi-siswa-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function test_admin_can_download_import_template()
    {
        Excel::fake();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.absensi-siswa.download-template'));

        $response->assertStatus(200);
        Excel::assertDownloaded('template-import-absensi.xlsx');
    }

    public function test_admin_can_import_absensi_siswa()
    {
        $fakeExcelContent = [
            ['tanggal', 'nis', 'status', 'jam_masuk', 'jam_pulang', 'keterangan'],
            ['2026-07-28', '11111', 'hadir', '07:00', '14:00', 'Hadir'],
            ['2026-07-28', '22222', 'sakit', '', '', 'Demam'],
        ];

        // Create a temporary excel/csv file for upload
        $tempFile = tempnam(sys_get_temp_dir(), 'import_absensi');
        $handle = fopen($tempFile, 'w');
        foreach ($fakeExcelContent as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        $uploadedFile = new UploadedFile(
            $tempFile,
            'import_absensi.csv',
            'text/csv',
            null,
            true
        );

        $response = $this->actingAs($this->admin)
            ->post(route('admin.absensi-siswa.import'), [
                'file' => $uploadedFile,
            ]);

        @unlink($tempFile);

        $response->assertRedirect(route('admin.absensi-siswa.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa1->id,
            'tanggal' => '2026-07-28 00:00:00',
            'status' => 'hadir',
            'jam_masuk' => '07:00',
            'jam_pulang' => '14:00',
            'metode' => 'import',
        ]);

        $this->assertDatabaseHas('absensi_siswa', [
            'siswa_id' => $this->siswa2->id,
            'tanggal' => '2026-07-28 00:00:00',
            'status' => 'sakit',
            'keterangan' => 'Demam',
            'metode' => 'import',
        ]);
    }
}
