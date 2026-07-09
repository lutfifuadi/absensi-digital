<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSheetsSyncVerifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_siswa_with_aliased_fields()
    {
        // 1. Setup Master Data (Kelas & Tahun Akademik)
        $ta = TahunAkademik::create([
            'nama' => '2024/2025 Ganjil',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2024-07-01',
            'tanggal_selesai' => '2024-12-31',
            'is_aktif' => true,
        ]);

        $kelas = Kelas::create([
            'nama' => 'X-MIPA-1',
            'tingkat' => 'X',
            'jurusan' => 'MIPA',
            'tahun_akademik_id' => $ta->id,
            'is_aktif_absensi' => true,
            'kustomisasi_jam' => false,
        ]);

        // 2. Setup mock rows Google Sheets (key yang dikembalikan oleh fetchData / Google Sheets row)
        $mockRow = [
            'nis' => '998877',
            'nisn' => '0099887766',
            'nama_lengkap' => 'Test Siswa Google Sheet',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '05/05/2010',
            'alamat' => 'Jl. Test No. 9',
            'no_hp' => '08122334455',
            'no_hp_ortu' => '08122334456',
            'kelas' => 'X-MIPA-1',
            'tahun_ajaran' => '2024/2025 Ganjil',
            'status' => 'aktif',
        ];

        // 3. Mock GoogleSheetsService agar tidak memanggil fetchData() yang menggunakan Google Client API
        // Kita menggunakan subclass / partial mock atau direct subclassing agar lebih mudah
        $service = new class extends GoogleSheetsService
        {
            public $mockedRows = [];

            public function fetchData(array $config): array
            {
                return $this->mockedRows;
            }
        };

        $service->mockedRows = [$mockRow];

        $config = [
            'column_mapping' => [
                'nis' => 'nis',
                'nisn' => 'nisn',
                'nama_lengkap' => 'nama_lengkap',
                'jenis_kelamin' => 'jenis_kelamin',
                'tempat_lahir' => 'tempat_lahir',
                'tanggal_lahir' => 'tanggal_lahir',
                'alamat' => 'alamat',
                'no_hp' => 'no_hp',
                'no_hp_ortu' => 'no_hp_ortu',
                'kelas' => 'kelas',
                'tahun_ajaran' => 'tahun_ajaran',
                'status' => 'status',
            ],
        ];

        // 4. Jalankan sinkronisasi
        $result = $service->syncSiswa($config, null, 0, 10);

        // Dump errors if any to see
        if (! empty($result['errors'])) {
            dump($result['errors']);
        }

        // 5. Assertion hasil return value
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEmpty($result['errors']);

        // 6. Verifikasi database: Data Siswa
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0099887766',
            'nis' => '998877',
            'nama_lengkap' => 'Test Siswa Google Sheet',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2010-05-05 00:00:00',
            'alamat' => 'Jl. Test No. 9',
            'no_hp' => '08122334455',
            'no_hp_ortu' => '08122334456',
            'kelas_id' => $kelas->id,
            'tahun_akademik_id' => $ta->id,
            'status' => 'aktif',
        ]);

        // 7. Verifikasi database: User Siswa
        $this->assertDatabaseHas('users', [
            'username' => '0099887766',
            'name' => 'Test Siswa Google Sheet',
            'role' => User::ROLE_SISWA,
        ]);

        // 8. Verifikasi database: User Orang Tua (Wali)
        $this->assertDatabaseHas('users', [
            'username' => 'ortu.0099887766',
            'name' => 'Wali Murid Test Siswa Google Sheet',
            'role' => User::ROLE_ORANG_TUA,
            'no_hp' => '08122334456',
        ]);

        // 9. Pastikan relasi pivot siswa dengan ortu terhubung
        $siswa = Siswa::where('nisn', '0099887766')->first();
        $this->assertNotNull($siswa->ortu_user_id);
        $this->assertEquals(1, $siswa->ortu()->count());
        $this->assertEquals($siswa->ortu_user_id, $siswa->ortu()->first()->id);
    }

    public function test_read_sheet_headers_range_parsing()
    {
        $service = new GoogleSheetsService();

        $testCases = [
            'siswa!A:Z' => 'siswa!A1:Z1',
            'siswa!A1:Z10' => 'siswa!A1:Z1',
            'Siswa' => 'Siswa!A1:Z1',
            'siswa!A1:A:Z1' => 'siswa!A1:Z1',
            'Siswa Kelas 10!A:D' => "'Siswa Kelas 10'!A1:D1",
            "'Siswa Kelas 10'!B2:H9" => "'Siswa Kelas 10'!B1:H1",
            'Sheet1!C:C' => 'Sheet1!C1:Z1',
            'A:Z' => 'Sheet1!A1:Z1',
            '' => 'Sheet1!A1:Z1',
            '   ' => 'Sheet1!A1:Z1',
            'siswa!' => 'siswa!A1:Z1',
            'siswa!1:100' => 'siswa!A1:Z1',
            'siswa!A1' => 'siswa!A1:Z1',
        ];

        foreach ($testCases as $input => $expected) {
            $parsed = $service->parseHeaderRange($input);
            $this->assertEquals($expected, $parsed, "Failed asserting that range parsing for '{$input}' returns '{$expected}', got '{$parsed}'");
        }
    }
}
