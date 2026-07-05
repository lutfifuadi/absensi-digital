<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use App\Models\Pengaturan;
use App\Services\GoogleSheetsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleSheetsGuruSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_guru_success()
    {
        // 1. Setup mock row Google Sheets dengan data guru lengkap
        $mockRow = [
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Guru Hebat Indonesia',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru Utama',
            'no_hp' => '08999999999',
            'status' => 'aktif',
            'email' => 'guru.hebat@sekolah.com',
            'username' => 'guruhebat',
        ];

        // 2. Buat inline class wrapper (subclass) GoogleSheetsService
        $service = new class extends GoogleSheetsService
        {
            public array $mockedRows = [];

            public function fetchData(array $config): array
            {
                return $this->mockedRows;
            }

            public function readSheetHeaders(array $config): array
            {
                if (empty($this->mockedRows)) {
                    return [];
                }
                return array_keys($this->mockedRows[0]);
            }
        };

        $service->mockedRows = [$mockRow];

        $config = [
            'column_mapping' => [
                'nip' => 'nip',
                'nama_lengkap' => 'nama_lengkap',
                'jenis_kelamin' => 'jenis_kelamin',
                'mata_pelajaran' => 'mata_pelajaran',
                'jabatan' => 'jabatan',
                'no_hp' => 'no_hp',
                'status' => 'status',
                'email' => 'email',
                'username' => 'username',
            ],
        ];

        // 3. Jalankan syncGuru
        $result = $service->syncGuru($config, null, 0, 10);

        if (!empty($result['errors'])) {
            dump($result['errors']);
        }

        // 4. Assert return value
        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);

        // 5. Assert database guru
        $this->assertDatabaseHas('guru', [
            'nip' => '198501012010011002',
            'nama_lengkap' => 'Guru Hebat Indonesia',
            'jenis_kelamin' => 'L',
            'mata_pelajaran' => 'Matematika',
            'jabatan' => 'Guru Utama',
            'no_hp' => '08999999999',
            'status' => 'aktif',
        ]);

        // 6. Assert database users (role = 'guru')
        $this->assertDatabaseHas('users', [
            'username' => 'guruhebat',
            'name' => 'Guru Hebat Indonesia',
            'email' => 'guru.hebat@sekolah.com',
            'role' => User::ROLE_GURU,
        ]);
    }

    public function test_sync_guru_normalization_and_defaults()
    {
        // Setup default domain untuk testing email fallback
        Pengaturan::updateOrCreate(
            ['key' => 'website_lembaga'],
            ['value' => 'smpkeren.sch.id', 'group' => 'umum']
        );

        // Setup mock row dengan data non-standar, mapel kosong, email kosong
        $mockRow = [
            'nip' => '199002022015032003',
            'nama' => 'Ibu Guru Normalisasi',
            'jenis_kelamin' => 'Laki-laki',
            'mata_pelajaran' => '',
            'status' => 'Non-aktif',
        ];

        $service = new class extends GoogleSheetsService
        {
            public array $mockedRows = [];

            public function fetchData(array $config): array
            {
                return $this->mockedRows;
            }

            public function readSheetHeaders(array $config): array
            {
                if (empty($this->mockedRows)) {
                    return [];
                }
                return array_keys($this->mockedRows[0]);
            }
        };

        $service->mockedRows = [$mockRow];

        // Kosongkan mapping manual agar memakai auto-detect / alias normal
        $config = [
            'column_mapping' => [],
        ];

        // Jalankan sinkronisasi
        $result = $service->syncGuru($config, null, 0, 10);

        if (!empty($result['errors'])) {
            dump($result['errors']);
        }

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['failed']);

        // Assert data guru yang telah dinormalisasi
        $this->assertDatabaseHas('guru', [
            'nip' => '199002022015032003',
            'nama_lengkap' => 'Ibu Guru Normalisasi',
            'jenis_kelamin' => 'L', // normalisasi dari "Laki-laki" ke "L"
            'mata_pelajaran' => '-', // default "-" karena kosong
            'status' => 'nonaktif', // normalisasi dari "Non-aktif" ke "nonaktif"
        ]);

        // Assert user yang digenerate dengan default email: [nip]@[website_lembaga]
        $this->assertDatabaseHas('users', [
            'username' => '199002022015032003', // username default ke NIP
            'name' => 'Ibu Guru Normalisasi',
            'email' => '199002022015032003@smpkeren.sch.id', // [nip]@[website_lembaga]
            'role' => User::ROLE_GURU,
        ]);
    }

    public function test_sync_guru_validation_error_skips_row()
    {
        // 2 Baris data bermasalah:
        // Baris 1: NIP Kosong
        // Baris 2: Nama Lengkap Kosong
        $mockRows = [
            [
                'nip' => '',
                'nama_lengkap' => 'Guru Tanpa NIP',
                'jenis_kelamin' => 'P',
                'status' => 'aktif',
            ],
            [
                'nip' => '199505052020021001',
                'nama_lengkap' => '',
                'jenis_kelamin' => 'L',
                'status' => 'aktif',
            ]
        ];

        $service = new class extends GoogleSheetsService
        {
            public array $mockedRows = [];

            public function fetchData(array $config): array
            {
                return $this->mockedRows;
            }

            public function readSheetHeaders(array $config): array
            {
                if (empty($this->mockedRows)) {
                    return [];
                }
                return array_keys($this->mockedRows[0]);
            }
        };

        $service->mockedRows = $mockRows;

        $config = [
            'column_mapping' => [
                'nip' => 'nip',
                'nama_lengkap' => 'nama_lengkap',
                'jenis_kelamin' => 'jenis_kelamin',
                'status' => 'status',
            ],
        ];

        $result = $service->syncGuru($config, null, 0, 10);

        // success harus false karena ada baris yang gagal
        $this->assertFalse($result['success']);
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(2, $result['failed']);

        // Assert ada pesan error yang sesuai
        $this->assertContains('NIP kosong pada baris 2', $result['errors']);
        $this->assertContains('Nama lengkap kosong pada baris 3', $result['errors']);

        // Pastikan tidak ada data yang masuk ke database
        $this->assertEquals(0, Guru::count());
    }
}
