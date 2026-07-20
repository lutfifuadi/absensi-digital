<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiStaff;
use App\Models\ActivityLog;
use App\Models\Guru;
use App\Models\IzinSakit;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use App\Models\User;
use App\Notifications\IzinDiajukanNotification;
use App\Notifications\IzinDisetujuiNotification;
use App\Services\LeaveLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Pengaturan;
use App\Jobs\SendWhatsAppMessage;

class IzinSakitController extends Controller
{
    public function __construct(
        private LeaveLimitService $leaveLimitService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = IzinSakit::with(['approver'])->orderByDesc('tanggal_mulai');

        // Scoping per role
        if ($user->isRole(User::ROLE_SISWA)) {
            $siswa = $user->siswa;
            if (!$siswa) abort(404, 'Data siswa tidak ditemukan.');
            $query->where('tipe', 'siswa')->where('reference_id', $siswa->id);
        } elseif ($user->isRole(User::ROLE_GURU)) {
            $guru = $user->guru;
            if (!$guru) abort(404, 'Data guru tidak ditemukan.');
            $query->where('tipe', 'guru')->where('reference_id', $guru->id);
        } elseif ($user->isRole(User::ROLE_STAFF_TU)) {
            $staff = $user->staff;
            if (!$staff) abort(404, 'Data staff tidak ditemukan.');
            $query->where('tipe', 'staff')->where('reference_id', $staff->id);
        } elseif ($user->isRole(User::ROLE_WALI_KELAS)) {
            // Wali kelas can see their class students' permits
            $guru = $user->guru;
            $classIds = \App\Models\Kelas::where('wali_kelas_id', $guru?->id)->pluck('id');
            $siswaIdsInClass = \App\Models\Siswa::whereIn('kelas_id', $classIds)->pluck('id');

            $query->where(function($q) use ($guru, $siswaIdsInClass) {
                $q->where(function($sq) use ($guru) {
                    $sq->where('tipe', 'guru')->where('reference_id', $guru?->id);
                })->orWhere(function($sq) use ($siswaIdsInClass) {
                    $sq->where('tipe', 'siswa')->whereIn('reference_id', $siswaIdsInClass);
                });
            });
        }

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $izinSakit = $query->paginate(20)->withQueryString();

        return view('admin.izin-sakit.index', compact('izinSakit'));
    }

    public function create()
    {
        $siswaOptions = Siswa::with('user:id')->orderBy('nama_lengkap')->get();
        $guruOptions = Guru::with('user:id')->orderBy('nama_lengkap')->get();
        $staffOptions = StaffTataUsaha::with('user:id')->orderBy('nama_lengkap')->get();

        return view('admin.izin-sakit.form', compact('siswaOptions', 'guruOptions', 'staffOptions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipe'           => 'required|in:siswa,guru,staff',
            'reference_id'   => 'required|integer',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'jenis'          => 'required|in:sakit,izin',
            'keterangan'     => 'nullable|string',
            'lampiran'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:100',
        ]);

        $this->validateReferenceId($data['tipe'], $data['reference_id']);

        // ── Batasan Perizinan (PRD-001) ──────────────────────────────────────
        // Dapatkan user_id dari reference
        $pengajuUser = $this->resolveUserFromReference($data['tipe'], $data['reference_id']);

        if ($pengajuUser) {
            // Mapping jenis izin ke leave_type
            $leaveType = $data['jenis'] === 'sakit' ? 'sick' : 'permission';

            // Hitung jumlah hari
            $start = \Carbon\Carbon::parse($data['tanggal_mulai']);
            $end   = \Carbon\Carbon::parse($data['tanggal_selesai']);
            $requestDays = $start->diffInDays($end) + 1;

            // Validasi kuota
            $quotaCheck = $this->leaveLimitService->validateQuota(
                $pengajuUser,
                $leaveType,
                $requestDays
            );

            // Jika di-block, tolak pengajuan
            if (!$quotaCheck['allowed'] && $quotaCheck['action_type'] === 'block') {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan ditolak: Kuota izin/' . $data['jenis'] . ' Anda sudah habis. Silakan hubungi admin untuk dispensasi.');
            }

            // Set overlimit flag
            $data['is_overlimit'] = $quotaCheck['is_overlimit'];
            $data['overlimit_reason'] = $quotaCheck['is_overlimit']
                ? 'Melebihi batas kuota (' . $requestDays . ' hari diajukan, sisa: ' . ($quotaCheck['balances'][0]['remaining'] ?? 0) . ' hari)'
                : null;
            $data['is_dispensation'] = false;
            $data['user_id'] = $pengajuUser->id;
        }
        // ─────────────────────────────────────────────────────────────────────

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('izin-lampiran', 'public');
        } else {
            unset($data['lampiran']);
        }

