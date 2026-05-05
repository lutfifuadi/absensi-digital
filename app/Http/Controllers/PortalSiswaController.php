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
            $qrImage = QrCodeGenerator::renderDataUri($siswa->qr_code, 200);

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
}
