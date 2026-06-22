<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Tampilkan daftar penugasan berdasarkan role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Assignment::with(['guru', 'kelas'])->orderByDesc('tanggal_tugas');

        if ($user->role === 'guru') {
            $guru = $user->guru;
            if (!$guru) {
                abort(404, 'Data guru tidak ditemukan.');
            }
            $query->where('guru_id', $guru->id);
        } elseif ($user->role === 'siswa') {
            $siswa = $user->siswa;
            if (!$siswa || !$siswa->kelas_id) {
                abort(404, 'Anda belum terdaftar di kelas manapun.');
            }
            $query->where('kelas_id', $siswa->kelas_id);
        } elseif ($user->role === 'wali_kelas') {
            $guru = $user->guru;
            $classIds = Kelas::where('wali_kelas_id', $guru?->id)->pluck('id');
            $query->whereIn('kelas_id', $classIds);
        }

        // Filter pencarian & kelas (opsional untuk guru/admin)
        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('judul', 'like', '%' . $request->search . '%')
                  ->orWhere('mata_pelajaran', 'like', '%' . $request->search . '%');
            });
        }

        $assignments = $query->paginate(20)->withQueryString();
        $kelasOptions = Kelas::orderBy('nama')->get();

        if ($user->role === 'guru') {
            return view('guru.assignments.index', compact('assignments', 'kelasOptions'));
        } elseif ($user->role === 'siswa') {
            return view('siswa.assignments.index', compact('assignments'));
        } else {
            // Admin, super admin, operator
            return view('admin.assignments.index', compact('assignments', 'kelasOptions'));
        }
    }

    /**
     * Form tambah penugasan (Hanya Guru).
     */
    public function create()
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403, 'Akses ditolak.');
        }

        $kelasOptions = Kelas::orderBy('nama')->get();
        return view('guru.assignments.form', compact('kelasOptions'));
    }

    /**
     * Simpan penugasan baru (Hanya Guru).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403, 'Akses ditolak.');
        }

        $guru = $user->guru;
        if (!$guru) {
            abort(404, 'Data guru tidak ditemukan.');
        }

        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'mata_pelajaran' => 'required|string|max:100',
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'required|string',
            'tanggal_tugas'  => 'required|date',
            'file_lampiran'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,rar|max:5120', // Max 5MB
        ]);

        $data['guru_id'] = $guru->id;

        if ($request->hasFile('file_lampiran')) {
            $data['file_lampiran'] = $request->file('file_lampiran')->store('assignments', 'public');
        }

        Assignment::create($data);

        return redirect()->route('assignments.index')
            ->with('success', 'Penugasan berhasil dibuat dan dipublikasikan.');
    }

    /**
     * Form edit penugasan (Hanya Guru yang memiliki tugas ini).
     */
    public function edit(Assignment $assignment)
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403, 'Akses ditolak.');
        }

        $guru = $user->guru;
        if (!$guru || $assignment->guru_id !== $guru->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penugasan ini.');
        }

        $kelasOptions = Kelas::orderBy('nama')->get();
        return view('guru.assignments.form', compact('assignment', 'kelasOptions'));
    }

    /**
     * Update penugasan (Hanya Guru yang memiliki tugas ini).
     */
    public function update(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        if ($user->role !== 'guru') {
            abort(403, 'Akses ditolak.');
        }

        $guru = $user->guru;
        if (!$guru || $assignment->guru_id !== $guru->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengupdate penugasan ini.');
        }

        $data = $request->validate([
            'kelas_id'       => 'required|exists:kelas,id',
            'mata_pelajaran' => 'required|string|max:100',
            'judul'          => 'required|string|max:255',
            'deskripsi'      => 'required|string',
            'tanggal_tugas'  => 'required|date',
            'file_lampiran'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,rar|max:5120',
        ]);

        if ($request->hasFile('file_lampiran')) {
            if ($assignment->file_lampiran) {
                Storage::disk('public')->delete($assignment->file_lampiran);
            }
            $data['file_lampiran'] = $request->file('file_lampiran')->store('assignments', 'public');
        }

        $assignment->update($data);

        return redirect()->route('assignments.index')
            ->with('success', 'Penugasan berhasil diperbarui.');
    }

    /**
     * Hapus penugasan (Hanya Guru pemilik atau Admin).
     */
    public function destroy(Assignment $assignment)
    {
        $user = Auth::user();
        $isGuruOwner = ($user->role === 'guru' && $user->guru && $assignment->guru_id === $user->guru->id);
        $isAdmin = in_array($user->role, ['super_admin', 'admin_sekolah', 'operator']);

        if (!$isGuruOwner && !$isAdmin) {
            abort(403, 'Akses ditolak.');
        }

        if ($assignment->file_lampiran) {
            Storage::disk('public')->delete($assignment->file_lampiran);
        }

        $assignment->delete();

        return redirect()->route('assignments.index')
            ->with('success', 'Penugasan berhasil dihapus.');
    }

    /**
     * Tampilkan detail tugas (Bisa dilihat Guru, Siswa sekelas, Admin).
     */
    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        $isGuruOwner = ($user->role === 'guru' && $user->guru && $assignment->guru_id === $user->guru->id);
        $isStudentInClass = ($user->role === 'siswa' && $user->siswa && $assignment->kelas_id === $user->siswa->kelas_id);
        $isAdmin = in_array($user->role, ['super_admin', 'admin_sekolah', 'operator', 'wali_kelas']);

        if (!$isGuruOwner && !$isStudentInClass && !$isAdmin) {
            abort(403, 'Akses ditolak.');
        }

        return view('assignments.show', compact('assignment'));
    }
}
