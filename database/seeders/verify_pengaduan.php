<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Pengaduan;
use App\Models\LogPengaduan;

echo '=== TABEL PENGADUAN ===' . PHP_EOL;
echo 'Total: ' . Pengaduan::count() . PHP_EOL;
foreach (Pengaduan::all() as $p) {
    echo '  [' . $p->kode_unik . '] ' . $p->nama_lengkap . ' - ' . $p->status_label . ' (' . $p->status_color . ')' . PHP_EOL;
}

echo PHP_EOL . '=== TABEL LOG_PENGADUAN ===' . PHP_EOL;
echo 'Total: ' . LogPengaduan::count() . PHP_EOL;
foreach (LogPengaduan::all() as $l) {
    echo '  Log#' . $l->id . ': PGN#' . $l->pengaduan_id . ' ' . $l->status_dari . '->' . $l->status_ke . ' oleh ' . $l->diubah_oleh . PHP_EOL;
}
