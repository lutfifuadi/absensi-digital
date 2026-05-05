<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')
            ->with('users')
            ->orderBy('name')
            ->get();

        return view('admin.role.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.role.form', [
            'role' => new Role(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $slug = Str::slug($data['name'], '_');
        $data['slug'] = $this->generateUniqueSlug($slug);

        Role::create($data);

        return redirect()->route('admin.role.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return view('admin.role.form', compact('role'));
    }

    public function show(Role $role)
    {
        return redirect()->route('admin.role.index');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string|max:1000',
        ]);

        $role->update($data);

        return redirect()->route('admin.role.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
        }

        $role->delete();

        return redirect()->route('admin.role.index')->with('success', 'Role berhasil dihapus.');
    }

    protected function generateUniqueSlug(string $slug): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while (Role::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}_{$counter}";
            $counter++;
        }

        return $slug;
    }
}
