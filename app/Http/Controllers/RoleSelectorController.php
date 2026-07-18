<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleSelectorController extends Controller
{
    /**
     * Tampilkan halaman pemilih role.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $availableRoles = array_unique(array_filter(array_merge([$user->role], $user->roles ?? [])));

        if (empty($availableRoles) || count($availableRoles) <= 1) {
            return redirect()->route('dashboard');
        }

        return view('content.authentications.auth-select-role', compact('availableRoles'));
    }

    /**
     * Pilih role aktif.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function select(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $availableRoles = array_unique(array_filter(array_merge([$user->role], $user->roles ?? [])));

        $request->validate([
            'role' => 'required|string|in:' . implode(',', $availableRoles),
        ]);

        $role = $request->input('role');
        session(['active_role' => $role]);

        return redirect()->route('dashboard');
    }

    /**
     * Switch role aktif ke yang lain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $availableRoles = array_unique(array_filter(array_merge([$user->role], $user->roles ?? [])));

        $request->validate([
            'role' => 'required|string|in:' . implode(',', $availableRoles),
        ]);

        $role = $request->input('role');
        session(['active_role' => $role]);

        // Cek URL sebelumnya. Jika mengandung route/path role lama, redirect ke dashboard
        // Untuk sederhananya, redirect ke dashboard atau redirect back.
        // Agar aman, jika sebelumnya mengakses dashboard role lama, redirect ke /dashboard.
        // Kita bisa langsung redirect ke /dashboard demi konsistensi dan menghindari 403 jika redirect back ke route yang dilindungi role lama.
        $previousUrl = url()->previous();
        if (str_contains($previousUrl, '/siswa/') || 
            str_contains($previousUrl, '/guru/') || 
            str_contains($previousUrl, '/wali-kelas/') || 
            str_contains($previousUrl, '/ortu/') || 
            str_contains($previousUrl, '/piket/') || 
            str_contains($previousUrl, '/admin/')) {
            return redirect()->route('dashboard');
        }

        return redirect()->back();
    }
}
