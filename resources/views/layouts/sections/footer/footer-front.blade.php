@php
$namaSekolah = $namaSekolah ?? \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
  ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
  ?? config('variables.templateName');
$logoSekolah = $logoSekolah ?? \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
@endphp

<!-- Footer: Start -->
<footer class="landing-footer footer-text py-5" style="background: #0f172a !important; border-top: 1px solid rgba(255,255,255,0.1); color: #94a3b8 !important;">
  <div class="footer-top position-relative overflow-hidden z-1">
    <div class="container">
      <div class="row gx-0 gy-6 g-lg-10">
        <div class="col-lg-5">
          <a href="{{url('/')}}" class="app-brand-link mb-4 text-decoration-none">
            <div class="avatar avatar-sm me-2 bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
              <i class="ti tabler-school text-white fs-5"></i>
            </div>
            <span class="app-brand-text demo text-white fw-bold" style="font-size: 1.5rem; letter-spacing: -0.5px;">{{ $namaSekolah }}</span>
          </a>
          <p class="footer-text mb-6 mt-2 opacity-75" style="max-width: 400px; line-height: 1.6;">Solusi administrasi digital terintegrasi untuk <strong>{{ $namaSekolah }}</strong> yang modern, akuntabel, dan transparan dalam pengelolaan kehadiran.</p>
          @php
            $websiteLembaga = \App\Models\Pengaturan::where('key', 'website_lembaga')->value('value');
          @endphp
          @if($websiteLembaga)
            <div class="d-flex align-items-center gap-3 mt-4">
              <a href="{{ $websiteLembaga }}" target="_blank" class="footer-social-link" title="Website Resmi"><i class="ti tabler-world fs-4"></i></a>
            </div>
          @endif
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title text-white fw-bold mb-4">Layanan</h6>
          <ul class="list-unstyled">
             <li class="mb-3"><a href="#fitur" class="footer-link-custom">Fitur Utama</a></li>
             <li class="mb-3"><a href="{{ route('login') }}" class="footer-link-custom">Portal Admin</a></li>
             <li class="mb-3"><a href="{{ route('public.live-board') }}" class="footer-link-custom">Live Monitor</a></li>
             <li class="mb-3"><a href="{{ route('public.scan-qr.index') }}" class="footer-link-custom">Scan QR Absensi</a></li>
             <li class="mb-3"><a href="{{ route('pengaduan.form') }}" class="footer-link-custom">Pengaduan Data</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
          <h6 class="footer-title text-white fw-bold mb-4">Informasi</h6>
          <ul class="list-unstyled">
            <li class="mb-3"><a href="javascript:void(0);" class="footer-link-custom">Tentang Kami</a></li>
            <li class="mb-3"><a href="javascript:void(0);" class="footer-link-custom">Panduan Pengguna</a></li>
            <li class="mb-3"><a href="javascript:void(0);" class="footer-link-custom">Kebijakan Privasi</a></li>
            <li class="mb-3"><a href="javascript:void(0);" class="footer-link-custom">Bantuan</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4">
          <h6 class="footer-title text-white fw-bold mb-4">Hubungi Kami</h6>
          <div class="d-flex mb-3">
             <i class="ti tabler-map-pin me-3 text-primary"></i>
             <span class="small">{{ \App\Models\Pengaturan::where('key', 'alamat_lembaga')->value('value') ?? 'Jl. Pendidikan No. 123, Komplek Madrasah Terpadu, Indonesia' }}</span>
          </div>
          <div class="d-flex mb-3">
             <i class="ti tabler-phone me-3 text-primary"></i>
             <span class="small">{{ \App\Models\Pengaturan::where('key', 'no_telp_lembaga')->value('value') ?? '+62 123 4567 890' }}</span>
          </div>
          <div class="d-flex">
             <i class="ti tabler-mail me-3 text-primary"></i>
             <span class="small">{{ \App\Models\Pengaturan::where('key', 'email_lembaga')->value('value') ?? 'info@madrasah.sch.id' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="container">
    <hr class="my-5" style="border-color: rgba(255,255,255,0.05);">
  </div>

  <div class="footer-bottom" style="background: transparent !important;">
    <div class="container d-flex flex-wrap justify-content-between align-items-center flex-md-row flex-column text-center text-md-start">
      <div class="mb-2 mb-md-0">
        <span class="text-white-50 small">© {{ date('Y') }} <strong>{{ $namaSekolah }}</strong>. Digagas untuk Masa Depan <strong>{{ $namaSekolah }}</strong>.</span>
      </div>
      <div class="d-flex align-items-center gap-4 mt-2 mt-md-0">
        <a href="javascript:void(0);" class="text-white-50 small text-decoration-none hover-white">Term of Services</a>
        <a href="javascript:void(0);" class="text-white-50 small text-decoration-none hover-white">Cookies</a>
      </div>
    </div>
  </div>
</footer>
<!-- Footer: End -->

<style>
  .footer-link-custom {
    color: #94a3b8 !important;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.9rem;
  }
  .footer-link-custom:hover {
    color: #7367F0 !important;
    padding-left: 5px;
  }
  .footer-social-link {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.03);
    color: #94a3b8;
    transition: all 0.3s ease;
    text-decoration: none;
  }
  .footer-social-link:hover {
    background: #7367F0;
    color: #fff;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(115, 103, 240, 0.3);
  }
  .hover-white:hover {
    color: #fff !important;
  }
</style>
