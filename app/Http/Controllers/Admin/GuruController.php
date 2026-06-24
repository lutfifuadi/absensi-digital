<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Pengaturan;
use App\Models\User;
use App\Support\QrCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        return view('admin.guru.form', compact('guru', 'user'));
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
        return view('admin.guru.form', compact('guru'));
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

        $template = \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (!$template) {
            // Generate QR data URI per guru
            $qrImages = $guruList->mapWithKeys(fn (Guru $g) => [
                $g->id => QrCodeGenerator::renderDataUri($g->qr_code ?? QrCodeGenerator::generate('GURU'), 160),
            ]);
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.guru.kartu-qr-pdf', compact('guruList', 'namaSekolah', 'qrImages'))
                      ->setPaper('a4', 'portrait')
                      ->download("kartu-qr-guru-semua.pdf");
        }

        $config = $template->config;
        $entities = $guruList;

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
                  ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
                  ->download("kartu-identitas-guru-semua.pdf");
    }

    /**
     * Generate & download kartu QR untuk satu guru.
     */
    public function generateQrSatu(Guru $guru)
    {
        if (!$guru->qr_code) {
            $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
        }

        $template = \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (!$template) {
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
            $qrImage = QrCodeGenerator::renderDataUri($guru->qr_code, 200);
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.guru.kartu-qr-satu-pdf', compact('guru', 'namaSekolah', 'qrImage'))
                      ->setPaper([0, 0, 226.77, 283.46]) // 8x10 cm
                      ->download("kartu-qr-{$guru->nip}.pdf");
        }

        $config = $template->config;
        $entities = collect([$guru]);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
                  ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
                  ->download("kartu-identitas-{$guru->nip}.pdf");
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
}
