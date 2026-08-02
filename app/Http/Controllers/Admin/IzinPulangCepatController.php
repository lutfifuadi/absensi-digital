<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\IzinPulangCepat;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IzinPulangCepatController extends Controller
{
    public function index(Request $request)
    {
        $query = IzinPulangCepat::with(['user', 'approver', 'satpam'])->orderByDesc('created_at');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_izin', 'like', "%{$search}%")
                  ->orWhere('alasan', 'like', "%{$search}%")
                  ->orWhere('nama_penjemput', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        $izinPulangCepat = $query->paginate(15)->withQueryString();

        return view('admin.izin-pulang-cepat.index', compact('izinPulangCepat'));
    }

    public function create()
    {
        $siswaOptions = Siswa::with('user:id')->orderBy('nama_lengkap')->get();
        $guruOptions = Guru::with('user:id')->orderBy('nama_lengkap')->get();
        $staffOptions = StaffTataUsaha::with('user:id')->orderBy('nama_lengkap')->get();

        return view('admin.izin-pulang-cepat.create', compact('siswaOptions', 'guruOptions', 'staffOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kategori'           => 'required|in:siswa,guru,staff',
            'reference_id'       => 'required|integer',
            'tanggal'            => 'required|date',
            'jam_rencana_keluar' => 'required|date_format:H:i',
            'jenis_alasan'       => 'required|in:sakit,urusan_keluarga,dinas_luar,dispensasi,lainnya',
            'alasan'             => 'required|string',
            'nama_penjemput'     => 'nullable|string|max:100',
            'no_hp_penjemput'    => 'nullable|string|max:20',
            'lampiran'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $pengajuUser = $this->resolveUserFromReference($validated['kategori'], $validated['reference_id']);
        if (!$pengajuUser) {
            return back()->withInput()->with('error', 'Data pengaju tidak valid atau user pengaju tidak ditemukan.');
        }

        $todayStr = date('Ymd');
        $randomSeq = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $kodeIzin = "IPC-{$todayStr}-{$randomSeq}";
        while (IzinPulangCepat::where('kode_izin', $kodeIzin)->exists()) {
            $randomSeq = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
            $kodeIzin = "IPC-{$todayStr}-{$randomSeq}";
        }

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('izin-pulang-cepat', 'public');
        }

        IzinPulangCepat::create([
            'kode_izin'          => $kodeIzin,
            'kategori'           => $validated['kategori'],
            'reference_id'       => $validated['reference_id'],
            'user_id'            => $pengajuUser->id,
            'tanggal'            => $validated['tanggal'],
            'jam_rencana_keluar' => $validated['jam_rencana_keluar'],
            'alasan'             => $validated['alasan'],
            'jenis_alasan'       => $validated['jenis_alasan'],
            'lampiran'           => $lampiranPath,
            'nama_penjemput'     => $validated['nama_penjemput'] ?? null,
            'no_hp_penjemput'    => $validated['no_hp_penjemput'] ?? null,
            'status'             => 'pending',
        ]);

        return redirect()
            ->route('admin.izin-pulang-cepat.index')
            ->with('success', "Pengajuan izin pulang cepat berhasil dibuat dengan kode {$kodeIzin}.");
    }

    public function show(IzinPulangCepat $izinPulangCepat)
    {
        $izinPulangCepat->load(['user', 'approver', 'satpam']);
        $reference = $izinPulangCepat->reference;

        $qrCode = null;
        if (class_exists(QrCode::class)) {
            try {
                $qrCode = QrCode::size(180)->generate($izinPulangCepat->kode_izin);
            } catch (\Exception $e) {
                $qrCode = null;
            }
        }

        return view('admin.izin-pulang-cepat.show', [
            'izin'      => $izinPulangCepat,
            'reference' => $reference,
            'qrCode'    => $qrCode,
        ]);
    }

    public function approve(Request $request, IzinPulangCepat $izinPulangCepat): RedirectResponse
    {
        if ($izinPulangCepat->status !== 'pending') {
            return back()->with('error', 'Status izin ini tidak bisa disetujui lagi.');
        }

        $izinPulangCepat->update([
            'status'           => 'approved',
            'disetujui_oleh'   => Auth::id(),
            'disetujui_pada'   => now(),
            'catatan_approver' => $request->input('catatan_approver'),
        ]);

        return back()->with('success', "Izin pulang cepat {$izinPulangCepat->kode_izin} berhasil disetujui.");
    }

    public function reject(Request $request, IzinPulangCepat $izinPulangCepat): RedirectResponse
    {
        $request->validate([
            'catatan_approver' => 'required|string|max:500',
        ], [
            'catatan_approver.required' => 'Catatan/alasan penolakan wajib diisi.',
        ]);

        if ($izinPulangCepat->status !== 'pending') {
            return back()->with('error', 'Status izin ini tidak dalam status pending.');
        }

        $izinPulangCepat->update([
            'status'           => 'rejected',
            'disetujui_oleh'   => Auth::id(),
            'disetujui_pada'   => now(),
            'catatan_approver' => $request->catatan_approver,
        ]);

        return back()->with('success', "Izin pulang cepat {$izinPulangCepat->kode_izin} telah ditolak.");
    }

    public function destroy(IzinPulangCepat $izinPulangCepat): RedirectResponse
    {
        if ($izinPulangCepat->lampiran && Storage::disk('public')->exists($izinPulangCepat->lampiran)) {
            Storage::disk('public')->delete($izinPulangCepat->lampiran);
        }

        $kode = $izinPulangCepat->kode_izin;
        $izinPulangCepat->delete();

        return redirect()
            ->route('admin.izin-pulang-cepat.index')
            ->with('success', "Pengajuan izin {$kode} berhasil dihapus/dibatalkan.");
    }

    private function resolveUserFromReference(string $kategori, int $referenceId): ?User
    {
        return match ($kategori) {
            'siswa' => Siswa::find($referenceId)?->user,
            'guru'  => Guru::find($referenceId)?->user,
            'staff' => StaffTataUsaha::find($referenceId)?->user,
            default => null,
        };
    }
}
