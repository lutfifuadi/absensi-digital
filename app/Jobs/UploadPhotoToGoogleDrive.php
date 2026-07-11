<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Models\UploadBatchItem;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadPhotoToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [10, 30, 60];

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 120;

    /**
     * The ID of the UploadBatchItem.
     *
     * @var int
     */
    protected int $itemId;

    /**
     * Create a new job instance.
     *
     * @param int $itemId
     */
    public function __construct(int $itemId)
    {
        $this->itemId = $itemId;
        $this->queue = 'uploads';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $item = UploadBatchItem::find($this->itemId);

        if (!$item) {
            return;
        }

        // Jika item berstatus 'success', return (skip).
        if ($item->status === 'success') {
            return;
        }

        // Ubah status item menjadi 'processing'.
        $item->markAsProcessing();

        // Ambil path file fisik di stored_path
        $filePath = Storage::disk('local')->path($item->stored_path);

        // Cek apakah file fisik ada. Jika tidak ada -> mark as failed
        if (!file_exists($filePath)) {
            $item->markAsFailed("File temporary tidak ditemukan di server");
            $item->batch->updateProgress();
            return;
        }

        try {
            // Gunakan app(GoogleDriveService::class) untuk memanggil method upload.
            $gdriveService = app(GoogleDriveService::class);

            // Lakukan upload
            $fileId = $gdriveService->uploadPhoto($filePath, $item->old_file_id);

            // Jika upload mengembalikan ID yang valid (panjang string > 30)
            if ($fileId && strlen($fileId) > 30) {
                // Update item: google_drive_file_id = $fileId, status = 'success', processed_at = now()
                $item->update([
                    'google_drive_file_id' => $fileId,
                    'status' => 'success',
                    'processed_at' => now(),
                    'error_message' => null,
                ]);

                // Jika item memiliki siswa_id, ambil model Siswa dan update field foto dengan $fileId
                if ($item->siswa_id) {
                    $siswa = Siswa::find($item->siswa_id);
                    if ($siswa) {
                        $siswa->update([
                            'foto' => $fileId,
                        ]);
                    }
                }

                // Hapus file lokal di stored_path untuk menghemat disk space
                if (Storage::disk('local')->exists($item->stored_path)) {
                    Storage::disk('local')->delete($item->stored_path);
                }

                // Panggil updateProgress()
                $item->batch->updateProgress();
            } else {
                throw new \Exception("Gagal mengupload file ke Google Drive (ID tidak valid).");
            }
        } catch (Throwable $exception) {
            // Jika upload gagal / exception terjadi:
            // Mark as failed, simpan message exception.
            $item->markAsFailed($exception->getMessage());
            $item->batch->updateProgress();

            // Lemparkan exception kembali agar Laravel bisa retry job jika $tries belum habis.
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $item = UploadBatchItem::find($this->itemId);

        if ($item) {
            // Set status item menjadi 'failed'
            // Simpan $exception->getMessage() ke error_message
            $item->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'processed_at' => now(),
            ]);

            // Hapus file lokal di stored_path agar tidak tertinggal
            if (Storage::disk('local')->exists($item->stored_path)) {
                Storage::disk('local')->delete($item->stored_path);
            }

            // Panggil $item->batch->updateProgress()
            $item->batch->updateProgress();
        }
    }
}
