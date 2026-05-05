<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$templates = App\Models\NotificationTemplate::all();
echo "=== REDAKSI NOTIFIKASI YANG TERSEDIA ===" . PHP_EOL . PHP_EOL;

foreach($templates as $no => $t) {
    $label = App\Models\NotificationTemplate::TYPES[$t->type] ?? $t->type;
    echo "[$no] " . $label . " (" . $t->type . ")" . PHP_EOL;
    echo str_repeat('-', 50) . PHP_EOL;
    echo $t->content . PHP_EOL;
    echo PHP_EOL . PHP_EOL;
}