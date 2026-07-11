<?php

namespace Tests\Feature\Admin;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAkademik;
use App\Models\UploadBatch;
use App\Models\UploadBatchItem;
use App\Models\User;
use App\Jobs\UploadPhotoToGoogleDrive;
use App\Jobs\ProcessZipImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadMassalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $adminSekolah;
    protected User $guru;
    protected TahunAkademik $tahunAkademik;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->adminSekolah = User::factory()->create([
            'role' => User::ROLE_ADMIN_SEKOLAH,
        ]);

        $this->guru = User::factory()->create([
            'role' => User::ROLE_GURU,
        ]);

        $this->tahunAkademik = TahunAkademik::create([
            'nama' => '2025/2026',
            'semester' => 'ganjil',
            'tanggal_mulai' => '2025-07-01',
            'tanggal_selesai' => '2025-12-31',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'nama' => 'X-A',
            'tingkat' => '10',
            'jurusan' => 'IPA',
            'tahun_akademik_id' => $this->tahunAkademik->id,
        ]);
    }

    public function test_index_page_is_accessible_by_admin()
    {
        $response = $this->actingAs($this->adminSekolah)->get(route('admin.upload-massal.index'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.upload-massal.index');
        $response->assertViewHas('driveConnected');
    }

    public function test_index_page_is_not_accessible_by_guru()
    {
        $response = $this->actingAs($this->guru)->get(route('admin.upload-massal.index'));
        $response->assertStatus(403);
    }

    public function test_upload_files_creates_batch_and_dispatches_jobs()
    {
        Queue::fake();
        Storage::fake('local');

        $file1 = UploadedFile::fake()->image('0012345678-photo1.jpg');
        $file2 = UploadedFile::fake()->image('0098765432-photo2.png');

        // Create mock student
        $siswa1 = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama_lengkap' => 'Ahmad Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminSekolah)->post(route('admin.upload-massal.upload'), [
            'files' => [$file1, $file2],
            'nama_batch' => 'Test Batch Upload',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'redirect_url']);

        $batch = UploadBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('Test Batch Upload', $batch->nama_batch);
        $this->assertEquals('processing', $batch->status);
        $this->assertEquals(2, $batch->total_items);

        $this->assertDatabaseHas('upload_batches', [
            'id' => $batch->id,
            'nama_batch' => 'Test Batch Upload',
        ]);

        $this->assertDatabaseHas('upload_batch_items', [
            'upload_batch_id' => $batch->id,
            'siswa_id' => $siswa1->id,
            'original_filename' => '0012345678-photo1.jpg',
        ]);

        $this->assertDatabaseHas('upload_batch_items', [
            'upload_batch_id' => $batch->id,
            'siswa_id' => null,
            'original_filename' => '0098765432-photo2.png',
        ]);

        Queue::assertPushed(UploadPhotoToGoogleDrive::class, 2);
    }

    public function test_import_zip_creates_batch_and_dispatches_process_zip_job()
    {
        Queue::fake();
        Storage::fake('local');

        $zipFile = UploadedFile::fake()->create('photos.zip', 100, 'application/zip');

        $response = $this->actingAs($this->adminSekolah)->post(route('admin.upload-massal.import-zip'), [
            'file_zip' => $zipFile,
            'nama_batch' => 'Test ZIP Batch',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'redirect_url']);

        $batch = UploadBatch::first();
        $this->assertNotNull($batch);
        $this->assertEquals('Test ZIP Batch', $batch->nama_batch);
        $this->assertEquals('pending', $batch->status);
        $this->assertEquals('zip', $batch->sumber);
        $this->assertNotNull($batch->file_zip);

        Queue::assertPushed(ProcessZipImport::class, function ($job) use ($batch) {
            $ref = new \ReflectionProperty(get_class($job), 'batchId');
            $ref->setAccessible(true);
            return $ref->getValue($job) === $batch->id;
        });
    }

    public function test_batches_list_filtering()
    {
        // Batch owned by super admin
        UploadBatch::create([
            'user_id' => $this->superAdmin->id,
            'nama_batch' => 'Super Admin Batch',
            'status' => 'completed',
        ]);

        // Batch owned by admin sekolah
        UploadBatch::create([
            'user_id' => $this->adminSekolah->id,
            'nama_batch' => 'Admin Sekolah Batch',
            'status' => 'completed',
        ]);

        // Super admin can see both
        $response = $this->actingAs($this->superAdmin)->get(route('admin.upload-massal.batches'));
        $response->assertStatus(200);
        $response->assertViewHas('batches');
        $this->assertCount(2, $response->viewData('batches'));

        // Admin sekolah can only see theirs
        $response = $this->actingAs($this->adminSekolah)->get(route('admin.upload-massal.batches'));
        $response->assertStatus(200);
        $response->assertViewHas('batches');
        $this->assertCount(1, $response->viewData('batches'));
    }

    public function test_show_batch_authorization()
    {
        $batch = UploadBatch::create([
            'user_id' => $this->superAdmin->id,
            'nama_batch' => 'Super Admin Batch',
            'status' => 'completed',
        ]);

        // Super admin can view
        $response = $this->actingAs($this->superAdmin)->get(route('admin.upload-massal.batches.show', $batch->id));
        $response->assertStatus(200);

        // Admin sekolah cannot view super admin's batch
        $response = $this->actingAs($this->adminSekolah)->get(route('admin.upload-massal.batches.show', $batch->id));
        $response->assertStatus(403);
    }

    public function test_retry_failed_items()
    {
        Queue::fake();

        $batch = UploadBatch::create([
            'user_id' => $this->adminSekolah->id,
            'nama_batch' => 'Failed Batch',
            'status' => 'failed',
        ]);

        $item1 = UploadBatchItem::create([
            'upload_batch_id' => $batch->id,
            'original_filename' => 'photo1.jpg',
            'status' => 'failed',
            'error_message' => 'Network error',
            'retry_count' => 0,
        ]);

        $response = $this->actingAs($this->adminSekolah)->post(route('admin.upload-massal.batches.retry', $batch->id), [
            'item_ids' => [$item1->id],
        ]);

        $response->assertRedirect();
        
        $item1->refresh();
        $this->assertEquals('pending', $item1->status);
        $this->assertNull($item1->error_message);
        $this->assertEquals(1, $item1->retry_count);

        Queue::assertPushed(UploadPhotoToGoogleDrive::class, 1);
    }

    public function test_cancel_processing_batch()
    {
        Storage::fake('local');
        
        $batch = UploadBatch::create([
            'user_id' => $this->adminSekolah->id,
            'nama_batch' => 'Cancel Batch',
            'status' => 'processing',
        ]);

        $item1 = UploadBatchItem::create([
            'upload_batch_id' => $batch->id,
            'original_filename' => 'photo1.jpg',
            'stored_path' => 'uploads/batch/' . $batch->id . '/photo1.jpg',
            'status' => 'pending',
        ]);

        Storage::disk('local')->put($item1->stored_path, 'fake_image_content');

        $response = $this->actingAs($this->adminSekolah)->post(route('admin.upload-massal.batches.cancel', $batch->id));
        $response->assertRedirect();

        $batch->refresh();
        $this->assertEquals('cancelled', $batch->status);

        $item1->refresh();
        $this->assertEquals('failed', $item1->status);
        $this->assertEquals('Proses dibatalkan oleh pengguna', $item1->error_message);

        $this->assertFalse(Storage::disk('local')->exists($item1->stored_path));
    }

    public function test_check_student_endpoint()
    {
        Siswa::create([
            'nis' => '12345',
            'nisn' => '1234567890',
            'nama_lengkap' => 'Budi Utomo',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2010-01-01',
            'no_hp_ortu' => '08123456780',
            'kelas_id' => $this->kelas->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminSekolah)->get(route('admin.upload-massal.check-student', '1234567890'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'nama' => 'Budi Utomo',
            ],
        ]);

        $response404 = $this->actingAs($this->adminSekolah)->get(route('admin.upload-massal.check-student', '0000000000'));
        $response404->assertStatus(404);
    }
}
