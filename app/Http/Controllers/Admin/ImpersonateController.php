<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonateController extends Controller
{
    /**
     * Login as another user (Impersonation).
     * Stores the original admin's ID in session for later restoration.
     */
    public function loginAs(User $user)
    {
        // Only super_admin can impersonate
        if (! auth()->user()->hasAnyRole(['super_admin'])) {
            abort(403, 'Hanya Super Admin yang dapat menggunakan fitur ini.');
        }

        // Cannot impersonate yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak bisa login sebagai diri sendiri.');
        }

        // Cannot impersonate another super_admin
        if ($user->role === 'super_admin') {
            return back()->with('error', 'Tidak bisa menggunakan fitur ini untuk sesama Super Admin.');
        }

        // Cannot impersonate if already impersonating someone
        if (Session::has('impersonator_id')) {
            return back()->with('error', 'Harap kembali ke akun admin Anda sebelum impersonate user lain.');
        }

        // Save target user ID and admin data
        $targetUserId = $user->id;
        $adminId = auth()->id();
        $adminName = auth()->user()->name;

        // Log BEFORE switch
        ActivityLog::record(
            'impersonate_start',
            'users',
            "Admin [{$adminName}] mulai impersonate: {$user->name} (Role: {$user->role})",
            null,
            ['target_user_id' => $user->id, 'target_user_name' => $user->name, 'target_role' => $user->role]
        );

        // Login target user on the web (session) guard
        Auth::guard('web')->login($user);

        // Update the user resolver for current request
        request()->setUserResolver(fn() => $user);

        // Save session data AFTER login (important because login() regenerates the session)
        $session = request()->session();
        $session->put([
            'impersonator_id' => $adminId,
            'impersonator_name' => $adminName,
            // CRITICAL: Synchronize password hashes for AuthenticateSession middleware
            'password_hash_web' => $user->getAuthPassword(),
            'password_hash_sanctum' => $user->getAuthPassword(),
        ]);

        // Manually save session to ensure it persists before redirection
        $session->save();

        return redirect('/dashboard');
    }

    /**
     * Revert back to the original admin account.
     */
    public function revert()
    {
        $impersonatorId = Session::get('impersonator_id');

        if (! $impersonatorId) {
            return redirect('/dashboard')->with('error', 'Tidak ada sesi impersonation yang aktif.');
        }

        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            Session::forget(['impersonator_id', 'impersonator_name']);
            Auth::guard('web')->logout();
            return redirect()->route('auth-login-basic')->with('error', 'Sesi admin tidak ditemukan. Silakan login ulang.');
        }

        $previousUser = auth()->user();
        $previousName = $previousUser->name;
        $previousRole = $previousUser->role;

        // Restore original admin session
        Auth::guard('web')->login($impersonator);

        // Update user resolver
        request()->setUserResolver(fn() => $impersonator);

        // Clear impersonation session data AFTER login
        $session = request()->session();
        $session->forget(['impersonator_id', 'impersonator_name']);

        // CRITICAL FIX: Update session password hash for the restored admin user.
        $session->put([
            'password_hash_web' => $impersonator->getAuthPassword(),
            'password_hash_sanctum' => $impersonator->getAuthPassword(),
        ]);

        // Save session manually
        $session->save();

        // Log AFTER restore so user_id = admin
        ActivityLog::record(
            'impersonate_end',
            'users',
            "Admin [{$impersonator->name}] selesai impersonate: {$previousName} (Role: {$previousRole})",
            ['impersonated_user' => $previousName, 'impersonated_role' => $previousRole],
            ['admin_name' => $impersonator->name]
        );

        return redirect()->route('admin.users.index')
            ->with('success', "Berhasil kembali ke akun admin. Sesi impersonation sebagai {$previousName} telah diakhiri.");
    }
}