        $data['status'] = 'pending';

        $izin = IzinSakit::create($data);

        // Notify all admin & super_admin users
        $admins = User::whereIn('role', ['super_admin', 'admin_sekolah'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new IzinDiajukanNotification($izin));
        }

        // WhatsApp Gateway to Admin / PIC
        $nomorAdmin = Pengaturan::where('key', 'penerima_notifikasi_ajuan_ijin')->value('value');
        if ($nomorAdmin && Pengaturan::where('key', 'jenis_notifikasi_ortu')->value('value') === 'WhatsApp (WA)') {
            $nama = "-";
            if ($data['tipe'] === 'siswa') $nama = Siswa::find($data['reference_id'])?->nama_lengkap;
            elseif ($data['tipe'] === 'guru') $nama = Guru::find($data['reference_id'])?->nama_lengkap;
            elseif ($data['tipe'] === 'staff') $nama = StaffTataUsaha::find($data['reference_id'])?->nama_lengkap;

            $pesan = "*AJUAN {$data['jenis']} BARU*\n\n";
            $pesan .= "Tipe: " . ucfirst($data['tipe']) . "\n";
            $pesan .= "Nama: {$nama}\n";
            $pesan .= "Tanggal: {$data['tanggal_mulai']} s.d {$data['tanggal_selesai']}\n";
            $pesan .= "Ket: {$data['keterangan']}\n\n";
            $pesan .= "Silakan cek di sistem untuk Approve/Reject.";

            SendWhatsAppMessage::dispatch($nomorAdmin, $pesan, 'Notifikasi Admin Absensi', false);
        }

