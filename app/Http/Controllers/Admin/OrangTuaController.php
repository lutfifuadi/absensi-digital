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
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $status = $request->query('status');

        $allowedSortColumns = ['name', 'email', 'username', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'name';
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'asc';

        $orangTua = User::withRole(User::ROLE_ORANG_TUA)
            ->with('children')
            ->when($status, function ($query, $status) {
                $query->where('users.status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%")
                      ->orWhereHas('children', function ($q) use ($search) {
                          $q->where('nama_lengkap', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.orang-tua.table', compact('orangTua', 'sortBy', 'sortDir'))->render();
        }

        return view('admin.orang-tua.index', compact('orangTua', 'sortBy', 'sortDir'));
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

    public function syncData(Request $request)
    {
        DB::beginTransaction();
        try {
            // Ambil domain dari pengaturan atau default
            $domain = \App\Models\Pengaturan::where('key', 'website_lembaga')->value('value');
            // Jika berisi URL lengkap, ambil host-nya
            if ($domain && filter_var($domain, FILTER_VALIDATE_URL)) {
                $domain = parse_url($domain, PHP_URL_HOST);
            }
            if (!$domain) {
                $domain = 'madrasah.sch.id';
            }

            $siswaTanpaOrtu = Siswa::whereNull('ortu_user_id')->get();
            $jumlahSiswaDiupdate = 0;
            $jumlahOrtuDibuat = 0;

            foreach ($siswaTanpaOrtu as $siswa) {
                $identifier = $siswa->nisn ?? $siswa->nis;
                if (!$identifier) {
                    continue; // Skip jika tidak ada nisn dan nis
                }

                $username = 'ortu.' . $identifier;
                $email = 'ortu.' . $identifier . '@' . $domain;
                $namaWali = 'Wali Murid ' . $siswa->nama_lengkap;
                $password = $siswa->nisn ? Hash::make($siswa->nisn) : Hash::make('password123');

                // Cek apakah user ortu dengan username ini sudah ada (mungkin beda siswa tapi identifier sama, jarang terjadi tapi mungkin)
                $ortu = User::where('username', $username)->first();

                if (!$ortu) {
                    $ortu = User::create([
                        'name' => $namaWali,
                        'email' => $email,
                        'username' => $username,
                        'password' => $password,
                        'is_active' => true,
                        'role' => User::ROLE_ORANG_TUA,
                        'roles' => [User::ROLE_ORANG_TUA],
                        'no_hp' => $siswa->no_hp_ortu,
                    ]);
                    $jumlahOrtuDibuat++;
                } else {
                    if (empty($ortu->no_hp) && !empty($siswa->no_hp_ortu)) {
                        $ortu->update([
                            'no_hp' => $siswa->no_hp_ortu
                        ]);
                    }
                }

                // Update siswa
                $siswa->ortu_user_id = $ortu->id;
                $siswa->save();
                
                // Sync pivot
                $siswa->ortu()->syncWithoutDetaching([$ortu->id]);
                
                $jumlahSiswaDiupdate++;
            }

            // Perbaikan relasi: siswa yang sudah punya ortu_user_id tapi belum ada di pivot
            $siswaDenganOrtu = Siswa::whereNotNull('ortu_user_id')->get();
            $jumlahRelasiDiperbaiki = 0;
            foreach ($siswaDenganOrtu as $siswa) {
                $pivotExists = DB::table('siswa_ortu')
                    ->where('siswa_id', $siswa->id)
                    ->where('ortu_user_id', $siswa->ortu_user_id)
                    ->exists();
                    
                if (!$pivotExists) {
                    $siswa->ortu()->syncWithoutDetaching([$siswa->ortu_user_id]);
                    $jumlahRelasiDiperbaiki++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil membuat $jumlahOrtuDibuat akun baru, mensinkronkan $jumlahSiswaDiupdate siswa, dan memperbaiki $jumlahRelasiDiperbaiki relasi."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyAll(Request $request)
    {
        DB::beginTransaction();
        try {
            // Ambil semua user dengan role orang_tua
            $ortuIds = User::withRole(User::ROLE_ORANG_TUA)->pluck('id')->toArray();

            if (empty($ortuIds)) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => 'Tidak ada data Orang Tua untuk dihapus.']);
                }
                return redirect()->route('admin.orang-tua.index')
                    ->with('info', 'Tidak ada data Orang Tua untuk dihapus.');
            }

            // Update siswa: set ortu_user_id null
            Siswa::whereIn('ortu_user_id', $ortuIds)->update(['ortu_user_id' => null]);

            // Hapus relasi di tabel pivot siswa_ortu
            DB::table('siswa_ortu')->whereIn('ortu_user_id', $ortuIds)->delete();

            // Hapus user ortu
            User::whereIn('id', $ortuIds)->delete();

            DB::commit();

            $message = 'Berhasil menghapus semua data Orang Tua dan mereset relasi pada Siswa.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return redirect()->route('admin.orang-tua.index')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMsg = 'Gagal menghapus semua data Orang Tua. ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 500);
            }
            return back()->with('error', $errorMsg);
        }
    }

    public function resetPassword(User $user)
    {
        try {
            $firstSiswa = $user->children()->first();
            $passwordRaw = 'password123';
            if ($firstSiswa) {
                $passwordRaw = $firstSiswa->nisn ?? $firstSiswa->nis ?? 'password123';
            }

            $user->update([
                'password' => Hash::make($passwordRaw)
            ]);

            return response()->json([
                'success' => true,
                'message' => "Password untuk orang tua {$user->name} berhasil di-reset menjadi: {$passwordRaw}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal me-reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetPasswordAll(Request $request)
    {
        DB::beginTransaction();
        try {
            $ortuUsers = User::withRole(User::ROLE_ORANG_TUA)->with('children')->get();
            $count = 0;

            foreach ($ortuUsers as $user) {
                $firstSiswa = $user->children->first();
                $passwordRaw = 'password123';
                if ($firstSiswa) {
                    $passwordRaw = $firstSiswa->nisn ?? $firstSiswa->nis ?? 'password123';
                }

                $user->update([
                    'password' => Hash::make($passwordRaw)
                ]);
                $count++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil me-reset password untuk {$count} akun Orang Tua ke password default masing-masing anak."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal me-reset password massal: ' . $e->getMessage()
            ], 500);
        }
    }
}
