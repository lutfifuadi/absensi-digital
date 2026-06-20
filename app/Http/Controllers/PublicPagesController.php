<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PublicPagesController extends Controller
{
    private function getInstitusiInfo(): array
    {
        return [
            'nama_lembaga'      => Pengaturan::where('key', 'nama_lembaga')->value('value') ?? 'Sekolah',
            'nama_yayasan'      => Pengaturan::where('key', 'nama_yayasan_dinas')->value('value') ?? '',
            'website_lembaga'   => Pengaturan::where('key', 'website_lembaga')->value('value') ?? '',
            'alamat_lembaga'    => Pengaturan::where('key', 'alamat_lembaga')->value('value') ?? '',
            'kontak_lembaga'    => Pengaturan::where('key', 'kontak_lembaga')->value('value') ?? '',
            'email_lembaga'     => Pengaturan::where('key', 'email_lembaga')->value('value') ?? '',
            'kepala_lembaga'    => Pengaturan::where('key', 'nama_kepala_lembaga')->value('value') ?? '',
            'logo_sekolah'      => Pengaturan::where('key', 'logo_sekolah')->value('value') ?? '',
            'status_akreditasi' => Pengaturan::where('key', 'status_akreditasi')->value('value') ?? '',
        ];
    }

    public function tentangKami()
    {
        $info = $this->getInstitusiInfo();
        return view('public.tentang-kami', compact('info'));
    }

    public function panduanPengguna()
    {
        $info = $this->getInstitusiInfo();
        return view('public.panduan-pengguna', compact('info'));
    }

    public function kebijakanPrivasi()
    {
        $info = $this->getInstitusiInfo();
        return view('public.kebijakan-privasi', compact('info'));
    }

    public function bantuan()
    {
        $info = $this->getInstitusiInfo();
        return view('public.bantuan', compact('info'));
    }

    public function prestasi()
    {
        $namaSekolah = Pengaturan::where('key', 'nama_lembaga')->value('value')
            ?? Pengaturan::where('key', 'nama_sekolah')->value('value')
            ?? 'Madrasah Aliyah';
        $logoUrl = Pengaturan::where('key', 'logo_url')->value('value');
        if (!$logoUrl) {
            $logoLocal = Pengaturan::where('key', 'logo_sekolah')->value('value');
            if ($logoLocal) $logoUrl = asset('uploads/logo/' . $logoLocal);
        }
        return view('public.prestasi', compact('namaSekolah', 'logoUrl'));
    }
}
