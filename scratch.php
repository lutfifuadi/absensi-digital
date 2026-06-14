<?php
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$user = App\Models\User::where('role', 'super_admin')->first();
auth()->login($user);
$request->setUserResolver(function () use ($user) { return $user; });
session(['tahun_akademik_id' => 3]);
$controller = app(App\Http\Controllers\DashboardController::class);
$response = $controller->index($request);
echo "Total Siswa: " . $response->getData()['totalSiswa'] . "\nTotal Kelas: " . $response->getData()['totalKelas'];
