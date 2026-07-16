<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$externalExcel = 'D:\\Project\\Website\\MAN 1 Kota Bandung\\Tahun Pelajaran 2026-2027\\KELAS_XII_TA_2026_2027.xlsx';
$spreadsheet = IOFactory::load($externalExcel);
$sheet1 = $spreadsheet->getSheetByName('Sheet1');
$rows = $sheet1->toArray();

echo "Checking ADLY AKBAR in Sheet1:\n";
foreach ($rows as $index => $row) {
    if ($index === 0) continue;
    $nama = trim((string)($row[3] ?? ''));
    if (stripos($nama, 'ADLY') !== false || stripos($nama, 'AKBAR') !== false) {
        // Limit output to matching rows
        if (stripos($nama, 'ADLY') !== false) {
            echo "Row: " . ($index + 1) . ", NIS: " . ($row[1] ?? '') . ", NISN: " . ($row[2] ?? '') . ", Name: $nama, Class: " . ($row[4] ?? '') . "\n";
        }
    }
}
