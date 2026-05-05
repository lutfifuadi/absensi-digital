<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $perPage = $request->query('per_page', 10);

        $users = User::when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.users.table', compact('users'))->render();
        }

        return view('admin.users.index', compact('users'));
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
        $data['password'] = Hash::make($data['password']);

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
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang aktif.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
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
