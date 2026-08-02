<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\IzinPulangCepat;
use App\Models\Siswa;
use App\Models\StaffTataUsaha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class IzinPulangCepatService
{
    /**
     * Memverifikasi izin pulang cepat berdasarkan kode_izin / NIS / NIP / NIK.
     */
    public function verifyPermission(string $query): array
    {
        $input = trim($query);

        // 1. Cari berdasarkan kode_izin langsung
        $izin = IzinPulangCepat::with(['user', 'approver', 'satpam'])
            ->where('kode_izin', $input)
            ->latest()
            ->first();

        // 2. Jika tidak ditemukan, cari berdasarkan NIS (siswa)
        if (!$izin) {
            $siswa = Siswa::where('nis', $input)->orWhere('nisn', $input)->first();
            if ($siswa) {
                $izin = IzinPulangCepat::with(['user', 'approver', 'satpam'])
                    ->where('kategori', 'siswa')
                    ->where('reference_id', $siswa->id)
                    ->latest()
                    ->first();
            }
        }

        // 3. Jika tidak ditemukan, cari berdasarkan NIP/NIK (guru)
        if (!$izin) {
            $guru = Guru::where('nip', $input)->orWhere('nik', $input)->first();
            if ($guru) {
                $izin = IzinPulangCepat::with(['user', 'approver', 'satpam'])
                    ->where('kategori', 'guru')
                    ->where('reference_id', $guru->id)
                    ->latest()
                    ->first();
            }
        }

        // 4. Jika tidak ditemukan, cari berdasarkan NIP/NIK (staff)
        if (!$izin) {
            $staff = StaffTataUsaha::where('nip', $input)->orWhere('nik', $input)->first();
            if ($staff) {
                $izin = IzinPulangCepat::with(['user', 'approver', 'satpam'])
                    ->where('kategori', 'staff')
                    ->where('reference_id', $staff->id)
                    ->latest()
                    ->first();
            }
        }

        if (!$izin) {
            return [
                'success' => false,
                'message' => 'Data izin pulang cepat tidak ditemukan.',
                'data' => null,
            ];
        }

        $ref = $izin->reference;
        $namaPengaju = $ref->nama_lengkap ?? $ref->nama ?? $izin->user->name ?? '-';

        $identitas = '-';
        $subText = '-';
        $fotoUrl = asset('assets/img/avatars/1.png');

        if ($izin->kategori === 'siswa' && $ref) {
            $identitas = 'NIS: ' . ($ref->nis ?? '-');
            $subText = 'Kelas: ' . ($ref->kelas->nama_kelas ?? '-');
            if (!empty($ref->foto)) {
                $fotoUrl = asset('storage/' . $ref->foto);
            }
        } elseif ($izin->kategori === 'guru' && $ref) {
            $identitas = 'NIP: ' . ($ref->nip ?? '-');
            $subText = 'Jabatan: ' . ($ref->jabatan ?? 'Guru');
            if (!empty($ref->foto)) {
                $fotoUrl = asset('storage/' . $ref->foto);
            }
        } elseif ($izin->kategori === 'staff' && $ref) {
            $identitas = 'NIP: ' . ($ref->nip ?? '-');
            $subText = 'Jabatan: ' . ($ref->jabatan ?? 'Staff TU');
            if (!empty($ref->foto)) {
                $fotoUrl = asset('storage/' . $ref->foto);
            }
        }

        $today = Carbon::today()->toDateString();
        $tglIzin = $izin->tanggal ? $izin->tanggal->format('Y-m-d') : null;

        $isValid = ($izin->status === 'approved' && $tglIzin === $today);

        $statusLabel = 'TIDAK VALID / EXPIRED';
        $statusMessage = 'Izin tidak dapat digunakan.';

        if ($izin->status === 'completed') {
            $statusLabel = 'SUDAH REKLAMASI / COMPLETED';
            $statusMessage = 'Izin ini sudah diverifikasi sebelumnya pada ' . ($izin->diverifikasi_satpam_pada ? $izin->diverifikasi_satpam_pada->format('d/m/Y H:i') : '-');
        } elseif ($izin->status === 'approved' && $tglIzin === $today) {
            $statusLabel = 'IZIN VALID - SILAKAN KELUAR';
            $statusMessage = 'Izin disetujui untuk hari ini.';
        } elseif ($izin->status === 'approved' && $tglIzin !== $today) {
            $statusLabel = 'IZIN BUKAN HARI INI';
            $statusMessage = 'Izin ini disetujui untuk tanggal ' . ($izin->tanggal ? $izin->tanggal->format('d/m/Y') : '-');
        } elseif ($izin->status === 'pending') {
            $statusLabel = 'BELUM DISETUJUI (PENDING)';
            $statusMessage = 'Pengajuan izin belum mendapat persetujuan pimpinan/walikelas.';
        } elseif ($izin->status === 'rejected') {
            $statusLabel = 'IZIN DITOLAK';
            $statusMessage = 'Pengajuan izin telah ditolak.';
        }

        return [
            'success' => true,
            'is_valid' => $isValid,
            'status_label' => $statusLabel,
            'status_message' => $statusMessage,
            'data' => [
                'id' => $izin->id,
                'kode_izin' => $izin->kode_izin,
                'kategori' => strtoupper($izin->kategori),
                'nama' => $namaPengaju,
                'identitas' => $identitas,
                'sub_text' => $subText,
                'foto_url' => $fotoUrl,
                'tanggal' => $izin->tanggal ? $izin->tanggal->format('d/m/Y') : '-',
                'jam_rencana_keluar' => $izin->jam_rencana_keluar ?? '-',
                'jam_realisasi_keluar' => $izin->jam_realisasi_keluar ?? '-',
                'jenis_alasan' => strtoupper(str_replace('_', ' ', $izin->jenis_alasan ?? '-')),
                'alasan' => $izin->alasan ?? '-',
                'nama_penjemput' => $izin->nama_penjemput ?? '-',
                'no_hp_penjemput' => $izin->no_hp_penjemput ?? '-',
                'status' => $izin->status,
                'disetujui_oleh' => $izin->approver->name ?? '-',
                'diverifikasi_satpam_oleh' => $izin->satpam->name ?? '-',
                'diverifikasi_satpam_pada' => $izin->diverifikasi_satpam_pada ? $izin->diverifikasi_satpam_pada->format('d/m/Y H:i') : null,
            ]
        ];
    }

    /**
     * Konfirmasi checkout gerbang satpam.
     */
    public function processCheckout(IzinPulangCepat $izin): array
    {
        if ($izin->status === 'completed') {
            return [
                'success' => false,
                'message' => 'Izin ini sudah diselesaikan sebelumnya.',
            ];
        }

        $now = now();
        $jamRealisasi = $now->format('H:i:s');

        $izin->update([
            'status' => 'completed',
            'diverifikasi_satpam_oleh' => Auth::id(),
            'diverifikasi_satpam_pada' => $now,
            'jam_realisasi_keluar' => $jamRealisasi,
        ]);

        try {
            event('izin_pulang_cepat.completed', [$izin]);
        } catch (\Throwable $e) {
            // Silence if no event handler registered yet
        }

        return [
            'success' => true,
            'message' => 'Berhasil! Gerbang dibuka dan status keluar dicatat.',
            'data' => [
                'kode_izin' => $izin->kode_izin,
                'jam_realisasi_keluar' => $jamRealisasi,
                'diverifikasi_pada' => $now->format('d/m/Y H:i:s'),
            ]
        ];
    }
}
