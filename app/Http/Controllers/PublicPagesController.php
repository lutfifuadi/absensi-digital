<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PublicPagesController extends Controller
{
    private function getInstitusiInfo(): array
    {
        return [
            'nama_lembaga'      => setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Sekolah',
            'nama_yayasan'      => setting('nama_yayasan_dinas', ''),
            'website_lembaga'   => setting('website_lembaga', ''),
            'alamat_lembaga'    => setting('alamat_lembaga', ''),
            'kontak_lembaga'    => setting('no_telp_lembaga') ?: setting('kontak_lembaga', ''),
            'email_lembaga'     => setting('email_lembaga', ''),
            'kepala_lembaga'    => setting('nama_kepala_lembaga', ''),
            'logo_sekolah'      => setting('logo_sekolah', ''),
            'status_akreditasi' => setting('status_akreditasi', ''),
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
        $namaSekolah = setting('nama_lembaga') ?: setting('nama_sekolah') ?: 'Madrasah Aliyah';
        $logoUrl = null;
        $logoLocal = setting('logo_sekolah');
        if ($logoLocal) {
            $logoUrl = (filter_var($logoLocal, FILTER_VALIDATE_URL) || str_starts_with($logoLocal, 'http://') || str_starts_with($logoLocal, 'https://'))
                ? $logoLocal
                : asset('uploads/logo/' . $logoLocal);
        } elseif (setting('logo_url')) {
            $logoUrl = setting('logo_url');
        }
        return view('public.prestasi', compact('namaSekolah', 'logoUrl'));
    }
}
