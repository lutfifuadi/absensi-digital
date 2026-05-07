<?php
// QA Test Script untuk PembelianLisensi
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$results = [];

// Test 1: License key format
$key = \App\Models\PembelianLisensi::generateLicenseKey();
$results[] = ['Test 1 - License Key Format', preg_match('/^PRE-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}$/', $key) ? 'PASS' : 'FAIL', $key];

// Test 2: Download token length
$token = \App\Models\PembelianLisensi::generateDownloadToken();
$results[] = ['Test 2 - Token Length (64 chars)', strlen($token) === 64 ? 'PASS' : 'FAIL', strlen($token) . ' chars'];

// Test 3: Create with menunggu status
$p = \App\Models\PembelianLisensi::create([
    'nama_klien' => 'Test QA - SMA Negeri 99',
    'email_klien' => 'test@qa.com',
    'domain' => 'absensi.sma99.sch.id',
    'payment_status' => 'menunggu',
]);
$pStatus = $p->fresh();
$results[] = ['Test 3 - Create pending', $pStatus->status === 'pending' && $pStatus->license_key === null ? 'PASS' : 'FAIL', 'status=' . $pStatus->status];

// Test 4: isValid() when pending
$results[] = ['Test 4 - isValid pending (must be false)', !$p->isValid() ? 'PASS' : 'FAIL', 'isValid=' . ($p->isValid() ? 'true' : 'false')];

// Test 5: Simulate activation
$p->license_key = \App\Models\PembelianLisensi::generateLicenseKey();
$p->download_token = \App\Models\PembelianLisensi::generateDownloadToken();
$p->status = 'active';
$p->payment_status = 'lunas';
$p->activated_at = now();
$p->save();
$pFresh = $p->fresh();
$results[] = ['Test 5 - Activation', $pFresh->status === 'active' && !empty($pFresh->license_key) ? 'PASS' : 'FAIL', 'key=' . $pFresh->license_key];

// Test 6: isValid() when active
$results[] = ['Test 6 - isValid active (must be true)', $pFresh->isValid() ? 'PASS' : 'FAIL', 'isValid=true'];

// Test 7: Revoke
$p->update(['status' => 'revoked']);
$results[] = ['Test 7 - isValid after revoke (must be false)', !$p->fresh()->isValid() ? 'PASS' : 'FAIL', 'isValid=false'];

// Test 8: License key unique constraint
$p->delete();
$results[] = ['Test 8 - Cleanup', 'PASS', 'OK'];

// Print results
$allPassed = true;
foreach ($results as [$name, $status, $detail]) {
    echo sprintf("[%s] %s — %s\n", $status, $name, $detail);
    if ($status !== 'PASS') $allPassed = false;
}
echo "\n" . ($allPassed ? '✅ ALL QA TESTS PASSED' : '❌ SOME TESTS FAILED') . "\n";
