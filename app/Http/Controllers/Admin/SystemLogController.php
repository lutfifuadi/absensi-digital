<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SystemLogController extends Controller
{
    /**
     * Tampilan utama Log Viewer
     */
    public function index()
    {
        $logFiles = $this->getAvailableLogFiles();
        return view('admin.system-logs', compact('logFiles'));
    }

    /**
     * AJAX: Mengambil isi dari file log tertentu
     */
    public function getLogs(Request $request)
    {
        $request->validate([
            'file' => 'required|string'
        ]);

        $fileName = $request->input('file');
        $availableFiles = $this->getAvailableLogFiles();

        if (!in_array($fileName, $availableFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'File log tidak valid atau tidak diizinkan.'
            ], 403);
        }

        $logPath = storage_path('logs/' . $fileName);

        if (!File::exists($logPath)) {
            return response()->json([
                'success' => true,
                'content' => '[File log belum terbuat / masih kosong]'
            ]);
        }

        // Baca file log, batasi ukuran untuk performa jika terlalu besar
        $content = File::get($logPath);

        // Jika file log kosong
        if (trim($content) === '') {
            $content = '[File log kosong]';
        } else {
            // Batasi tampilan maksimal 1500 baris terakhir agar tidak crash/lambat
            $lines = explode("\n", $content);
            if (count($lines) > 1500) {
                $lines = array_slice($lines, -1500);
                $content = implode("\n", $lines);
            }
        }

        return response()->json([
            'success' => true,
            'content' => $content
        ]);
    }

    /**
     * AJAX POST: Mengosongkan file log secara fisik di server
     */
    public function clearLog(Request $request)
    {
        $request->validate([
            'file' => 'required|string'
        ]);

        $fileName = $request->input('file');
        $availableFiles = $this->getAvailableLogFiles();

        if (!in_array($fileName, $availableFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'File log tidak valid atau tidak diizinkan.'
            ], 403);
        }

        $logPath = storage_path('logs/' . $fileName);

        try {
            if (File::exists($logPath)) {
                // Tulis string kosong ke file log
                File::put($logPath, '');
            }

            return response()->json([
                'success' => true,
                'message' => "Isi file log '{$fileName}' berhasil dibersihkan."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan file log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan daftar file log yang diizinkan dibaca/dibersihkan
     */
    private function getAvailableLogFiles()
    {
        $logPath = storage_path('logs');
        if (!File::isDirectory($logPath)) {
            return [];
        }

        $files = File::files($logPath);
        $logFiles = [];

        foreach ($files as $file) {
            $fileName = $file->getFilename();
            // Hanya izinkan file berekstensi .log dan abaikan file tersembunyi
            if (str_ends_with($fileName, '.log') && !str_starts_with($fileName, '.')) {
                $logFiles[] = $fileName;
            }
        }

        // Urutkan agar qr-scan.log ada di paling atas karena paling sering diakses,
        // baru file laravel.log atau file log tanggalan
        usort($logFiles, function ($a, $b) {
            if ($a === 'qr-scan.log') return -1;
            if ($b === 'qr-scan.log') return 1;
            if ($a === 'laravel.log') return -1;
            if ($b === 'laravel.log') return 1;
            return strcmp($b, $a); // Urutkan tanggal log terbaru di atas
        });

        return $logFiles;
    }
}
