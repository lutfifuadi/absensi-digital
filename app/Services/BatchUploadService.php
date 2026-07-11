<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\UploadBatch;
use App\Models\UploadBatchItem;
use App\Models\User;
use App\Jobs\UploadPhotoToGoogleDrive;
use App\Jobs\ProcessZipImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BatchUploadService
{
    /**
     * Create batch from multiple files.
     *
     * @param array $files
     * @param User $user
     * @param string|null $namaBatch
     * @return UploadBatch
     */
    public function createBatchFromFiles(array $files, User $user, ?string $namaBatch = null): UploadBatch
    {
        return DB::transaction(function () use ($files, $user, $namaBatch) {
            // Nama batch default: "Upload - " . now()->format('Y-m-d H:i:s')
            $nama = $namaBatch ?: "Upload - " . now()->format('Y-m-d H:i:s');

            // Buat record UploadBatch dengan status 'pending'
            $batch = UploadBatch::create([
                'user_id' => $user->id,
                'nama_batch' => $nama,
                'sumber' => 'web',
                'status' => 'pending',
                'total_items' => 0,
                'success_count' => 0,
                'failed_count' => 0,
            ]);

            $totalItems = 0;

            // Iterasi $files
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $originalFilename = $file->getClientOriginalName();
                    $timestamp = now()->timestamp;
                    $storedName = $timestamp . '_' . $originalFilename;

                    // Simpan file ke folder: uploads/batch/{batchId}/{timestamp}_{originalName} (disk local)
                    $storedPath = $file->storeAs("uploads/batch/{$batch->id}", $storedName, 'local');

                    // Deteksi siswa_id dan old_file_id
                    $detection = $this->detectStudentFromFilename($originalFilename);
                    $siswaId = $detection['siswa_id'] ?? null;
                    $oldFileId = $detection['old_file_id'] ?? null;

                    // Buat record UploadBatchItem dengan status 'pending'
                    UploadBatchItem::create([
                        'upload_batch_id' => $batch->id,
                        'siswa_id' => $siswaId,
                        'original_filename' => $originalFilename,
                        'stored_path' => $storedPath,
                        'old_file_id' => $oldFileId,
                        'status' => 'pending',
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType() ?: 'image/jpeg',
                    ]);

                    $totalItems++;
                }
            }

            // Jika total item > 0
            if ($totalItems > 0) {
                // Update total_items pada UploadBatch
                // Ubah status batch menjadi 'processing'
                $batch->update([
                    'total_items' => $totalItems,
                    'status' => 'processing',
                ]);

                // Dispatch job UploadPhotoToGoogleDrive untuk setiap item
                // Mengambil item-item yang baru dibuat
                $items = $batch->items;
                foreach ($items as $item) {
                    UploadPhotoToGoogleDrive::dispatch($item->id);
                }
            }

            // Panggil $batch->updateProgress()
            $batch->updateProgress();

            return $batch->fresh();
        });
    }

    /**
     * Create batch from ZIP file.
     *
     * @param UploadedFile $zipFile
     * @param User $user
     * @param string|null $namaBatch
     * @return UploadBatch
     */
    public function createBatchFromZip(UploadedFile $zipFile, User $user, ?string $namaBatch = null): UploadBatch
    {
        // Simpan file ZIP ke path temp: uploads/zip/{timestamp}_{filename}.zip
        $timestamp = now()->timestamp;
        $originalName = $zipFile->getClientOriginalName();
        $storedName = $timestamp . '_' . $originalName;
        $storedPath = $zipFile->storeAs('uploads/zip', $storedName, 'local');

        // Nama batch default: "Import ZIP - " . now()->format('Y-m-d H:i:s')
        $nama = $namaBatch ?: "Import ZIP - " . now()->format('Y-m-d H:i:s');

        // Buat record UploadBatch dengan status 'pending', sumber = 'zip', file_zip = path file ZIP
        $batch = UploadBatch::create([
            'user_id' => $user->id,
            'nama_batch' => $nama,
            'sumber' => 'zip',
            'file_zip' => $storedPath,
            'status' => 'pending',
            'total_items' => 0,
            'success_count' => 0,
            'failed_count' => 0,
        ]);

        // Dispatch job ProcessZipImport($batch->id)
        ProcessZipImport::dispatch($batch->id);

        return $batch;
    }

    /**
     * Retry failed items in a batch.
     *
     * @param UploadBatch $batch
     * @param array|null $itemIds
     * @return int
     */
    public function retryFailedItems(UploadBatch $batch, ?array $itemIds = null): int
    {
        return DB::transaction(function () use ($batch, $itemIds) {
            // Ambil item yang failed (status = 'failed')
            $query = $batch->items()->where('status', 'failed');

            // Jika $itemIds diberikan, filter hanya ID yang tercantum
            if ($itemIds !== null) {
                $query->whereIn('id', $itemIds);
            }

            $failedItems = $query->get();
            $retryCount = 0;

            foreach ($failedItems as $item) {
                // Ubah status menjadi 'pending', error_message = null, increment retry_count
                $item->update([
                    'status' => 'pending',
                    'error_message' => null,
                    'retry_count' => $item->retry_count + 1,
                ]);

                // Dispatch job UploadPhotoToGoogleDrive($item->id)
                UploadPhotoToGoogleDrive::dispatch($item->id);
                $retryCount++;
            }

            // Jika ada item yang di-retry
            if ($retryCount > 0) {
                // Update status batch menjadi 'processing'
                $batch->status = 'processing';
                $batch->save();
            }

            return $retryCount;
        });
    }

    /**
     * Cancel a batch in progress.
     *
     * @param UploadBatch $batch
     * @return void
     * @throws \Exception
     */
    public function cancelBatch(UploadBatch $batch): void
    {
        // Jika status batch bukan 'processing', throw exception
        if ($batch->status !== 'processing') {
            throw new \RuntimeException("Hanya batch yang sedang diproses yang dapat dibatalkan.");
        }

        DB::transaction(function () use ($batch) {
            // Ubah status batch menjadi 'cancelled'
            $batch->status = 'cancelled';
            $batch->save();

            // Ambil semua item batch yang statusnya 'pending'
            $pendingItems = $batch->items()->where('status', 'pending')->get();

            foreach ($pendingItems as $item) {
                // Hapus file temporary lokalnya jika ada
                if ($item->stored_path && Storage::disk('local')->exists($item->stored_path)) {
                    Storage::disk('local')->delete($item->stored_path);
                }

                // Update semua item batch yang statusnya 'pending' menjadi 'failed' dengan error_message = "Proses dibatalkan oleh pengguna"
                $item->update([
                    'status' => 'failed',
                    'error_message' => 'Proses dibatalkan oleh pengguna',
                ]);
            }

            // Panggil $batch->updateProgress()
            $batch->updateProgress();

            // Set status batch kembali ke 'cancelled' untuk override status dari updateProgress()
            $batch->status = 'cancelled';
            $batch->save();
        });
    }

    /**
     * Reset all batch logs — hapus batch records, items, dan file lokal.
     * Super Admin: hapus SEMUA batch. Admin Sekolah: hapus batch miliknya saja.
     * File Google Drive TIDAK dihapus.
     *
     * @param User $user
     * @return int Jumlah batch yang dihapus
     * @throws \Exception
     */
    public function resetAllBatches(User $user): int
    {
        return DB::transaction(function () use ($user) {
            // Tentukan query batch berdasarkan role
            $query = UploadBatch::query();

            if (!$user->isSuperAdmin()) {
                // Admin Sekolah: hanya batch miliknya
                $query->where('user_id', $user->id);
            }

            $batches = $query->get();
            $totalBatches = $batches->count();

            if ($totalBatches === 0) {
                return 0;
            }

            // Hapus file lokal setiap batch
            foreach ($batches as $batch) {
                // Hapus folder uploads/batch/{batchId} jika ada
                $batchPath = "uploads/batch/{$batch->id}";
                if (Storage::disk('local')->exists($batchPath)) {
                    Storage::disk('local')->deleteDirectory($batchPath);
                }

                // Hapus file ZIP jika ada (untuk batch sumber ZIP)
                if ($batch->file_zip && Storage::disk('local')->exists($batch->file_zip)) {
                    Storage::disk('local')->delete($batch->file_zip);
                }

                // Hapus file-file lokal di setiap item (redundan, tapi aman)
                foreach ($batch->items as $item) {
                    if ($item->stored_path && Storage::disk('local')->exists($item->stored_path)) {
                        Storage::disk('local')->delete($item->stored_path);
                    }
                }

                // Hapus items batch (cascade seharusnya, tapi kita explicit)
                $batch->items()->delete();
            }

            // Hapus semua batch records
            if (!$user->isSuperAdmin()) {
                UploadBatch::where('user_id', $user->id)->delete();
            } else {
                UploadBatch::query()->delete();
            }

            return $totalBatches;
        });
    }

    /**
     * Detect student information from filename.
     *
     * @param string $filename
     * @return array|null
     */
    public function detectStudentFromFilename(string $filename): ?array
    {
        // Ambil NISN dari nama file menggunakan regex: preg_match('/\b(\d{10})\b/', $filename, $matches)
        if (preg_match('/\b(\d{10})\b/', $filename, $matches)) {
            // Cari model Siswa berdasarkan nisn = $matches[1]
            $siswa = Siswa::where('nisn', $matches[1])->first();
            if ($siswa) {
                // Jika siswa ditemukan, return ['siswa_id' => $siswa->id, 'old_file_id' => (strlen($siswa->foto) > 30 ? $siswa->foto : null)]
                return [
                    'siswa_id' => $siswa->id,
                    'old_file_id' => ($siswa->foto && strlen($siswa->foto) > 30) ? $siswa->foto : null
                ];
            }
        }

        return null;
    }
}
