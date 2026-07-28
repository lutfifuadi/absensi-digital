<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruBkController extends Controller
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
                $q->withRole(User::ROLE_GURU_BK)
                  ->orWhere('guru.is_guru_bk', true);
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

        $guruBkUsers = $query->paginate($perPage)->withQueryString();

        // Candidates for assignment as Guru BK
        $availableGurus = Guru::with('user')
            ->where('is_guru_bk', false)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        if ($request->ajax()) {
            return view('admin.guru-bk.table', compact('guruBkUsers', 'sortBy', 'sortDir'))->render();
        }

        return view('admin.guru-bk.index', compact('guruBkUsers', 'availableGurus', 'sortBy', 'sortDir'));
    }

    /**
     * Assign a teacher as Guru BK.
     */
    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'konseling_limit' => 'nullable|integer|min:1',
        ]);

        $guru = Guru::findOrFail($request->input('guru_id'));
        $guru->is_guru_bk = true;
        if ($request->filled('konseling_limit')) {
            $guru->konseling_limit = $request->input('konseling_limit');
        }
        $guru->save();

        if ($guru->user) {
            $user = $guru->user;
            $roles = $user->roles ?? [];
            if (!in_array(User::ROLE_GURU_BK, $roles)) {
                $roles[] = User::ROLE_GURU_BK;
                $user->roles = array_values(array_unique($roles));
                $user->save();
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Guru BK berhasil ditetapkan.']);
        }

        return redirect()->route('admin.guru-bk.index')->with('success', 'Guru BK berhasil ditetapkan.');
    }

    /**
     * Remove Guru BK status from teacher.
     */
    public function destroy(Guru $guru)
    {
        $guru->is_guru_bk = false;
        $guru->save();

        if ($guru->user) {
            $user = $guru->user;
            $roles = array_diff($user->roles ?? [], [User::ROLE_GURU_BK]);
            $user->roles = array_values($roles);
            if ($user->role === User::ROLE_GURU_BK) {
                $user->role = User::ROLE_GURU;
            }
            $user->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status Guru BK berhasil dihapus.']);
        }

        return redirect()->route('admin.guru-bk.index')->with('success', 'Status Guru BK berhasil dihapus.');
    }
}
