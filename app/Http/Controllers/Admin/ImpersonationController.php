<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\ImpersonationLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a student.
     */
    public function start(Request $request, Siswa $siswa)
    {
        $admin = Auth::user();

        // Security check: Only super_admin or admin_sekolah (or those who have can_impersonate_student) can impersonate.
        // The PRD mentions permission "can_impersonate_student".
        // Let's allow super_admin and admin_sekolah to run this.
        if (!$admin || !$admin->hasAnyRole(['super_admin', 'admin_sekolah'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk masuk sebagai siswa (Impersonate).');
        }

        // Must have user account associated
        if (!$siswa->user_id) {
            return back()->with('error', 'Siswa yang ditargetkan tidak memiliki akun user terkait.');
        }

        $targetUser = $siswa->user;
        if (!$targetUser) {
            return back()->with('error', 'Akun user siswa tidak ditemukan.');
        }

        // Cannot impersonate yourself (just in case)
        if ($targetUser->id === $admin->id) {
            return back()->with('error', 'Anda tidak bisa login sebagai diri sendiri.');
        }

        // Cannot impersonate if already impersonating someone
        if (Session::has('impersonated_by')) {
            return back()->with('error', 'Harap kembali ke akun admin Anda sebelum impersonate user lain.');
        }

        $adminId = $admin->id;
        $adminName = $admin->name;

        // 1. Catat Log Audit ke Database impersonation_logs
        ImpersonationLog::create([
            'admin_id'   => $adminId,
            'siswa_id'   => $siswa->id,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?: 'Unknown',
            'status'     => 'started',
        ]);

        // Catat ke ActivityLog umum
        ActivityLog::record(
            'impersonate_siswa_start',
            'siswa',
            "Admin [{$adminName}] mulai impersonate Siswa: {$siswa->nama_lengkap} (User ID: {$targetUser->id})",
            null,
            ['target_siswa_id' => $siswa->id, 'target_user_id' => $targetUser->id, 'siswa_name' => $siswa->nama_lengkap]
        );

        // 2. Login sebagai siswa di guard 'web'
        Auth::guard('web')->login($targetUser);

        // Update the user resolver for current request
        request()->setUserResolver(fn() => $targetUser);

        // 3. Simpan data session admin asli (ID Admin, Role, Nama) ke session
        $session = request()->session();
        $session->put([
            'impersonated_by' => $adminId,
            'impersonator_name' => $adminName,
            'impersonate_original_role' => 'admin',
            // CRITICAL: Synchronize password hashes for AuthenticateSession middleware
            'password_hash_web' => $targetUser->getAuthPassword(),
            'password_hash_sanctum' => $targetUser->getAuthPassword(),
            'active_role' => User::ROLE_SISWA, // Force active role to siswa
        ]);

        // Manually save session to ensure it persists before redirection
        $session->save();

        return redirect()->route('siswa.dashboard')
            ->with('success', "Anda sekarang masuk sebagai {$siswa->nama_lengkap}");
    }

    /**
     * Revert back to the original admin account.
     */
    public function leave(Request $request)
    {
        if (!Session::has('impersonated_by')) {
            return redirect('/dashboard')->with('error', 'Tidak ada sesi impersonation yang aktif.');
        }

        $adminId = Session::get('impersonated_by');
        $admin = User::find($adminId);

        if (!$admin) {
            Session::forget(['impersonated_by', 'impersonator_name', 'impersonate_original_role', 'active_role']);
            Auth::logout();
            return redirect()->route('login')->with('error', 'Sesi admin tidak ditemukan. Silakan login ulang.');
        }

        $siswaUser = Auth::guard('web')->user();
        $siswa = $siswaUser ? $siswaUser->siswa : null;
        $siswaName = $siswa ? $siswa->nama_lengkap : ($siswaUser ? $siswaUser->name : 'Siswa');
        $siswaId = $siswa ? $siswa->id : null;

        // 1. Logout dari user siswa
        Auth::guard('web')->logout();

        // 2. Login kembali ke akun admin asli
        Auth::guard('web')->login($admin);

        // Update user resolver
        request()->setUserResolver(fn() => $admin);

        // 3. Update Log Audit
        if ($siswaId) {
            ImpersonationLog::where('admin_id', $adminId)
                ->where('siswa_id', $siswaId)
                ->where('status', 'started')
                ->update([
                    'ended_at' => now(),
                    'status'   => 'ended'
                ]);
        }

        // Catat ke ActivityLog umum
        ActivityLog::record(
            'impersonate_siswa_end',
            'siswa',
            "Admin [{$admin->name}] selesai impersonate Siswa: {$siswaName}",
            ['siswa_name' => $siswaName],
            ['admin_name' => $admin->name]
        );

        // 4. Bersihkan session impersonasi & set kembali active_role ke role admin asli
        $session = request()->session();
        $session->forget(['impersonated_by', 'impersonator_name', 'impersonate_original_role']);

        $session->put([
            'password_hash_web' => $admin->getAuthPassword(),
            'password_hash_sanctum' => $admin->getAuthPassword(),
            'active_role' => $admin->role, // Kembalikan ke role asli
        ]);

        $session->save();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Sesi impersonasi selesai. Anda kembali sebagai Admin.');
    }
}
