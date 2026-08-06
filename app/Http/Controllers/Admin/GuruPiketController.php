<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuruPiketController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'nama_lengkap');
        $sortDir = $request->query('sort_dir', 'asc');
        $perPage = (int) $request->query('per_page', 10);

        $allowedSorts = ['nama_lengkap', 'nip', 'status', 'email'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'nama_lengkap';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $query = User::query()
            ->select('users.*')
            ->leftJoin('guru', 'users.id', '=', 'guru.user_id')
            ->with('guru')
            ->where(function ($q) {
                $q->withRole(User::ROLE_PIKET);
            });

        // Filter search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('guru.nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('guru.nip', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($status) {
            if ($status === 'belum lengkap') {
                $query->whereNull('guru.id');
            } else {
                $query->where('guru.status', $status);
            }
        }

        // Sorting
        if ($sortBy === 'nama_lengkap') {
            $query->orderBy(DB::raw('COALESCE(guru.nama_lengkap, users.name)'), $sortDir);
        } elseif ($sortBy === 'nip') {
            $query->orderBy('guru.nip', $sortDir);
        } elseif ($sortBy === 'status') {
            $query->orderBy('guru.status', $sortDir);
        } elseif ($sortBy === 'email') {
            $query->orderBy('users.email', $sortDir);
        } else {
            $query->orderBy('users.name', $sortDir);
        }

        $guruPiketUsers = $query->paginate($perPage)->withQueryString();

        // Candidates for assignment as Guru Piket (Guru yang belum memiliki role piket)
        $piketUserIds = User::withRole(User::ROLE_PIKET)->pluck('id')->toArray();

        $availableGurus = Guru::with('user')
            ->where(function ($q) use ($piketUserIds) {
                $q->whereNull('user_id')
                  ->orWhereNotIn('user_id', $piketUserIds);
            })
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($request->ajax()) {
            return view('admin.guru-piket.table', compact('guruPiketUsers', 'sortBy', 'sortDir'))->render();
        }

        return view('admin.guru-piket.index', compact('guruPiketUsers', 'availableGurus', 'sortBy', 'sortDir'));
    }

    /**
     * Assign existing teacher OR Create new manual Guru Piket officer.
     */
    public function store(Request $request)
    {
        $mode = $request->input('mode', 'assign');

        if ($mode === 'create') {
            $validated = $request->validate([
                'nama'     => 'required|string|max:255',
                'nip'      => 'nullable|string|max:50|unique:guru,nip',
                'email'    => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6',
                'no_hp'    => 'nullable|string|max:20',
            ]);

            return DB::transaction(function () use ($validated, $request) {
                $username = !empty($validated['nip']) ? $validated['nip'] : Str::slug($validated['nama']) . rand(100, 999);

                $user = User::create([
                    'name'     => $validated['nama'],
                    'username' => $username,
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role'     => User::ROLE_PIKET,
                    'roles'    => [User::ROLE_PIKET, User::ROLE_GURU],
                    'status'   => 'aktif',
                ]);

                Guru::create([
                    'user_id'      => $user->id,
                    'nama_lengkap' => $validated['nama'],
                    'nip'          => $validated['nip'] ?? null,
                    'email'        => $validated['email'],
                    'no_hp'        => $validated['no_hp'] ?? null,
                    'status'       => 'aktif',
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => 'Petugas Piket baru berhasil dibuat & ditambahkan!']);
                }

                return redirect()->route('admin.guru-piket.index')->with('success', 'Petugas Piket baru berhasil dibuat & ditambahkan!');
            });
        }

        // Mode Assign (Guru Existing)
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
        ]);

        $guru = Guru::findOrFail($request->input('guru_id'));

        if ($guru->user) {
            $user = $guru->user;
            $roles = $user->roles ?? [];
            if (!in_array(User::ROLE_PIKET, $roles)) {
                $roles[] = User::ROLE_PIKET;
                $user->roles = array_values(array_unique($roles));
                $user->save();
            }
        } else {
            $username = !empty($guru->nip) ? $guru->nip : Str::slug($guru->nama_lengkap) . rand(100, 999);
            $user = User::create([
                'name'     => $guru->nama_lengkap,
                'username' => $username,
                'email'    => $guru->email ?: $username . '@sekolah.sch.id',
                'password' => Hash::make('password123'),
                'role'     => User::ROLE_PIKET,
                'roles'    => [User::ROLE_PIKET, User::ROLE_GURU],
                'status'   => 'aktif',
            ]);
            $guru->user_id = $user->id;
            $guru->save();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Guru Piket berhasil ditetapkan.']);
        }

        return redirect()->route('admin.guru-piket.index')->with('success', 'Guru Piket berhasil ditetapkan.');
    }

    /**
     * Remove Guru Piket status from teacher.
     */
    public function destroy(Guru $guru)
    {
        if ($guru->user) {
            $user = $guru->user;
            $roles = array_diff($user->roles ?? [], [User::ROLE_PIKET]);
            $user->roles = array_values($roles);
            if ($user->role === User::ROLE_PIKET) {
                $user->role = User::ROLE_GURU;
            }
            $user->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status Guru Piket berhasil dihapus.']);
        }

        return redirect()->route('admin.guru-piket.index')->with('success', 'Status Guru Piket berhasil dihapus.');
    }
}
