@extends('layouts/layoutFront')

@section('title', 'Bantuan — ' . ($info['nama_lembaga'] ?? 'Sistem Absensi'))

@section('content')
<style>
.pub-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
  padding: 5rem 0 3rem;
}
.help-card {
  background: #fff;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
  height: 100%;
  text-align: center;
  transition: transform .2s, box-shadow .2s;
}
.help-card:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,.12); }
.help-card .icon {
  width: 64px; height: 64px;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem;
  margin: 0 auto 1rem;
}
.faq-item { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; margin-bottom: .75rem; }
.faq-item .faq-q { padding: 1rem 1.25rem; font-weight: 600; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; transition: background .2s; }
.faq-item .faq-q:hover { background: #eef2ff; }
.faq-item .faq-a { padding: 1rem 1.25rem; display: none; color: #6b7280; line-height: 1.7; }
.faq-item.open .faq-a { display: block; }
.faq-item.open .faq-q { background: #eef2ff; color: #4f46e5; }
</style>

<section class="pub-hero text-white text-center">
  <div class="container">
    <div class="badge bg-primary bg-opacity-25 text-primary-emphasis px-3 py-2 mb-3 fs-6">
      <i class="ti tabler-help-circle me-1"></i> Bantuan
    </div>
    <h1 class="fw-bold display-5 mb-3">Pusat Bantuan</h1>
    <p class="fs-5 text-white-50">Temukan jawaban dan hubungi kami untuk bantuan lebih lanjut</p>
  </div>
</section>

{{-- Quick Help Cards --}}
<section style="background:#f8fafc;padding:3rem 0;">
  <div class="container">
    <div class="row g-4 mb-2">
      <div class="col-md-4">
        <div class="help-card">
          <div class="icon" style="background:#eef2ff;color:#696cff;">
            <i class="ti tabler-book"></i>
          </div>
          <h5 class="fw-bold mb-2">Panduan Pengguna</h5>
          <p class="text-muted small mb-3">Baca panduan lengkap penggunaan sistem untuk setiap peran</p>
          <a href="{{ route('public.panduan-pengguna') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
            Baca Panduan
          </a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="help-card">
          <div class="icon" style="background:#ecfdf5;color:#059669;">
            <i class="ti tabler-brand-whatsapp"></i>
          </div>
          <h5 class="fw-bold mb-2">Hubungi via WhatsApp</h5>
          <p class="text-muted small mb-3">Chat langsung dengan admin sekolah untuk bantuan cepat</p>
          @if($info['kontak_lembaga'])
          <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $info['kontak_lembaga']) }}"
             target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-4">
            Chat Sekarang
          </a>
          @else
          <span class="btn btn-outline-secondary btn-sm rounded-pill px-4 disabled">Kontak Belum Tersedia</span>
          @endif
        </div>
      </div>
      <div class="col-md-4">
        <div class="help-card">
          <div class="icon" style="background:#fff7ed;color:#ea580c;">
            <i class="ti tabler-mail"></i>
          </div>
          <h5 class="fw-bold mb-2">Kirim Email</h5>
          <p class="text-muted small mb-3">Kirim pertanyaan atau laporan masalah via email</p>
          @if($info['email_lembaga'])
          <a href="mailto:{{ $info['email_lembaga'] }}" class="btn btn-outline-warning btn-sm rounded-pill px-4">
            Kirim Email
          </a>
          @else
          <span class="btn btn-outline-secondary btn-sm rounded-pill px-4 disabled">Email Belum Tersedia</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Masalah Umum --}}
