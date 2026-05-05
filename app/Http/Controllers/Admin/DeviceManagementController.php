<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedDevice;
use Illuminate\Http\Request;

class DeviceManagementController extends Controller
{
    public function index()
    {
        $devices = AuthorizedDevice::orderByDesc('created_at')->get();
        return view('admin.devices.index', compact('devices'));
    }

    public function authorizeDevice(Request $request, AuthorizedDevice $device)
    {
        $device->update([
            'is_authorized' => true,
            'device_name' => $request->input('device_name') ?: $device->device_name,
        ]);

        return back()->with('success', 'Perangkat berhasil diizinkan.');
    }

    public function destroy(AuthorizedDevice $device)
    {
        $device->delete();
        return back()->with('success', 'Perangkat berhasil dihapus.');
    }
}
