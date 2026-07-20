<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveLimit;
use App\Models\User;
use App\Services\LeaveLimitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveLimitController extends Controller
{
    public function __construct(
        private LeaveLimitService $leaveLimitService
    ) {}

    /**
     * Daftar aturan limit.
     */
    public function index(Request $request): View
    {
        $query = LeaveLimit::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $leaveLimits = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.leave-limits.index', compact('leaveLimits'));
    }

    /**
     * Form tambah aturan baru.
     */
    public function create(): View
    {
        $roles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_STAFF_TU,
            User::ROLE_SISWA,
            User::ROLE_ORANG_TUA,
            User::ROLE_PIKET,
        ];

        return view('admin.leave-limits.form', compact('roles'));
    }

    /**
     * Simpan aturan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'leave_type'    => 'required|in:sick,permission,all',
            'max_days'      => 'required|integer|min:1',
            'period'        => 'required|in:monthly,semester,yearly',
            'action_type'   => 'required|in:warning,block',
            'target_roles'  => 'required|array',
            'target_roles.*' => 'string',
            'target_grades' => 'nullable|array',
            'target_grades.*' => 'string',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        LeaveLimit::create($validated);

        return redirect()
            ->route('admin.leave-limits.index')
            ->with('success', 'Aturan limit berhasil ditambahkan.');
    }

    /**
     * Form ubah aturan.
     */
    public function edit(LeaveLimit $leaveLimit): View
    {
        $roles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_ADMIN_SEKOLAH,
            User::ROLE_OPERATOR,
            User::ROLE_GURU,
            User::ROLE_WALI_KELAS,
            User::ROLE_STAFF_TU,
            User::ROLE_SISWA,
            User::ROLE_ORANG_TUA,
            User::ROLE_PIKET,
        ];

        return view('admin.leave-limits.form', compact('leaveLimit', 'roles'));
    }

    /**
     * Simpan perubahan aturan.
     */
    public function update(Request $request, LeaveLimit $leaveLimit): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'leave_type'    => 'required|in:sick,permission,all',
            'max_days'      => 'required|integer|min:1',
            'period'        => 'required|in:monthly,semester,yearly',
            'action_type'   => 'required|in:warning,block',
            'target_roles'  => 'required|array',
            'target_roles.*' => 'string',
            'target_grades' => 'nullable|array',
            'target_grades.*' => 'string',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $leaveLimit->update($validated);

        return redirect()
            ->route('admin.leave-limits.index')
            ->with('success', 'Aturan limit berhasil diperbarui.');
    }

    /**
     * Toggle status aktif/nonaktif aturan limit.
     */
    public function toggleStatus(LeaveLimit $leaveLimit)
    {
        $leaveLimit->is_active = !$leaveLimit->is_active;
        $leaveLimit->save();

        return response()->json([
            'success'   => true,
            'is_active' => $leaveLimit->is_active,
            'message'   => 'Status aturan "' . $leaveLimit->name . '" berhasil ' . ($leaveLimit->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.',
        ]);
    }

    /**
     * Hapus aturan limit (force delete karena bukan soft delete).
     */
    public function destroy(LeaveLimit $leaveLimit): RedirectResponse
    {
        // Hapus juga semua balance terkait
        $leaveLimit->leaveBalances()->delete();
        $leaveLimit->delete();

        return redirect()
            ->route('admin.leave-limits.index')
            ->with('success', 'Aturan limit berhasil dihapus.');
    }

    /**
     * Form tambah kuota ekstra (dispensasi) untuk user.
     */
    public function dispensationForm(User $user): View
    {
        $limits = LeaveLimit::where('is_active', true)->orderBy('name')->get();

        $currentBalance = null;
        if (old('leave_limit_id')) {
            $limit = LeaveLimit::find(old('leave_limit_id'));
            if ($limit) {
                $currentBalance = $this->leaveLimitService->getUserBalance($user, $limit);
            }
        }

        return view('admin.leave-limits.dispensation', compact('user', 'limits', 'currentBalance'));
    }

    /**
     * Proses tambah kuota ekstra (dispensasi).
     */
    public function grantDispensation(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'leave_limit_id' => 'required|exists:leave_limits,id',
            'extra_days'     => 'required|integer|min:1',
            'reason'         => 'required|string|max:500',
        ]);

        $limit = LeaveLimit::findOrFail($validated['leave_limit_id']);

        $this->leaveLimitService->addDispensation(
            $user,
            $limit,
            (int) $validated['extra_days'],
            $validated['reason']
        );

        return redirect()
            ->route('admin.leave-limits.index')
            ->with('success', "Dispensasi kuota {$validated['extra_days']} hari berhasil diberikan kepada {$user->name}.");
    }

    /**
     * Endpoint AJAX untuk mengecek sisa kuota user saat mengisi form izin.
     *
     * @param  Request $request  (user_id, leave_type, start_date, end_date)
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkQuota(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'leave_type' => 'required|in:sick,permission,all',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $user = User::findOrFail($request->user_id);
        $start = \Carbon\Carbon::parse($request->start_date);
        $end   = \Carbon\Carbon::parse($request->end_date);

        // Hitung jumlah hari (inklusif)
        $requestDays = $start->diffInDays($end) + 1;

        $result = $this->leaveLimitService->validateQuota(
            $user,
            $request->leave_type,
            $requestDays
        );

        return response()->json([
            'success'       => true,
            'request_days'  => $requestDays,
            'allowed'       => $result['allowed'],
            'is_overlimit'  => $result['is_overlimit'],
            'action_type'   => $result['action_type'],
            'balances'      => $result['balances'],
            'message'       => $result['allowed']
                ? 'Kuota mencukupi'
                : ($result['action_type'] === 'block'
                    ? 'Maaf, kuota izin Anda sudah habis. Pengajuan tidak dapat dilanjutkan.'
                    : 'Perhatian: Kuota izin Anda hampir/habis. Segera hubungi admin jika perlu dispensasi.'),
        ]);
    }
}
