<?php

// To run this test: php tests/qa_pelepasan_test.php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Kegiatan;
use App\Models\AbsensiKegiatan;
use App\Models\TahunAkademik;
use App\Http\Controllers\Admin\PelepasanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

function runTest() {
    echo "=========================================\n";
    echo "RUNNING QA TESTING: ABSENSI PELEPASAN XII\n";
    echo "=========================================\n";

    // 1. Check if Grade XII classes and imported students exist
    $kelasCount = Kelas::where('tingkat', 'XII')->count();
    $siswaCount = Siswa::whereHas('kelas', function($q) {
        $q->where('tingkat', 'XII');
    })->count();

    echo "Kelas XII count in DB: $kelasCount (XII-F.1 to XII-F.12)\n";
    echo "Siswa XII count in DB: $siswaCount / 420\n";

    if ($siswaCount === 0) {
        echo "WARNING: Import command is still running or hasn't imported any students yet. Let's wait or look up one student.\n";
    }

    // Find a student to test scan
    $testStudent = Siswa::with('kelas')
        ->whereHas('kelas', function($q) {
            $q->where('tingkat', 'XII');
        })->first();

    if (!$testStudent) {
        echo "FAIL: No student in Class XII found in DB. Test aborted.\n";
        return;
    }

    echo "Testing using student: {$testStudent->nama_lengkap} (NISN: {$testStudent->nisn}, Kelas: {$testStudent->kelas->nama})\n";

    // Get Active Tahun Akademik
    $taId = session('tahun_akademik_id') ?? TahunAkademik::where('is_aktif', true)->value('id');
    
    // Clear any existing attendance for this student in Pelepasan just in case
    $kegiatan = Kegiatan::where('nama_kegiatan', 'Pelepasan Kelas XII Angkatan 2026')
        ->where('tahun_akademik_id', $taId)
        ->first();
    
    if ($kegiatan) {
        AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $testStudent->id)
            ->delete();
    }

    // Instanciate Controller
    $controller = new PelepasanController();

    // 2. Test Scan Store (New Attendance)
    echo "\nTEST CASE 1: First scan check-in (should succeed)\n";
    $request = Request::create('/admin/pelepasan/scan', 'POST', [
        'qr_code' => $testStudent->nisn
    ]);

    try {
        $response = $controller->scanStore($request);
        $data = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === 200 && $data['success'] === true && $data['is_new'] === true) {
            echo "PASS: Scan store recorded successfully. Message: {$data['message']}\n";
            echo "Details: Nama: {$data['siswa_nama']}, Kelas: {$data['siswa_kelas']}, Waktu: {$data['waktu']}, WA: {$data['wa_status']}\n";
        } else {
            echo "FAIL: Expected success, got status code " . $response->getStatusCode() . " and body: " . $response->getContent() . "\n";
        }
    } catch (\Exception $e) {
        echo "FAIL: Exception occurred: " . $e->getMessage() . "\n";
    }

    // 3. Test Scan Store (Duplicate Scan)
    echo "\nTEST CASE 2: Duplicate scan check-in (should return existing attendance with notification)\n";
    try {
        $response = $controller->scanStore($request);
        $data = json_decode($response->getContent(), true);

        if ($response->getStatusCode() === 200 && $data['success'] === true && $data['is_new'] === false) {
            echo "PASS: Duplicate scan handled properly. Message: {$data['message']}\n";
        } else {
            echo "FAIL: Expected duplicate handle, got status code " . $response->getStatusCode() . " and body: " . $response->getContent() . "\n";
        }
    } catch (\Exception $e) {
        echo "FAIL: Exception occurred: " . $e->getMessage() . "\n";
    }

    // 5. Test Scan Store (Invalid Card/NISN)
    echo "\nTEST CASE 3: Scan invalid card (should return 404)\n";
    $invalidRequest = Request::create('/admin/pelepasan/scan', 'POST', [
        'qr_code' => '9999999999'
    ]);
    try {
        $response = $controller->scanStore($invalidRequest);
        if ($response->getStatusCode() === 404) {
            echo "PASS: Invalid card returned 404 as expected.\n";
        } else {
            echo "FAIL: Expected 404 error, got " . $response->getStatusCode() . "\n";
        }
    } catch (\Exception $e) {
        echo "FAIL: Exception: " . $e->getMessage() . "\n";
    }

    // 5. Test Live Board Route
    echo "\nTEST CASE 4: Retrieve index dashboard data (should load without error)\n";
    try {
        $indexRequest = Request::create('/admin/pelepasan', 'GET');
        $indexResponse = $controller->index($indexRequest);
        echo "PASS: Index page loaded successfully.\n";
    } catch (\Exception $e) {
        echo "FAIL: Load index page failed: " . $e->getMessage() . "\n";
    }

    // Clean up test data
    if ($kegiatan) {
        AbsensiKegiatan::where('kegiatan_id', $kegiatan->id)
            ->where('siswa_id', $testStudent->id)
            ->delete();
        echo "\nCleaned up test attendance records.\n";
    }

    echo "=========================================\n";
    echo "QA TESTING COMPLETED\n";
    echo "=========================================\n";
}

runTest();
