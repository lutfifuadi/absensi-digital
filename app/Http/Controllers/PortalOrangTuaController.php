<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\IzinSakit;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PortalOrangTuaController extends Controller
{
    /**
     * Detail Profil Anak.
     */
    public function profilAnak($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anak = Siswa::with(['kelas.waliKelas', 'tahunAkademik'])
            ->where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        return view('portal-ortu.profil-anak', compact('anak'));
    }

    /**
     * Riwayat Absensi Anak.
     */
    public function absensiAnak(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anak = Siswa::where('id', $id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $absensi = AbsensiSiswa::where('siswa_id', $anak->id)
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('portal-ortu.absensi-anak', compact('anak', 'absensi', 'month', 'year'));
    }

    /**
     * Daftar Izin/Sakit Anak.
     */
    public function izinSakit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil semua anak yang terhubung dengan ortu ini
        $anakIds = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->pluck('id');

        $izinSakit = IzinSakit::with('siswa.kelas')
            ->whereIn('reference_id', $anakIds)
            ->where('tipe', 'siswa')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal-ortu.izin-sakit-index', compact('izinSakit'));
    }

    /**
     * Form Ajukan Izin/Sakit Anak.
     */
    public function izinSakitCreate()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $anakList = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->get();

        // 1. Hubungkan formulir pengajuan izin dengan data anak aktif yang sedang dipilih.
        $activeSiswaId = session('active_siswa_id');
        if (!$activeSiswaId && $anakList->isNotEmpty()) {
            $activeSiswaId = $anakList->first()->id;
            session(['active_siswa_id' => $activeSiswaId]);
        }

        return view('portal-ortu.izin-sakit-create', compact('anakList', 'activeSiswaId'));
    }

    /**
     * Simpan Pengajuan Izin/Sakit Anak.
     */
    public function izinSakitStore(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jenis' => 'required|in:sakit,izin',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string|max:500',
            'lampiran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // 2MB max
        ]);

        $siswa = Siswa::where('id', $request->siswa_id)
            ->where(function($query) use ($user) {
                $query->where('ortu_user_id', $user->id)
                      ->orWhereHas('ortu', function($q) use ($user) {
                          $q->where('users.id', $user->id);
                      });
            })
            ->firstOrFail();

        $data = [
            'tipe' => 'siswa',
            'reference_id' => $siswa->id,
            'user_id' => $siswa->user_id,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
        ];

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('izin-lampiran', 'public');
        }

        $izin = IzinSakit::create($data);

        // Notify all admin & super_admin users
        $admins = \App\Models\User::whereIn('role', ['super_admin', 'admin_sekolah'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\IzinDiajukanNotification($izin));
        }

        return redirect()->route('ortu.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dikirim dan menunggu persetujuan.');
    }

    /**
     * Batalkan/Hapus Pengajuan Izin/Sakit Anak.
     */
    public function izinSakitDestroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Ambil semua anak yang terhubung dengan ortu ini
        $anakIds = Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->pluck('id');

        $izinSakit = IzinSakit::where('id', $id)
            ->where('tipe', 'siswa')
            ->whereIn('reference_id', $anakIds)
            ->firstOrFail();

        if ($izinSakit->status !== 'pending') {
            return redirect()->route('ortu.izin-sakit.index')
                ->with('error', 'Pengajuan tidak dapat dibatalkan karena sudah diproses (disetujui/ditolak).');
        }

        // Jika ada lampiran, hapus file lampiran
        if ($izinSakit->lampiran) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($izinSakit->lampiran);
        }

        $izinSakit->delete();

        return redirect()->route('ortu.izin-sakit.index')
            ->with('success', 'Pengajuan izin/sakit berhasil dibatalkan.');
    }

    /**
     * Halaman Pengaturan Profil & Ganti Password.
     */
    public function pengaturan()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return view('portal-ortu.pengaturan', compact('user'));
    }

    /**
     * Update Data Diri Orang Tua.
     */
    public function updateProfil(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'hubungan' => 'required|string|max:100',
            'no_hp' => 'required|string|max:20|regex:/^[0-9\+\-\s]+$/',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'alamat' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'hubungan.required' => 'Hubungan wajib diisi.',
            'no_hp.required' => 'Nomor WhatsApp/Telepon wajib diisi.',
            'no_hp.regex' => 'Format nomor WhatsApp/Telepon tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
        ]);

        $user->update([
            'name' => $request->name,
            'hubungan' => $request->hubungan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat' => $request->alamat,
        ]);

        // Sinkronisasi nomor WhatsApp ke field no_hp_ortu di tabel siswa agar notifikasi WA terkirim ke nomor baru
        Siswa::where(function($query) use ($user) {
            $query->where('ortu_user_id', $user->id)
                  ->orWhereHas('ortu', function($q) use ($user) {
                      $q->where('users.id', $user->id);
                  });
        })->update(['no_hp_ortu' => $request->no_hp]);

        return redirect()->route('ortu.pengaturan')
            ->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Ganti Password Orang Tua.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'password_lama' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Password lama tidak cocok.');
                    }
                },
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Za-z]/',      // Harus ada huruf
                'regex:/[0-9]/',      // Harus ada angka
            ],
        ], [
            'password_lama.required' => 'Password lama wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.regex' => 'Password baru harus berupa kombinasi huruf dan angka.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('ortu.pengaturan')
            ->with([
                'success' => 'Password Anda berhasil diperbarui.',
                'password_success' => true
            ]);
    }
}