        return redirect()->route('admin.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil disimpan.');
    }

    /**
     * Resolve User model dari reference tipe & id.
     */
    protected function resolveUserFromReference(string $tipe, int $referenceId): ?User
    {
        if ($tipe === 'siswa') {
            $siswa = Siswa::find($referenceId);
            return $siswa?->user;
        }
        if ($tipe === 'guru') {
            $guru = Guru::find($referenceId);
            return $guru?->user;
        }
        if ($tipe === 'staff') {
            $staff = StaffTataUsaha::find($referenceId);
            return $staff?->user;
        }
        return null;
    }

    protected function validateReferenceId(string $tipe, int $referenceId): void
    {
        if ($tipe === 'siswa' && ! Siswa::where('id', $referenceId)->exists()) {
            abort(422, 'Referensi siswa tidak ditemukan.');
        }
        if ($tipe === 'guru' && ! Guru::where('id', $referenceId)->exists()) {
            abort(422, 'Referensi guru tidak ditemukan.');
        }
        if ($tipe === 'staff' && ! StaffTataUsaha::where('id', $referenceId)->exists()) {
            abort(422, 'Referensi staff tidak ditemukan.');
        }
    }

    public function edit(IzinSakit $izinSakit)
    {
        $siswaOptions = Siswa::with('user:id')->orderBy('nama_lengkap')->get();
        $guruOptions = Guru::with('user:id')->orderBy('nama_lengkap')->get();
        $staffOptions = StaffTataUsaha::with('user:id')->orderBy('nama_lengkap')->get();

        return view('admin.izin-sakit.form', compact('izinSakit', 'siswaOptions', 'guruOptions', 'staffOptions'));
    }

    public function update(Request $request, IzinSakit $izinSakit)
    {
        $data = $request->validate([
            'tipe'           => 'required|in:siswa,guru,staff',
            'reference_id'   => 'required|integer',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'jenis'          => 'required|in:sakit,izin',
            'keterangan'     => 'nullable|string',
            'lampiran'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:100',
            'status'         => 'required|in:pending,disetujui,ditolak',
        ]);

        $this->validateReferenceId($data['tipe'], $data['reference_id']);

        $previousStatus = $izinSakit->status;

        if ($request->hasFile('lampiran')) {
            if ($izinSakit->lampiran) {
                Storage::disk('public')->delete($izinSakit->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('izin-lampiran', 'public');
        } else {
            unset($data['lampiran']);
        }

        // Handle approval
        if ($data['status'] !== $previousStatus && in_array($data['status'], ['disetujui', 'ditolak'])) {
            $data['disetujui_oleh'] = Auth::id();

            // Update absensi records if disetujui
            if ($data['status'] === 'disetujui') {
                $this->updateAbsensiFromIzin($izinSakit, $data);

                // ── Kurangi kuota user saat disetujui ────────────────────────
                $this->deductLeaveQuota($izinSakit);
                // ─────────────────────────────────────────────────────────────
            }
        }

        $izinSakit->update($data);

        // Send notification if status changed
        if ($data['status'] !== $previousStatus && in_array($data['status'], ['disetujui', 'ditolak'])) {
            // Notify submitter or related admin
            $admins = User::whereIn('role', ['super_admin', 'admin_sekolah'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new IzinDisetujuiNotification($izinSakit));
            }
        }

        return redirect()->route('admin.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil diperbarui.');
    }

    public function approve(Request $request, IzinSakit $izinSakit)
    {
        // ── BUG-001: Guard double-approve ────────────────────────────────────
        if ($izinSakit->status === 'disetujui') {
            return back()->with('error', 'Izin ini sudah disetujui sebelumnya. Kuota tidak akan dipotong lagi.');
        }
        // ─────────────────────────────────────────────────────────────────────

        $action = $request->input('action', 'disetujui');
        if (! in_array($action, ['disetujui', 'ditolak'])) {
            $action = 'disetujui';
        }

        if ($action === 'disetujui') {
            $this->updateAbsensiFromIzin($izinSakit, [
                'tipe' => $izinSakit->tipe,
                'jenis' => $izinSakit->jenis,
            ]);

            // ── Kurangi kuota user saat disetujui ────────────────────────────
            $this->deductLeaveQuota($izinSakit);
            // ────────────────────────────────────────────────────────────────
        }

        $izinSakit->update([
            'status'         => $action,
            'disetujui_oleh' => Auth::id(),
        ]);

        ActivityLog::record(
            $action === 'disetujui' ? 'approve' : 'reject',
            'izin',
            ucfirst($action) . " izin {$izinSakit->tipe} #{$izinSakit->id} (" . ($action === 'disetujui' ? '✅' : '❌') . ")"
        );

        $admins = User::whereIn('role', ['super_admin', 'admin_sekolah'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new IzinDisetujuiNotification($izinSakit));
        }

        return back()->with('success', 'Status izin berhasil diperbarui menjadi ' . $action . '.');
    }

    /**
     * Kurangi kuota leave_balances saat izin disetujui.
     */
    protected function deductLeaveQuota(IzinSakit $izin): void
    {
        $pengajuUser = null;

        // Jika user_id sudah terisi di record, gunakan itu
        if ($izin->user_id) {
            $pengajuUser = User::find($izin->user_id);
        } else {
            // Fallback: cari dari reference
            $pengajuUser = $this->resolveUserFromReference($izin->tipe, $izin->reference_id);
        }

        if (!$pengajuUser) {
            return;
        }

        $leaveType = $izin->jenis === 'sakit' ? 'sick' : 'permission';
        $start = \Carbon\Carbon::parse($izin->tanggal_mulai);
        $end   = \Carbon\Carbon::parse($izin->tanggal_selesai);
        $usedDays = $start->diffInDays($end) + 1;

        $this->leaveLimitService->deductQuota($pengajuUser, $leaveType, $usedDays);
    }

    protected function updateAbsensiFromIzin(IzinSakit $izin, array $data): void
    {
        $jenis      = $data['jenis'] ?? $izin->jenis;
        $statusBaru = $jenis === 'sakit' ? 'sakit' : 'izin';

        $current = $izin->tanggal_mulai->copy();
        while ($current->lte($izin->tanggal_selesai)) {
            $tanggal = $current->toDateString();

            if ($izin->tipe === 'siswa') {
                AbsensiSiswa::updateOrCreate(
                    ['siswa_id' => $izin->reference_id, 'tanggal' => $tanggal],
                    ['kelas_id' => Siswa::find($izin->reference_id)?->kelas_id, 'status' => $statusBaru,
                     'keterangan' => $izin->keterangan, 'metode' => 'manual']
                );
            } elseif ($izin->tipe === 'guru') {
                AbsensiGuru::updateOrCreate(
                    ['guru_id' => $izin->reference_id, 'tanggal' => $tanggal],
                    ['status' => $statusBaru, 'keterangan' => $izin->keterangan, 'metode' => 'manual']
                );
            } else {
                AbsensiStaff::updateOrCreate(
                    ['staff_id' => $izin->reference_id, 'tanggal' => $tanggal],
                    ['status' => $statusBaru, 'keterangan' => $izin->keterangan, 'metode' => 'manual']
                );
            }

            $current->addDay();
        }
    }

    public function destroy(IzinSakit $izinSakit)
    {
        if ($izinSakit->lampiran) {
            Storage::disk('public')->delete($izinSakit->lampiran);
        }
        $izinSakit->delete();

        return redirect()->route('admin.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dihapus.');
    }

    public function markRead(Request $request)
    {
        $query = $request->user()->unreadNotifications();

        if ($request->boolean('all')) {
            $query->update(['read_at' => now()]);
        } elseif ($request->filled('id')) {
            $query->where('id', $request->id)->update(['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }
}
