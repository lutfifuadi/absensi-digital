<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use App\Http\Requests\Admin\StoreOrangTuaRequest;
use App\Http\Requests\Admin\UpdateOrangTuaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrangTuaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        $orangTua = User::withRole(User::ROLE_ORANG_TUA)
            ->with('children')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.orang-tua.table', compact('orangTua'))->render();
        }

        return view('admin.orang-tua.index', compact('orangTua'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        return view('admin.orang-tua.create', compact('siswaOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrangTuaRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $validated['password'] = Hash::make($validated['password']);
            $validated['role'] = User::ROLE_ORANG_TUA;
            $validated['roles'] = [User::ROLE_ORANG_TUA];

            $user = User::create($validated);

            if (!empty($validated['siswa_ids'])) {
                $user->children()->sync($validated['siswa_ids']);
                
                // Sinkronisasi ortu_user_id di tabel siswa (untuk backwards compatibility jika diperlukan)
                Siswa::whereIn('id', $validated['siswa_ids'])->update(['ortu_user_id' => $user->id]);
            }

            DB::commit();
            return redirect()->route('admin.orang-tua.index')
                ->with('success', 'Data Orang Tua berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan data Orang Tua. ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orangTua = User::withRole(User::ROLE_ORANG_TUA)->with('children.kelas')->findOrFail($id);
        return view('admin.orang-tua.show', compact('orangTua'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $orangTua = User::withRole(User::ROLE_ORANG_TUA)->with('children')->findOrFail($id);
        $siswaOptions = Siswa::orderBy('nama_lengkap')->get();
        $selectedSiswaIds = $orangTua->children->pluck('id')->toArray();

        return view('admin.orang-tua.edit', compact('orangTua', 'siswaOptions', 'selectedSiswaIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOrangTuaRequest $request, string $id)
    {
        $user = User::withRole(User::ROLE_ORANG_TUA)->findOrFail($id);

        DB::beginTransaction();
        try {
            $validated = $request->validated();
            
            if (!empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Ambil siswa yang saat ini dihubungkan
            $currentSiswaIds = $user->children->pluck('id')->toArray();
            
            // Sync pivot table
            $siswaIds = $validated['siswa_ids'] ?? [];
            $user->children()->sync($siswaIds);

            // Update ortu_user_id di table siswa untuk compatibility
            // 1. Reset ortu_user_id pada siswa yang di-disconnect
            $removedSiswaIds = array_diff($currentSiswaIds, $siswaIds);
            if (!empty($removedSiswaIds)) {
                Siswa::whereIn('id', $removedSiswaIds)->where('ortu_user_id', $user->id)->update(['ortu_user_id' => null]);
            }
            // 2. Set ortu_user_id pada siswa yang di-connect
            if (!empty($siswaIds)) {
                Siswa::whereIn('id', $siswaIds)->update(['ortu_user_id' => $user->id]);
            }

            DB::commit();
            return redirect()->route('admin.orang-tua.index')
                ->with('success', 'Data Orang Tua berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui data Orang Tua. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::withRole(User::ROLE_ORANG_TUA)->findOrFail($id);

        DB::beginTransaction();
        try {
            // Reset ortu_user_id di table siswa sebelum dihapus
            Siswa::where('ortu_user_id', $user->id)->update(['ortu_user_id' => null]);
            
            // Detach pivot
            $user->children()->detach();
            
            // Hapus user
            $user->delete();

            DB::commit();
            return redirect()->route('admin.orang-tua.index')
                ->with('success', 'Data Orang Tua berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data Orang Tua. ' . $e->getMessage());
        }
    }
}
