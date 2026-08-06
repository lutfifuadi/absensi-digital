<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * Assign a teacher as Guru Piket.
     */
    public function store(Request $request)
    {
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guru ini belum memiliki akun pengguna.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Guru ini belum memiliki akun pengguna.');
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