<section style="padding:4rem 0;">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-6">
        <h3 class="fw-bold mb-4">Masalah Umum & Solusi</h3>
        @php $issues = [
          [
            'q' => 'Tidak bisa login ke sistem',
            'a' => 'Pastikan username dan password benar. Jika lupa password, hubungi admin untuk reset. Pastikan akun Anda belum dinonaktifkan oleh admin.'
          ],
          [
            'q' => 'QR Code tidak terbaca saat scan',
            'a' => 'Pastikan QR Code tidak rusak atau buram. Coba cetak ulang QR Code dari portal. Jika kamera bermasalah, hubungi petugas piket.'
          ],
          [
            'q' => 'Notifikasi WA tidak masuk ke orang tua',
            'a' => 'Verifikasi nomor WA orang tua di data siswa sudah benar (format 628xxx). Pastikan nomor aktif di WhatsApp. Admin bisa cek status validasi nomor di halaman WA Gateway.'
          ],
          [
            'q' => 'Absensi mandiri tidak bisa dilakukan',
            'a' => 'Pastikan GPS/lokasi aktif di perangkat Anda. Jika fitur lokasi diaktifkan, Anda harus berada dalam radius yang ditentukan sekolah. Cek koneksi internet Anda.'
          ],
          [
            'q' => 'Data absensi tidak muncul di laporan',
            'a' => 'Pastikan tahun akademik yang aktif sudah benar di pengaturan. Refresh halaman laporan dan filter tanggal yang sesuai.'
          ],
          [
            'q' => 'Halaman error atau loading lama',
            'a' => 'Clear cache browser Anda (Ctrl+Shift+Del). Coba gunakan browser lain (Chrome/Firefox versi terbaru). Jika masalah berlanjut, hubungi admin sistem.'
          ],
        ]; @endphp
        @foreach($issues as $issue)
        <div class="faq-item">
          <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
            <span>{{ $issue['q'] }}</span>
            <i class="ti tabler-chevron-down"></i>
          </div>
          <div class="faq-a">{{ $issue['a'] }}</div>
        </div>
        @endforeach
      </div>
      <div class="col-lg-6">
        <h3 class="fw-bold mb-4">Informasi Kontak</h3>
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
          <div class="card-body p-4">
            <h5 class="fw-semibold mb-3">{{ $info['nama_lembaga'] }}</h5>
            <ul class="list-unstyled">
              @if($info['alamat_lembaga'])
              <li class="d-flex gap-3 mb-3">
                <div style="width:40px;height:40px;background:#eef2ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                  <i class="ti tabler-map-pin text-primary"></i>
                </div>
                <div><div class="fw-semibold small">Alamat</div><div class="text-muted">{{ $info['alamat_lembaga'] }}</div></div>
              </li>
              @endif
              @if($info['kontak_lembaga'])
              <li class="d-flex gap-3 mb-3">
                <div style="width:40px;height:40px;background:#ecfdf5;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                  <i class="ti tabler-phone text-success"></i>
                </div>
                <div>
                  <div class="fw-semibold small">Telepon</div>
                  <div class="text-muted">{{ $info['kontak_lembaga'] }}</div>
                </div>
              </li>
              @endif
              @if($info['email_lembaga'])
              <li class="d-flex gap-3 mb-3">
                <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                  <i class="ti tabler-mail text-warning"></i>
                </div>
                <div>
                  <div class="fw-semibold small">Email</div>
                  <a href="mailto:{{ $info['email_lembaga'] }}" class="text-muted">{{ $info['email_lembaga'] }}</a>
                </div>
              </li>
              @endif
              @if($info['website_lembaga'])
              <li class="d-flex gap-3 mb-3">
                <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                  <i class="ti tabler-world text-info"></i>
                </div>
                <div>
                  <div class="fw-semibold small">Website</div>
                  <a href="https://{{ $info['website_lembaga'] }}" target="_blank" class="text-muted">{{ $info['website_lembaga'] }}</a>
                </div>
              </li>
              @endif
            </ul>

            <hr>
            <div class="d-flex flex-column gap-2">
              <a href="{{ route('public.tentang-kami') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti tabler-school me-2"></i>Tentang Kami
              </a>
              <a href="{{ route('public.panduan-pengguna') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti tabler-book me-2"></i>Baca Panduan Lengkap
              </a>
              <a href="{{ route('public.kebijakan-privasi') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti tabler-shield-check me-2"></i>Kebijakan Privasi
              </a>
            </div>
          </div>
        </div>

        <div class="card mt-3 border-0" style="background:linear-gradient(135deg,#696cff,#5e60ce);border-radius:16px;">
          <div class="card-body p-4 text-white">
            <h5 class="fw-bold mb-2"><i class="ti tabler-login me-2"></i>Sudah punya akun?</h5>
            <p class="small opacity-90 mb-3">Masuk ke sistem untuk mengelola absensi dan melihat laporan</p>
            <a href="{{ route('login') }}" class="btn btn-light btn-sm px-4 rounded-pill fw-semibold">
              Masuk Sekarang
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
