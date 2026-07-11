<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\UploadBatch;
use App\Models\UploadBatchItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProcessZipImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 300;

    /**
     * The ID of the UploadBatch.
     *
     * @var int
     */
    protected int $batchId;

    /**
     * Create a new job instance.
     *
     * @param int $batchId
     */
    public function __construct(int $batchId)
    {
        $this->batchId = $batchId;
        $this->queue = 'uploads';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $batch = UploadBatch::find($this->batchId);

        if (!$batch) {
            return;
        }

        $zipPath = Storage::disk('local')->path($batch->file_zip);

        // Cek file ZIP di batch->file_zip (path di local storage)
        if (!file_exists($zipPath)) {
            $batch->update([
                'status' => 'failed',
                'metadata' => ['error' => 'File ZIP tidak ditemukan di server'],
            ]);
            $batch->updateProgress();
            return;
        }

        // Buat folder temporary extraction: storage/app/private/temp/uploads/{batchId}/
        $tempPath = Storage::disk('local')->path('temp/uploads/' . $this->batchId);
        File::ensureDirectoryExists($tempPath);

        // Gunakan PHP ZipArchive untuk mengekstrak file ZIP ke folder temp tersebut
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($tempPath);
            $zip->close();
        } else {
            // Jika ekstrak gagal → tandai batch as failed, status = 'failed', metadata = ['error' => 'Gagal mengekstrak ZIP']
            $batch->update([
                'status' => 'failed',
                'metadata' => ['error' => 'Gagal mengekstrak ZIP'],
            ]);
            $batch->updateProgress();

            // Hapus folder temp jika terlanjur terbuat
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            return;
        }

        // Scan folder temp untuk mencari file gambar saja (ekstensi: jpg, jpeg, png, webp)
        $files = File::allFiles($tempPath);
        $imageFiles = [];

        foreach ($files as $file) {
            // Abaikan file metadata macos (__MACOSX)
            if (str_contains($file->getRelativePathname(), '__MACOSX')) {
                continue;
            }

            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $imageFiles[] = $file;
            }
        }

        // Jika 0 file gambar ditemukan → tandai batch as failed, status = 'failed', metadata = ['error' => 'Tidak ada file gambar valid ditemukan di dalam ZIP']
        if (count($imageFiles) === 0) {
            $batch->update([
                'status' => 'failed',
                'metadata' => ['error' => 'Tidak ada file gambar valid ditemukan di dalam ZIP'],
            ]);
            $batch->updateProgress();

            // Hapus folder temp ZIP beserta isinya
            if (File::exists($tempPath)) {
                File::deleteDirectory($tempPath);
            }
            return;
        }

        // Batasi maksimal 500 file gambar. Jika lebih dari 500, sisanya di-skip dan catat warning di metadata batch.
        $totalFound = count($imageFiles);
        $metadata = $batch->metadata ?? [];
        if ($totalFound > 500) {
            $imageFiles = array_slice($imageFiles, 0, 500);
            $metadata['warning'] = "Menemukan {$totalFound} gambar, namun dibatasi maksimal 500 gambar. Sisanya (" . ($totalFound - 500) . ") di-skip.";
        }

        // Simpan file individual dan buat record items
        foreach ($imageFiles as $file) {
            $originalFilename = $file->getFilename();
            $targetRelativePath = 'uploads/batch/' . $this->batchId . '/' . $originalFilename;
            $targetFullPath = Storage::disk('local')->path($targetRelativePath);

            // Pastikan direktori tujuan ada
            File::ensureDirectoryExists(dirname($targetFullPath));

            // Copy/move file dari folder temp extraction ke path baru tersebut di storage local
            File::move($file->getRealPath(), $targetFullPath);

            // Jalankan regex pencarian NISN (10 digit angka) dari nama file asli
            $siswaId = null;
            $oldFileId = null;

            if (preg_match('/\b(\d{10})\d*/', $originalFilename, $matches)) {
                $nisn = $matches[1];
                $siswa = Siswa::where('nisn', $nisn)->first();
                if ($siswa) {
                    $siswaId = $siswa->id;
                    // Ambil old_file_id jika siswa sudah memiliki foto existing di Google Drive yang panjangnya > 30
                    if ($siswa->foto && strlen($siswa->foto) > 30) {
                        $oldFileId = $siswa->foto;
                    }
                }
            }

            // Buat record UploadBatchItem baru dengan status 'pending'
            $item = UploadBatchItem::create([
                'upload_batch_id' => $this->batchId,
                'siswa_id' => $siswaId,
                'original_filename' => $originalFilename,
                'stored_path' => $targetRelativePath,
                'old_file_id' => $oldFileId,
                'status' => 'pending',
                'file_size' => File::size($targetFullPath),
                'mime_type' => File::mimeType($targetFullPath) ?: 'image/jpeg',
            ]);

            // Dispatch job UploadPhotoToGoogleDrive untuk item baru tersebut
            UploadPhotoToGoogleDrive::dispatch($item->id);
        }

        // Update UploadBatch dengan total_items = jumlah file yang diproses, status = 'processing'
        $batch->update([
            'total_items' => count($imageFiles),
            'status' => 'processing',
            'metadata' => $metadata,
        ]);

        // Hapus folder temp ZIP beserta seluruh isinya yang sudah tidak dipakai
        if (File::exists($tempPath)) {
            File::deleteDirectory($tempPath);
        }

        // Panggil $batch->updateProgress()
        $batch->updateProgress();
    }
}
