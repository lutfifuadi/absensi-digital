<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $role = $request->query('role');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $users = User::query()
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, function ($query, $role) {
                return $query->where(function($q) use ($role) {
                    $q->where('role', $role)
                      ->orWhereJsonContains('roles', $role);
                });
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.users.table', compact('users', 'sortBy', 'sortDir'))->render();
        }

        $roles = $this->roleOptions();

        return view('admin.users.index', compact('users', 'roles', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        $roles = $this->roleOptions();

        return view('admin.users.form', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role'     => ['nullable', Rule::in(array_keys($this->roleOptions()))],
            'roles'    => ['nullable', 'array', 'min:1'],
            'roles.*'  => ['required', Rule::in(array_keys($this->roleOptions()))],
        ]);

        $roles = $data['roles'] ?? [];
        if (empty($roles) && ! empty($data['role'])) {
            $roles = [$data['role']];
        }

        if (empty($roles)) {
            return back()->withInput()->withErrors(['roles' => 'Pilih setidaknya satu role.']);
        }

        $roles = array_values(array_unique($roles));
        $data['role'] = $roles[0];
        $data['roles'] = $roles;

        $plainPassword = $data['password'];
        $data['password'] = Hash::make($plainPassword);

        User::setPendingPlainPassword($plainPassword);
        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = $this->roleOptions();

        return view('admin.users.form', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|min:8|confirmed',
            'role'     => ['nullable', Rule::in(array_keys($this->roleOptions()))],
            'roles'    => ['nullable', 'array', 'min:1'],
            'roles.*'  => ['required', Rule::in(array_keys($this->roleOptions()))],
        ]);

        $roles = $data['roles'] ?? [];
        if (empty($roles) && ! empty($data['role'])) {
            $roles = [$data['role']];
        }

        if (empty($roles)) {
            return back()->withInput()->withErrors(['roles' => 'Pilih setidaknya satu role.']);
        }

        $roles = array_values(array_unique($roles));
        $data['role'] = $roles[0];
        $data['roles'] = $roles;

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            User::setPendingPlainPassword($plainPassword);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus akun yang sedang aktif.'], 400);
            }
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }

        $user->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    /**
     * Show the plain-text password of a user.
     * Only accessible by super_admin or admin_sekolah.
     */
    public function showPassword(Request $request, User $user)
    {
        // Double-check authorization (belt-and-suspenders with middleware)
        $admin = $request->user();
        if (! $admin->hasAnyRole([User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_SEKOLAH])) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melihat password.',
            ], 403);
        }

        // Log the activity
        ActivityLog::record(
            'VIEW_PASSWORD',
            'User',
            "Melihat password user: {$user->name} (ID: {$user->id})",
            null,
            ['viewed_user_id' => $user->id, 'viewed_user_name' => $user->name]
        );

        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'password' => $user->password_plain,
        ]);
    }

    private function roleOptions(): array
    {
        if (Schema::hasTable('roles')) {
            return Role::orderBy('name')->pluck('name', 'slug')->toArray();
        }

        return [
            'super_admin'   => 'Super Admin',
            'admin_sekolah' => 'Admin Sekolah',
            'operator'      => 'Operator',
            'guru'          => 'Guru',
            'wali_kelas'    => 'Wali Kelas',
            'staff_tu'      => 'Staff TU',
            'siswa'         => 'Siswa',
        ];
    }
}
