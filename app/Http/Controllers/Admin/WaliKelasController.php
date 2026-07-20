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
use Illuminate\Validation\Rule;

class WaliKelasController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sortBy = $request->query('sort_by', 'nama_lengkap');
        $sortDir = $request->query('sort_dir', 'asc');
        $perPage = (int) $request->query('per_page', 10);

        $allowedSorts = ['nama_lengkap', 'nip', 'mata_pelajaran', 'status', 'email'];
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
            ->withRole(User::ROLE_WALI_KELAS);

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
            // Urutkan berdasarkan nama lengkap guru, jika tidak ada pakai name user
            $query->orderBy(DB::raw('COALESCE(guru.nama_lengkap, users.name)'), $sortDir);
        } elseif ($sortBy === 'nip') {
            $query->orderBy('guru.nip', $sortDir);
        } elseif ($sortBy === 'mata_pelajaran') {
            $query->orderBy('guru.mata_pelajaran', $sortDir);
        } elseif ($sortBy === 'status') {
            $query->orderBy('guru.status', $sortDir);
        } elseif ($sortBy === 'email') {
            $query->orderBy('users.email', $sortDir);
        } else {
            $query->orderBy('users.name', $sortDir);
        }

        $waliKelasUsers = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('admin.wali-kelas.table', compact('waliKelasUsers', 'sortBy', 'sortDir'));
        }

        return view('admin.wali-kelas.index', compact('waliKelasUsers', 'sortBy', 'sortDir'));
    }

    public function create(Request $request)
    {
        $guru = new Guru();
        $user = null;

        if ($request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
            if (! $user || ! $user->isRole(User::ROLE_WALI_KELAS)) {
                return redirect()->route('admin.wali-kelas.index')
                    ->with('error', 'User tidak valid untuk profil wali kelas.');
            }

            if ($user->guru) {
                return redirect()->route('admin.wali-kelas.edit', $user->guru->id)
                    ->with('info', 'Profil wali kelas sudah tersedia. Anda diarahkan ke halaman edit.');
            }
        }

        $mapelOptions = \App\Models\Mapel::where('status', 1)->orderBy('nama_mapel')->get();

        return view('admin.wali-kelas.form', compact('guru', 'user', 'mapelOptions'));
    }

    public function store(Request $request)
    {
        $rules = [
            'nama_lengkap'  => 'required|string|max:255',
            'nip'           => 'required|string|max:50|unique:guru,nip',
            'jenis_kelamin' => 'required|in:L,P',
            'mata_pelajaran'=> 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:50',
            'status'        => 'required|in:aktif,nonaktif',
            'user_id'       => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];

        if (! $request->filled('user_id')) {
            $rules['email']    = 'nullable|email|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $data = $request->validate($rules);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $user = null;

        DB::transaction(function () use ($data, $domainEmail, &$user) {
            if (! empty($data['user_id'])) {
                $user = User::find($data['user_id']);
                if (! $user || $user->guru) {
                    throw new \Exception('User tidak valid untuk profil wali kelas.');
                }
            } else {
                $email = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;
                $user  = User::create([
                    'name'     => $data['nama_lengkap'],
                    'username' => $data['nip'],
                    'email'    => $email,
                    'password' => Hash::make($data['password']),
                    'role'     => User::ROLE_WALI_KELAS,
                ]);
            }

            Guru::create([
                'user_id'        => $user->id,
                'nip'            => $data['nip'],
                'nama_lengkap'   => $data['nama_lengkap'],
                'jenis_kelamin'  => $data['jenis_kelamin'],
                'mata_pelajaran' => $data['mata_pelajaran'],
                'jabatan'        => $data['jabatan'] ?? null,
                'no_hp'          => $data['no_hp'] ?? null,
                'status'         => $data['status'],
                'qr_code'        => QrCodeGenerator::generate('GURU'),
            ]);
        });

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Wali kelas berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        if (! $guru->user || ! $guru->user->isRole(User::ROLE_WALI_KELAS)) {
            abort(403, 'Data yang diakses bukan merupakan wali kelas.');
        }

        $mapelOptions = \App\Models\Mapel::where('status', 1)->orderBy('nama_mapel')->get();

        return view('admin.wali-kelas.form', compact('guru', 'mapelOptions'));
    }

    public function update(Request $request, Guru $guru)
    {
        if (! $guru->user || ! $guru->user->isRole(User::ROLE_WALI_KELAS)) {
            abort(403, 'Data yang diperbarui bukan merupakan wali kelas.');
        }

        $data = $request->validate([
            'nama_lengkap'  => 'required|string|max:255',
            'nip'           => 'required|string|max:50|unique:guru,nip,' . $guru->id,
            'jenis_kelamin' => 'required|in:L,P',
            'mata_pelajaran'=> 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:50',
            'status'        => 'required|in:aktif,nonaktif',
            'email'         => 'nullable|email|unique:users,email,' . $guru->user_id,
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $domainEmail = Pengaturan::where('key', 'website_lembaga')->value('value') ?? 'madrasah.sch.id';
        $email       = $data['email'] ?? strtolower($data['nip']) . '@' . $domainEmail;

        DB::transaction(function () use ($data, $guru, $email) {
            $guru->update([
                'nama_lengkap'   => $data['nama_lengkap'],
                'nip'            => $data['nip'],
                'jenis_kelamin'  => $data['jenis_kelamin'],
                'mata_pelajaran' => $data['mata_pelajaran'],
                'jabatan'        => $data['jabatan'] ?? null,
                'no_hp'          => $data['no_hp'] ?? null,
                'status'         => $data['status'],
            ]);

            $guru->user->update([
                'name'     => $data['nama_lengkap'],
                'username' => $data['nip'],
                'email'    => $email,
            ]);

            if (! empty($data['password'])) {
                $guru->user->update(['password' => Hash::make($data['password'])]);
            }
        });

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Wali kelas berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        if (! $guru->user || ! $guru->user->isRole(User::ROLE_WALI_KELAS)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data yang dihapus bukan merupakan wali kelas.'], 403);
            }
            abort(403, 'Data yang dihapus bukan merupakan wali kelas.');
        }

        $guru->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Wali kelas berhasil dihapus.']);
        }

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Wali kelas berhasil dihapus.');
    }

    /**
     * Hapus user wali kelas yang belum memiliki profil guru.
     */
    public function destroyUser(User $user)
    {
        if (! $user->isRole(User::ROLE_WALI_KELAS)) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Aksi ini hanya diizinkan untuk akun wali kelas.'], 403);
            }
            abort(403, 'Aksi ini hanya diizinkan untuk akun wali kelas.');
        }

        if ($user->guru) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Gunakan tombol hapus pada profil untuk menghapus wali kelas yang sudah memiliki profil.'], 400);
            }
            return redirect()->route('admin.wali-kelas.index')
                ->with('error', 'Gunakan tombol hapus pada profil untuk menghapus wali kelas yang sudah memiliki profil.');
        }

        $user->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Akun wali kelas berhasil dihapus.']);
        }

        return redirect()->route('admin.wali-kelas.index')->with('success', 'Akun wali kelas berhasil dihapus.');
    }

    /**
     * Cetak kartu QR wali kelas (Semua) -> download PDF.
     */
    public function cetakQr(Request $request)
    {
        $guruList = Guru::whereHas('user', fn ($q) => $q->where('role', User::ROLE_WALI_KELAS))
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $template = \App\Models\IdCardTemplate::where('type', 'wali_kelas')->active()->first()
            ?? \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (! $template) {
            $qrImages    = $guruList->mapWithKeys(fn (Guru $g) => [
                $g->id => QrCodeGenerator::renderDataUri($g->qr_code ?? QrCodeGenerator::generate('GURU'), 160),
            ]);
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

            return \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'admin.guru.kartu-qr-pdf',
                compact('guruList', 'namaSekolah', 'qrImages')
            )->setPaper('a4', 'portrait')->download('kartu-qr-wali-kelas-semua.pdf');
        }

        $config   = $template->config;
        $entities = $guruList;

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.id-card-templates.pdf',
            compact('template', 'config', 'entities')
        )->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
         ->download('kartu-identitas-wali-kelas-semua.pdf');
    }

    /**
     * Generate & download kartu QR untuk satu wali kelas.
     */
    public function generateQrSatu(Guru $guru)
    {
        if (! $guru->qr_code) {
            $guru->update(['qr_code' => QrCodeGenerator::generate('GURU')]);
        }

        $template = \App\Models\IdCardTemplate::where('type', 'wali_kelas')->active()->first()
            ?? \App\Models\IdCardTemplate::where('type', 'guru')->active()->first();

        if (! $template) {
            $namaSekolah = \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
            $qrImage     = QrCodeGenerator::renderDataUri($guru->qr_code, 200);

            return \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'admin.guru.kartu-qr-satu-pdf',
                compact('guru', 'namaSekolah', 'qrImage')
            )->setPaper([0, 0, 226.77, 283.46])
             ->download("kartu-qr-{$guru->nip}.pdf");
        }

        $config   = $template->config;
        $entities = collect([$guru]);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'admin.id-card-templates.pdf',
            compact('template', 'config', 'entities')
        )->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
         ->download("kartu-identitas-{$guru->nip}.pdf");
    }
}
