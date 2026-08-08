<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use App\Models\PengumumanRead;
use App\Models\Kelas;
use App\Http\Requests\StorePengumumanRequest;
use App\Http\Requests\UpdatePengumumanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengumuman::with(['targetKelas', 'creator'])->withCount('reads');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('konten', 'like', "%{$search}%");
            });
        }

        if ($request->filled('target')) {
            $query->where('target', $request->input('target'));
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->input('kategori'));
        }

        if ($request->filled('is_aktif')) {
            $query->where('is_aktif', $request->input('is_aktif'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $pengumuman = $query->orderBy('is_pinned', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage)
                            ->withQueryString();

        $kelases = Kelas::orderBy('nama')->get();
        $tingkats = Kelas::whereNotNull('tingkat')
            ->where('tingkat', '!=', '')
            ->distinct()
            ->orderBy('tingkat')
            ->pluck('tingkat');

        if ($request->ajax()) {
            return view('admin.pengumuman.table', compact('pengumuman', 'kelases', 'tingkats'))->render();
        }

        return view('admin.pengumuman.index', compact('pengumuman', 'kelases', 'tingkats'));
    }

    public function store(StorePengumumanRequest $request)
    {
        $data = $request->validated();

        $data['is_pinned']  = $request->has('is_pinned');
        $data['is_popup']   = $request->has('is_popup');
        $data['force_read'] = $request->has('force_read');
        $data['is_aktif']   = $request->has('is_aktif');
        $data['created_by'] = auth()->id();
        $data['slug']       = Str::slug($data['judul']) . '-' . Str::random(5);

        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/pengumuman', $filename, 'public');
            $data['lampiran'] = $path;
        }

        $item = Pengumuman::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil dibuat.',
                'data'    => $item,
            ]);
        }

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function show($id)
    {
        $item = Pengumuman::with(['targetKelas', 'creator'])->withCount('reads')->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $item,
            ]);
        }

        return view('admin.pengumuman.show', compact('item'));
    }

    public function update(UpdatePengumumanRequest $request, $id)
    {
        $item = Pengumuman::findOrFail($id);
        $data = $request->validated();

        $data['is_pinned']  = $request->has('is_pinned');
        $data['is_popup']   = $request->has('is_popup');
        $data['force_read'] = $request->has('force_read');
        $data['is_aktif']   = $request->has('is_aktif');

        if ($request->hasFile('lampiran')) {
            if ($item->lampiran && Storage::disk('public')->exists($item->lampiran)) {
                Storage::disk('public')->delete($item->lampiran);
            }

            $file = $request->file('lampiran');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/pengumuman', $filename, 'public');
            $data['lampiran'] = $path;
        }

        if ($data['target'] !== 'kelas') {
            $data['target_kelas_id'] = null;
        }

        $item->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengumuman berhasil diperbarui.',
                'data'    => $item,
            ]);
        }

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy($id, Request $request)
    {
        $item = Pengumuman::findOrFail($id);
        $judul = $item->judul;

        if ($item->lampiran && Storage::disk('public')->exists($item->lampiran)) {
            Storage::disk('public')->delete($item->lampiran);
        }

        $item->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pengumuman \"{$judul}\" berhasil dihapus.",
            ]);
        }

        return redirect()->route('admin.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function togglePin($id, Request $request)
    {
        $item = Pengumuman::findOrFail($id);
        $item->is_pinned = !$item->is_pinned;
        $item->save();

        $msg = $item->is_pinned ? 'Pengumuman disematkan di bagian teratas.' : 'Pengumuman dilepas dari penyematan.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'is_pinned' => $item->is_pinned,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function markAsRead($id, Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $item = Pengumuman::findOrFail($id);

        PengumumanRead::firstOrCreate([
            'pengumuman_id' => $item->id,
            'user_id'       => $user->id,
        ], [
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengumuman berhasil ditandai sebagai dibaca.',
        ]);
    }
}
