<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('username', 'admin_percobaan')->first();
if ($user) {
    $user->password = Hash::make('admin123');
    $user->save();
    echo "Password untuk user 'admin_percobaan' berhasil direset menjadi: admin123\n";
} else {
    echo "User 'admin_percobaan' tidak ditemukan.\n";
}
