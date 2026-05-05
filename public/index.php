<?php

// ── WEB INSTALLER AUTO-PREPARE ──
$envPath = __DIR__.'/../.env';
$examplePath = __DIR__.'/../.env.example';

// 1. Buat .env jika tidak ada
if (!file_exists($envPath)) {
    if (file_exists($examplePath)) {
        copy($examplePath, $envPath);
    } else {
        file_put_contents($envPath, "APP_KEY=\nAPP_DEBUG=true\n");
    }
}

// 2. Generate APP_KEY jika kosong
$envContent = file_get_contents($envPath);
if (!preg_match('/^APP_KEY=base64:./m', $envContent)) {
    $newKey = 'base64:'.base64_encode(random_bytes(32));
    if (strpos($envContent, 'APP_KEY=') !== false) {
        $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY=$newKey", $envContent);
    } else {
        $envContent .= "\nAPP_KEY=$newKey";
    }
    file_put_contents($envPath, $envContent);
    
    // Pastikan key tersedia di environment saat ini agar Laravel bisa langsung boot
    $_ENV['APP_KEY'] = $newKey;
    putenv("APP_KEY=$newKey");
}

// 3. Cek apakah perlu redirect ke installer (misal jika database belum disetting)
$isInstalled = file_exists(__DIR__.'/../storage/installed');
if (!$isInstalled && strpos($_SERVER['REQUEST_URI'] ?? '', '/install') === false) {
    header('Location: /install');
    exit;
}
// ───────────────────────────

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
