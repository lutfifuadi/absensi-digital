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

// 2. Deteksi URL dan Set Produksi Otomatis
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 0) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentUrl = rtrim($protocol . $host, '/');
$isLocal = in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, '192.168.') === 0;

$envContent = file_get_contents($envPath);
$needsUpdate = false;

// Set APP_URL otomatis jika masih localhost atau kosong
if (preg_match('/^APP_URL=(http:\/\/localhost|https:\/\/localhost|)$/m', $envContent)) {
    $envContent = preg_replace('/^APP_URL=.*$/m', "APP_URL=$currentUrl", $envContent);
    $needsUpdate = true;
}

// Set ke production jika diakses dari domain asli
if (!$isLocal) {
    if (preg_match('/^APP_ENV=local$/m', $envContent)) {
        $envContent = preg_replace('/^APP_ENV=local$/m', "APP_ENV=production", $envContent);
        $needsUpdate = true;
    }
    if (preg_match('/^APP_DEBUG=true$/m', $envContent)) {
        $envContent = preg_replace('/^APP_DEBUG=true$/m', "APP_DEBUG=false", $envContent);
        $needsUpdate = true;
    }
}

// 3. Generate APP_KEY jika kosong
if (!preg_match('/^APP_KEY=base64:./m', $envContent)) {
    $newKey = 'base64:'.base64_encode(random_bytes(32));
    if (strpos($envContent, 'APP_KEY=') !== false) {
        $envContent = preg_replace('/^APP_KEY=.*$/m', "APP_KEY=$newKey", $envContent);
    } else {
        $envContent .= "\nAPP_KEY=$newKey";
    }
    $needsUpdate = true;
}

if ($needsUpdate) {
    file_put_contents($envPath, $envContent);
    
    // Reload env if key was just generated or updated
    if (preg_match('/^APP_KEY=(.+)$/m', $envContent, $matches)) {
        $_ENV['APP_KEY'] = trim($matches[1]);
        putenv("APP_KEY=" . trim($matches[1]));
    }
}

// 4. Cek apakah perlu redirect ke installer (misal jika database belum disetting)
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
