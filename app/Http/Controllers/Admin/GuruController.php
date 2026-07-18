<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\IdCardTemplate;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\IdCardPdfService;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Exports\GuruExport;
use App\Imports\GuruImport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index()
    {
        $guruUsers = User::with('guru')
            ->withRole(User::ROLE_GURU)
            ->orderBy('name')
            ->get();

        return view('admin.guru.index', compact('guruUsers'));
    }

    public function create(Request $request)
    {
        $guru = new Guru();
        $user = null;

        if ($request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
            if (! $user || ! $user->isRole(User::ROLE_GURU)) {
                return redirect()->route('admin.guru.index')
                    ->with('error', 'User tidak valid untuk profil guru.');
            }

            if ($user->guru) {
                return redirect()->route('admin.guru.edit', $user->guru->id)
                    ->with('info', 'Profil guru sudah tersedia. Anda diarahkan ke halaman edit.');
            }
        }

        $mapelOptions = \App\Models\Mapel::where('status', 1)->orderBy('nama_mapel')->get();

        return view('admin.guru.form', compact('guru', 'user', 'mapelOptions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:guru,nip',
            'jenis_kelamin' => 'required|in:L,P',
            'mata_pelajaran' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];

        if (! $request->filled('user_id')) {
            $rules['email'] = 'nullable|email|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $data = $request->validate($rules);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $user = null;

        DB::transaction(function () use ($data, $domainEmail, &$user) {
            if (! empty($data['user_id'])) {
                $user = User::find($data['user_id']);
                if (! $user || $user->guru) {
                    throw new \Exception('User tidak valid untuk profil guru.');
                }
            } else {
                $email = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;
                $user = User::create([
                    'name' => $data['nama_lengkap'],
                    'username' => $data['nip'],
                    'email' => $email,
                    'password' => Hash::make($data['password']),
                    'role' => User::ROLE_GURU,
                ]);
            }

            Guru::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
                'nama_lengkap' => $data['nama_lengkap'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'mata_pelajaran' => $data['mata_pelajaran'],
                'jabatan' => $data['jabatan'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'status' => $data['status'],
                'qr_code' => QrCodeGenerator::generate('GURU'),
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        $mapelOptions = \App\Models\Mapel::where('status', 1)->orderBy('nama_mapel')->get();
        return view('admin.guru.form', compact('guru', 'mapelOptions'));
    }

    public function update(Request $request, Guru $guru)
    {
        $data = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:guru,nip,' . $guru->id,
            'jenis_kelamin' => 'required|in:L,P',
            'mata_pelajaran' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:50',
            'status' => 'required|in:aktif,nonaktif',
            'email' => 'nullable|email|unique:users,email,' . $guru->user_id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $email = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;

        DB::transaction(function () use ($data, $guru, $email) {
            $guru->update([
                'nama_lengkap' => $data['nama_lengkap'],
                'nip' => $data['nip'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'mata_pelajaran' => $data['mata_pelajaran'],
                'jabatan' => $data['jabatan'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'status' => $data['status'],
            ]);

            $guru->user->update([
                'name' => $data['nama_lengkap'],
                'username' => $data['nip'],
                'email' => $email,
            ]);

            if (! empty($data['password'])) {
                $guru->user->update(['password' => Hash::make($data['password'])]);
            }
        });

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        $user = $guru->user;

        DB::transaction(function () use ($guru, $user) {
            if ($user) {
                $user->delete();
            } else {
                $guru->delete();
            }
        });

        return redirect()->route('admin.guru.index')
            ->with('success', 'Guru dan akun user berhasil dihapus.');
    }

    /**
     * Cetak kartu QR guru (Semua) -> download PDF.
     */
    public function cetakQr(Request $request)
    {
        $guruList = Guru::where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $template = IdCardTemplate::where('type', 'guru')->active()->first();

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuGuru($guruList, $template, 'kartu-identitas-guru-semua');
        } catch (\Exception $e) {
            Log::error('Gagal cetak kartu guru: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    /**
     * Generate & download kartu QR untuk satu guru.
     */
    public function generateQrSatu(Guru $guru)
    {
        if (! $guru->qr_code) {
            $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
        }

        $template = IdCardTemplate::where('type', 'guru')->active()->first();

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuGuru(collect([$guru]), $template, "kartu-identitas-{$guru->nip}");
        } catch (\Exception $e) {
            Log::error('Gagal generate kartu guru: ' . $e->getMessage());
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }

    /**
     * Cetak kartu pilihan guru berdasarkan checkbox IDs.
     */
    public function cetakKartuPilihan(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:guru,id',
        ]);

        $guruList = Guru::whereIn('id', $request->ids)
            ->orderBy('nama_lengkap')
            ->get();

        $template = IdCardTemplate::where('type', 'guru')->active()->first();

        if (! $template) {
            return back()->with('error', 'Template ID Card untuk Guru belum diaktifkan. Silakan buat dan aktifkan template terlebih dahulu di menu ID Card Templates.');
        }

        try {
            $service = new IdCardPdfService();
            return $service->renderKartuGuru($guruList, $template, 'kartu-identitas-guru-pilihan');
        } catch (\Exception $e) {
            Log::error('Gagal cetak kartu pilihan guru: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencetak kartu: ' . $e->getMessage());
        }
    }
    public function importStore(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new GuruImport(), $request->file('import_file'));

            return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil diimpor dari Excel.');
        } catch (ExcelValidationException $exception) {
            $failures = $exception->failures();

            $messages = collect($failures)->map(function ($failure) {
                $row = $failure->row();
                $attribute = $failure->attribute();
                $errors = implode(', ', $failure->errors());

                return "Baris {$row}: {$attribute} - {$errors}";
            })->implode(' | ');

            return redirect()->back()->with('error', 'Import gagal: ' . $messages);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Import gagal: ' . $th->getMessage());
        }
    }

    public function export(Request $request)
    {
        $search = $request->query('search');
        $filename = 'data_guru_' . now()->format('Y-m-d_H-i-s');

        return Excel::download(new GuruExport($search), $filename . '.xlsx');
    }

    public function downloadSample()
    {
        $headers = [
            'nip',
            'nama_lengkap',
            'jenis_kelamin',
            'mata_pelajaran',
            'jabatan',
            'no_hp',
            'status'
        ];

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            
            // Add a sample row
            fputcsv($file, [
                '197001012000011001',
                'Ahmad Guru Sampel, S.Pd',
                'L',
                'Matematika',
                'Guru Madya',
                '08123456789',
                'aktif'
            ]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=sampel_import_guru.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }

    public function destroyAll(Request $request)
    {
        $request->validate([
            'konfirmasi' => 'required|string|in:HAPUS SEMUA GURU',
        ], [
            'konfirmasi.in' => 'Konfirmasi harus persis "HAPUS SEMUA GURU".',
        ]);

        try {
            DB::transaction(function () {
                // Ambil daftar ID guru dan ID user guru terkait
                $guruList = Guru::all();
                $guruIds = $guruList->pluck('id')->toArray();
                $userIds = $guruList->pluck('user_id')->filter()->toArray();

                // Set NULL pada: kelas (wali_kelas_id), jadwal_pelajaran (guru_id), ekskul_absensi (pembina_id)
                \App\Models\Kelas::whereIn('wali_kelas_id', $guruIds)->update(['wali_kelas_id' => null]);
                \App\Models\JadwalPelajaran::whereIn('guru_id', $guruIds)->update(['guru_id' => null]);
                \App\Models\EkskulAbsensi::whereIn('pembina_id', $guruIds)->update(['pembina_id' => null]);

                // Hapus relasi: absensi_guru, ekskul_pembina, assignments, dan izin_sakit
                \App\Models\AbsensiGuru::whereIn('guru_id', $guruIds)->delete();
                \App\Models\EkskulPembina::whereIn('guru_id', $guruIds)->delete();
                \App\Models\Assignment::whereIn('guru_id', $guruIds)->delete();
                \App\Models\IzinSakit::where('tipe', 'guru')->whereIn('reference_id', $guruIds)->delete();

                // Hapus entitas Guru dan model User terkait
                Guru::whereIn('id', $guruIds)->delete();
                if (!empty($userIds)) {
                    User::whereIn('id', $userIds)->delete();
                }

                // Simpan catatan log aktivitas admin
                \App\Models\ActivityLog::record(
                    'DELETE_ALL',
                    'Guru',
                    'Menghapus semua data guru beserta akun user dan relasi terkait (' . count($guruIds) . ' guru).'
                );
            });

            return redirect()->route('admin.guru.index')->with('success', 'Semua data guru berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus semua data guru: ' . $e->getMessage());
        }
    }
}
