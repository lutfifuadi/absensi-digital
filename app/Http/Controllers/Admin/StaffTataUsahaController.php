<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Models\StaffTataUsaha;
use App\Models\User;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffTataUsahaController extends Controller
{
    public function index()
    {
        $staff = StaffTataUsaha::with('user')->orderBy('nama_lengkap')->get();

        return view('admin.staff-tata-usaha.index', compact('staff'));
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
     * Cetak kartu QR staff (Semua) -> download PDF.
     */
    public function cetakQr(Request $request)
    {
        $staffList = StaffTataUsaha::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $template = \App\Models\IdCardTemplate::where('type', 'staff')->active()->first();

        if (!$template) {
            return back()->with('error', 'Silakan buat dan aktifkan template ID Card untuk Staff terlebih dahulu.');
        }

        $config = $template->config;
        $entities = $staffList;

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
                  ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
                  ->download("kartu-identitas-staff-semua.pdf");
    }

    /**
     * Generate & download kartu QR untuk satu staff.
     */
    public function generateQrSatu(StaffTataUsaha $staff)
    {
        if (!$staff->qr_code) {
            $staff->update(['qr_code' => QrCodeGenerator::generate('STAFF')]);
        }

        $template = \App\Models\IdCardTemplate::where('type', 'staff')->active()->first();

        if (!$template) {
            return back()->with('error', 'Silakan buat dan aktifkan template ID Card untuk Staff terlebih dahulu.');
        }

        $config = $template->config;
        $entities = collect([$staff]);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
                  ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
                  ->download("kartu-identitas-{$staff->nip}.pdf");
    }
}
