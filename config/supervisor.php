<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supervisor API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk koneksi ke Supervisor XML-RPC API.
    | Digunakan oleh SupervisorService untuk mengontrol queue worker.
    |
    */
    'host' => env('SUPERVISOR_HOST', '127.0.0.1'),
    'port' => env('SUPERVISOR_PORT', 9001),
    'username' => env('SUPERVISOR_USERNAME', 'supervisor_api'),
    'password' => env('SUPERVISOR_API_PASSWORD', ''),
    'program' => env('SUPERVISOR_PROGRAM', 'laravel-worker'),
];
