<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Pengaturan;
use App\Support\QrCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalSiswaController extends Controller
{
    public function downloadKartu()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();
        
        $template = \App\Models\IdCardTemplate::where('type', 'siswa')->active()->first();

        if (!$template) {
            $namaSekolah = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';
            $qrImage = QrCodeGenerator::renderDataUri($siswa->qr_code ?? $siswa->nisn, 200);

            $pdf = Pdf::loadView('admin.siswa.kartu-qr-satu-pdf', compact(
                'siswa', 'namaSekolah', 'qrImage'
            ))->setPaper([0, 0, 226.77, 283.46]); // 8x10 cm

            return $pdf->download("kartu-pelajar-{$siswa->nisn}.pdf");
        }

        $config = $template->config;
        $entities = collect([$siswa]);

        return Pdf::loadView('admin.id-card-templates.pdf', compact('template', 'config', 'entities'))
                  ->setPaper([0, 0, $config['canvas']['width'], $config['canvas']['height']])
                  ->download("kartu-pelajar-{$siswa->nisn}.pdf");
    }

    public function downloadKartuPelepasan()
    {
        $user = Auth::user();
        if ($user->role !== 'siswa') {
            abort(403, 'Akses ditolak.');
        }

        $siswa = Siswa::with('kelas')->where('user_id', $user->id)->firstOrFail();

        // Cek apakah siswa kelas XII
        $tingkat = $siswa->kelas ? trim($siswa->kelas->tingkat) : '';
        if (!$siswa->kelas || !in_array($tingkat, ['XII', '12'])) {
            return back()->with('error', 'Fitur ini hanya tersedia untuk siswa kelas XII.');
        }

        // Data untuk view kartu pelepasan (format gambar PNG)
        $namaSekolah  = Pengaturan::where('key', 'nama_sekolah')->value('value') ?? 'Madrasah Aliyah';

        // Logo sekolah - konversi ke base64 data URI biar html2canvas gak kena CORS
        $logoPath = Pengaturan::where('key', 'logo_sekolah')->value('value');
        $logoSekolah = null;
        if ($logoPath) {
            $fullPath = public_path('storage/' . $logoPath);
            if (file_exists($fullPath)) {
                $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                $mime = match($ext) {
                    'png' => 'image/png',
                    'jpg', 'jpeg' => 'image/jpeg',
                    'svg' => 'image/svg+xml',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                $imageData = file_get_contents($fullPath);
                if ($imageData !== false) {
                    $logoSekolah = 'data:' . $mime . ';base64,' . base64_encode($imageData);
                }
            }
        }

        // QR Code
        $qrCodeData = $siswa->qr_code ?: $siswa->nisn;
        $qrImage = QrCodeGenerator::renderDataUri($qrCodeData, 300);

        // Tahun akademik
        $tahunAkademik = \App\Models\TahunAkademik::where('is_aktif', true)->value('nama')
            ?? (date('Y') . '/' . (date('Y') + 1));

        return view('siswa.kartu-pelepasan', compact(
            'siswa', 'namaSekolah', 'logoSekolah', 'qrImage', 'tahunAkademik'
        ));
    }
}
