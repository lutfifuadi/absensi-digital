<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdCardTemplate;
use App\Models\Pengaturan;
use App\Models\StaffTataUsaha;
use App\Models\User;
use App\Services\IdCardPdfService;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffTataUsahaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);
        $sortBy = $request->query('sort_by', 'nama_lengkap');
        $sortDir = $request->query('sort_dir', 'asc');
        $status = $request->query('status');

        $allowedSortColumns = ['nama_lengkap', 'nip', 'jabatan', 'status', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'nama_lengkap';
        $sortDir = in_array($sortDir, ['asc', 'desc']) ? $sortDir : 'asc';

        $staff = StaffTataUsaha::with('user')
            ->when($status, function ($query, $status) {
                $query->where('staff_tata_usaha.status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('staff_tata_usaha.nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('staff_tata_usaha.nip', 'like', "%{$search}%")
                      ->orWhere('staff_tata_usaha.jabatan', 'like', "%{$search}%")
                      ->orWhere('staff_tata_usaha.no_hp', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q) use ($search) {
                          $q->where('email', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('admin.staff-tata-usaha.table', compact('staff', 'sortBy', 'sortDir'))->render();
        }

        return view('admin.staff-tata-usaha.index', compact('staff', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.staff-tata-usaha.form', ['staff' => new StaffTataUsaha()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:staff_tata_usaha,nip',
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $email = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;

        DB::transaction(function () use ($data, $email) {
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'username' => $data['nip'],
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_STAFF_TU,
            ]);

            StaffTataUsaha::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nama_lengkap' => $data['nama_lengkap'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'jabatan' => $data['jabatan'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'status' => $data['status'],
                'qr_code' => QrCodeGenerator::generate('STAFF'),
                'qr_code_nip' => $data['nip'],
            ]);
        });

        return redirect()->route('admin.staff-tata-usaha.index')->with('success', 'Staff TU berhasil ditambahkan.');
    }

    public function edit(StaffTataUsaha $staffTataUsaha)
    {
        return view('admin.staff-tata-usaha.form', ['staff' => $staffTataUsaha]);
    }

    public function update(Request $request, StaffTataUsaha $staffTataUsaha)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:staff_tata_usaha,nip,' . $staffTataUsaha->id,
            'jenis_kelamin' => 'required|in:L,P',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
            'email' => 'nullable|email|unique:users,email,' . $staffTataUsaha->user_id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $email = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;

        DB::transaction(function () use ($data, $staffTataUsaha, $email) {
            $staffTataUsaha->update([
                'nama_lengkap' => $data['nama_lengkap'],
                'nip' => $data['nip'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'jabatan' => $data['jabatan'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'status' => $data['status'],
                'qr_code_nip' => $data['nip'],
            ]);

            $staffTataUsaha->user->update([
                'name' => $data['nama_lengkap'],
                'username' => $data['nip'],
                'email' => $email,
            ]);

            if (! empty($data['password'])) {
                $staffTataUsaha->user->update(['password' => Hash::make($data['password'])]);
            }
        });

        return redirect()->route('admin.staff-tata-usaha.index')->with('success', 'Staff TU berhasil diperbarui.');
    }

    public function destroy(StaffTataUsaha $staffTataUsaha)
    {
        $staffTataUsaha->delete();

        return redirect()->route('admin.staff-tata-usaha.index')->with('success', 'Staff TU berhasil dihapus.');
    }

    /**
     * Re-generate Dual QR Code untuk 1 Staff (AJAX).
     */
    public function regenerateQr(Request $request, StaffTataUsaha $staffTataUsaha)
    {
        $newQrCode = QrCodeGenerator::generate('STAFF');
        $newQrCodeNip = $staffTataUsaha->nip;

        $staffTataUsaha->update([
            'qr_code'     => $newQrCode,
            'qr_code_nip' => $newQrCodeNip,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Dual QR Code untuk {$staffTataUsaha->nama_lengkap} berhasil diperbarui.",
            'qr_code' => $newQrCode,
            'qr_code_nip' => $newQrCodeNip,
        ]);
    }

    /**
     * Re-generate Dual QR Code Terpilih (Bulk AJAX).
     */
    public function regenerateQrBulk(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:staff_tata_usaha,id',
        ]);

        $staffs = StaffTataUsaha::whereIn('id', $request->ids)->get();
        $count = 0;

        foreach ($staffs as $staff) {
            $staff->update([
                'qr_code'     => QrCodeGenerator::generate('STAFF'),
                'qr_code_nip' => $staff->nip,
            ]);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Dual QR Code untuk {$count} staff berhasil diperbarui.",
        ]);
    }

    /**
     * Re-generate Dual QR Code untuk SELURUH Staff (All AJAX).
     */
    public function regenerateQrAll(Request $request)
    {
        $staffs = StaffTataUsaha::all();
        $count = 0;

        foreach ($staffs as $staff) {
            $staff->update([
                'qr_code'     => QrCodeGenerator::generate('STAFF'),
                'qr_code_nip' => $staff->nip,
            ]);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Dual QR Code untuk SELURUH ({$count}) staff berhasil diperbarui.",
        ]);
    }

    /**
     * Cetak kartu QR staff (Semua) -> download PDF.
     */
    public function cetakQr(Request $request)
    {
        $staffList = StaffTataUsaha::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $template = IdCardTemplate::where('type', 'staff')->active()->first();

        if (! $template) {
            return back()->with('error', 'Template ID Card untuk Staff belum diaktifkan. Silakan buat dan aktifkan template terlebih dahulu di menu ID Card Templates.');
        }

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuStaff($staffList, $template, 'kartu-identitas-staff-semua');
        } catch (\Exception $e) {
            Log::error('Gagal cetak kartu staff: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    /**
     * Generate & download kartu QR untuk satu staff.
     */
    public function generateQrSatu(StaffTataUsaha $staffTataUsaha)
    {
        if (! $staffTataUsaha->qr_code || ! $staffTataUsaha->qr_code_nip) {
            $staffTataUsaha->update([
                'qr_code' => $staffTataUsaha->qr_code ?? QrCodeGenerator::generate('STAFF'),
                'qr_code_nip' => $staffTataUsaha->qr_code_nip ?? $staffTataUsaha->nip,
            ]);
        }

        $template = IdCardTemplate::where('type', 'staff')->active()->first();

        if (! $template) {
            return back()->with('error', 'Template ID Card untuk Staff belum diaktifkan. Silakan buat dan aktifkan template terlebih dahulu di menu ID Card Templates.');
        }

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuStaff(collect([$staffTataUsaha]), $template, "kartu-identitas-{$staffTataUsaha->nip}");
        } catch (\Exception $e) {
            Log::error('Gagal generate kartu staff: ' . $e->getMessage());
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    /**
     * Cetak kartu pilihan staff berdasarkan checkbox IDs.
     */
    public function cetakKartuPilihan(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:staff_tata_usaha,id',
        ]);

        $staffList = StaffTataUsaha::whereIn('id', $request->ids)
            ->orderBy('nama_lengkap')
            ->get();

        $template = IdCardTemplate::where('type', 'staff')->active()->first();

        if (! $template) {
            return back()->with('error', 'Template ID Card untuk Staff belum diaktifkan. Silakan buat dan aktifkan template terlebih dahulu di menu ID Card Templates.');
        }

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuStaff($staffList, $template, 'kartu-identitas-staff-pilihan');
        } catch (\Exception $e) {
            Log::error('Gagal cetak kartu pilihan staff: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }
}
