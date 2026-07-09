<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SiswaImportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $tahunAkademik;
    protected $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for authentication
        $this->admin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        // Create standard Tahun Akademik
        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025-2026',
            'semester' => 'genap',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_selesai' => '2026-06-30',
            'is_aktif' => true,
        ]);

        // Create a Kelas
        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => 'X',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'is_aktif_absensi' => true,
            'kustomisasi_jam' => false,
        ]);
    }

    public function test_import_validation_and_flow()
    {
        $csvContent = "nis,nisn,nama_lengkap,jenis_kelamin,tempat_lahir,tanggal_lahir,alamat,no_hp,no_hp_ortu,kelas,tahun_ajaran,status\n"
            . "\"12345\",\"0012345678\",\"Ahmad Siswa Sampel\",\"L\",\"Jakarta\",\"01/01/2010\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n";

        // Let's use UploadedFile::fake() with a proper extension and mime type
        $uploadedFile = UploadedFile::fake()->create('siswa.csv', 10, 'text/csv');
        
        // We write the content to this fake file
        file_put_contents($uploadedFile->getRealPath(), $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.siswa.import.store'), [
                'import_file' => $uploadedFile,
            ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345678',
            'nama_lengkap' => 'Ahmad Siswa Sampel',
            'kelas_id' => $this->kelas->id,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => '0012345678',
            'role' => User::ROLE_SISWA,
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'ortu.0012345678',
            'role' => User::ROLE_ORANG_TUA,
            'no_hp' => '08123456780',
        ]);
    }

    public function test_import_partial_success_reports_errors()
    {
        // Line 2 has valid data, Line 3 has invalid kelas, Line 4 has invalid gender
        // Note: Maatwebsite Excel's WithValidation runs validation on the entire sheet first if WithValidation is used.
        // Wait, if it runs validation first, it throws ExcelValidationException.
        // Therefore, if any row fails validation rules, the whole import throws ExcelValidationException.
        // But if validation succeeds, but model resolution or carbon parsing fails, it's caught in ToModel and reports partial errors.
        // Let's test with a validation-passing but parsing-failing row (e.g. invalid date format).
        $csvContent = "nis,nisn,nama_lengkap,jenis_kelamin,tempat_lahir,tanggal_lahir,alamat,no_hp,no_hp_ortu,kelas,tahun_ajaran,status\n"
            . "\"12345\",\"0012345678\",\"Siswa Satu\",\"L\",\"Jakarta\",\"01/01/2010\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n"
            . "\"12346\",\"0012345679\",\"Siswa Dua\",\"L\",\"Jakarta\",\"TANGGAL_SALAH\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n";

        // Let's use UploadedFile::fake() with a proper extension and mime type
        $uploadedFile = UploadedFile::fake()->create('siswa.csv', 10, 'text/csv');
        
        // We write the content to this fake file
        file_put_contents($uploadedFile->getRealPath(), $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.siswa.import.store'), [
                'import_file' => $uploadedFile,
            ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }

        // Should return 200/success since it's a partial import flow
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
            'success_count' => 1,
            'failed_count' => 1,
        ]);

        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Satu',
        ]);

        $this->assertDatabaseMissing('siswa', [
            'nisn' => '0012345679',
        ]);
    }

    public function test_import_with_various_edge_cases()
    {
        // Skenario edge cases:
        // 1. Format tanggal lahir tidak valid ("31-02-2026", "teks acak").
        // 2. Nama kelas mengandung spasi di depan/belakang (" X-A " -> trim check).
        // 3. File Excel/CSV mengandung baris kosong di tengah (should be skipped by SkipsEmptyRows).
        // 4. Format tahun ajaran salah ("2025/2026" / "2025-2026 Aneh") -> should fallback or log warning.

        $csvContent = "nis,nisn,nama_lengkap,jenis_kelamin,tempat_lahir,tanggal_lahir,alamat,no_hp,no_hp_ortu,kelas,tahun_ajaran,status\n"
            // Baris 1: Valid
            . "\"12345\",\"0012345678\",\"Siswa Valid\",\"L\",\"Jakarta\",\"01/01/2010\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n"
            // Baris 2: Tanggal lahir tidak valid ("31-02-2026")
            . "\"12346\",\"0012345679\",\"Siswa Tanggal Salah 1\",\"L\",\"Jakarta\",\"31-02-2026\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n"
            // Baris 3: Tanggal lahir teks acak ("tanggal_lahir_acak")
            . "\"12347\",\"0012345680\",\"Siswa Tanggal Salah 2\",\"L\",\"Jakarta\",\"tanggal_lahir_acak\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Genap\",\"aktif\"\n"
            // Baris 4: Nama kelas mengandung spasi (" X-A ")
            . "\"12348\",\"0012345681\",\"Siswa Kelas Spasi\",\"L\",\"Jakarta\",\"01/01/2010\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\" X-A \",\"2025-2026 Genap\",\"aktif\"\n"
            // Baris 5: Baris kosong di tengah
            . ",,,,,,,,,,,\n"
            // Baris 6: Format tahun ajaran salah/tidak lengkap (misal "2025-2026 Aneh" -> fallback ke kelas/default TA)
            . "\"12349\",\"0012345682\",\"Siswa TA Salah\",\"L\",\"Jakarta\",\"01/01/2010\",\"Jl. Merdeka No. 1\",\"08123456789\",\"08123456780\",\"X-A\",\"2025-2026 Aneh\",\"aktif\"\n";

        $uploadedFile = UploadedFile::fake()->create('siswa_edge_cases.csv', 10, 'text/csv');
        file_put_contents($uploadedFile->getRealPath(), $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.siswa.import.store'), [
                'import_file' => $uploadedFile,
            ]);

        $response->assertStatus(200);
        $json = $response->json();

        // 3 baris harusnya sukses: Siswa Valid (Baris 1), Siswa Kelas Spasi (Baris 4), Siswa TA Salah (Baris 6)
        // 2 baris gagal: Siswa Tanggal Salah 1 (Baris 2), Siswa Tanggal Salah 2 (Baris 3)
        // Baris kosong diabaikan sepenuhnya oleh SkipsEmptyRows
        $this->assertEquals(3, $json['success_count']);
        $this->assertEquals(2, $json['failed_count']);

        // Verifikasi Siswa Valid disimpan
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345678',
            'nama_lengkap' => 'Siswa Valid',
            'tanggal_lahir' => '2010-01-01 00:00:00',
        ]);

        // Verifikasi Siswa Kelas Spasi disimpan (trimming nama kelas X-A berhasil)
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345681',
            'nama_lengkap' => 'Siswa Kelas Spasi',
            'kelas_id' => $this->kelas->id,
        ]);

        // Verifikasi Siswa TA Salah disimpan (fallback ke tahun akademik kelas/aktif berhasil)
        $this->assertDatabaseHas('siswa', [
            'nisn' => '0012345682',
            'nama_lengkap' => 'Siswa TA Salah',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);

        // Verifikasi baris error tertangkap dengan detail yang benar
        $errors = $json['errors'];
        $this->assertCount(2, $errors);
        
        $this->assertEquals('0012345679', $errors[0]['nisn']);
        $this->assertStringContainsString('Format tanggal lahir', $errors[0]['error']);

        $this->assertEquals('0012345680', $errors[1]['nisn']);
        $this->assertStringContainsString('Format tanggal lahir', $errors[1]['error']);
    }
}
