@extends('layouts/layoutFront')

@section('title', 'Panduan Pengguna — ' . ($info['nama_lembaga'] ?? 'Sistem Absensi'))

@section('content')
<style>
.pub-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
  padding: 5rem 0 3rem;
  position: relative;
}
.guide-card {
  background: #fff;
  border-radius: 16px;
  padding: 1.75rem;
  box-shadow: 0 2px 16px rgba(0,0,0,.07);
  height: 100%;
  border-left: 4px solid #696cff;
}
.step-badge {
  width: 36px; height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #696cff, #9397ff);
  color: #fff;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.9rem;
  flex-shrink: 0;
}
.role-tab { cursor: pointer; transition: all .2s; }
.role-tab.active { background: #696cff !important; color: #fff !important; }
.faq-item {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 0.75rem;
}
.faq-item .faq-q {
  padding: 1rem 1.25rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f9fafb;
  transition: background .2s;
}
.faq-item .faq-q:hover { background: #eef2ff; }
.faq-item .faq-a { padding: 1rem 1.25rem; display: none; color: #6b7280; }
.faq-item.open .faq-a { display: block; }
.faq-item.open .faq-q { background: #eef2ff; }
</style>

<section class="pub-hero text-white text-center">
  <div class="container">
    <div class="badge bg-primary bg-opacity-25 text-primary-emphasis px-3 py-2 mb-3 fs-6">
      <i class="ti tabler-book me-1"></i> Panduan Pengguna
    </div>
    <h1 class="fw-bold display-5 mb-3">Cara Menggunakan Sistem</h1>
    <p class="fs-5 text-white-50">Panduan lengkap untuk semua pengguna sistem presensi digital</p>
  </div>
</section>

{{-- Role Selector --}}
<section style="background:#f8fafc;padding:3rem 0;">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="fw-bold">Pilih Peran Anda</h2>
      <p class="text-muted">Panduan disesuaikan berdasarkan peran pengguna</p>
    </div>
    <div class="d-flex gap-2 justify-content-center flex-wrap mb-5" id="roleTabs">
      @php
      $roles = [
        'admin'   => ['icon' => 'tabler-shield-cog',     'label' => 'Admin/Operator'],
        'guru'    => ['icon' => 'tabler-chalkboard',     'label' => 'Guru'],
        'siswa'   => ['icon' => 'tabler-user-graduate',  'label' => 'Siswa'],
        'ortu'    => ['icon' => 'tabler-users',           'label' => 'Orang Tua'],
        'piket'   => ['icon' => 'tabler-qrcode-scan',    'label' => 'Petugas Piket'],
      ];
      @endphp
      @foreach($roles as $key => $role)
      <button class="btn {{ $key === 'admin' ? 'btn-primary' : 'btn-outline-secondary' }} role-tab px-4 py-2 rounded-pill"
              onclick="showRole('{{ $key }}', this)">
        <i class="ti {{ $role['icon'] }} me-2"></i>{{ $role['label'] }}
      </button>
      @endforeach
    </div>

    {{-- Admin Guide --}}
    <div id="guide-admin" class="role-guide">
      <div class="row g-4">
        @php $adminSteps = [
          ['title' => 'Login ke Sistem', 'desc' => 'Buka halaman login dan masukkan username serta password yang telah diberikan oleh administrator.', 'icon' => 'tabler-login'],
          ['title' => 'Kelola Data Siswa', 'desc' => 'Tambah, edit, atau impor data siswa melalui menu Admin → Siswa. Pastikan nomor WA orang tua terisi dengan benar.', 'icon' => 'tabler-users'],
          ['title' => 'Atur Tahun Akademik', 'desc' => 'Buat tahun akademik aktif dan atur kelas melalui menu Tahun Akademik sebelum memulai absensi.', 'icon' => 'tabler-calendar'],
          ['title' => 'Pengaturan Notifikasi', 'desc' => 'Konfigurasi WA Gateway di menu Admin → WA Gateway untuk mengaktifkan notifikasi otomatis ke orang tua.', 'icon' => 'tabler-bell'],
          ['title' => 'Monitor Absensi', 'desc' => 'Pantau kehadiran real-time melalui Live Monitor dan unduh laporan dari menu Laporan.', 'icon' => 'tabler-chart-bar'],
          ['title' => 'Cetak QR Code', 'desc' => 'Generate dan cetak QR Code untuk siswa, guru, dan staff dari masing-masing halaman data.', 'icon' => 'tabler-qrcode'],
        ]; @endphp
        @foreach($adminSteps as $i => $step)
        <div class="col-md-6 col-lg-4">
          <div class="guide-card">
            <div class="d-flex gap-3 align-items-start">
              <div class="step-badge">{{ $i + 1 }}</div>
              <div>
                <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                <div class="text-muted small">{{ $step['desc'] }}</div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Guru Guide --}}
    <div id="guide-guru" class="role-guide d-none">
      <div class="row g-4">
        @php $guruSteps = [
          ['title' => 'Login Guru', 'desc' => 'Masuk dengan akun guru yang telah dibuat oleh admin sekolah.', 'icon' => 'tabler-login'],
          ['title' => 'Lihat Jadwal', 'desc' => 'Cek jadwal pelajaran Anda di menu Jadwal Pelajaran.', 'icon' => 'tabler-calendar'],
          ['title' => 'Absensi Mandiri', 'desc' => 'Lakukan absensi mandiri melalui QR Code atau absensi manual dari halaman absensi guru.', 'icon' => 'tabler-qrcode-scan'],
          ['title' => 'Izin/Sakit', 'desc' => 'Ajukan izin atau sakit melalui menu Izin & Sakit dengan menyertakan keterangan yang jelas.', 'icon' => 'tabler-file-text'],
        ]; @endphp
        @foreach($guruSteps as $i => $step)
        <div class="col-md-6">
          <div class="guide-card">
            <div class="d-flex gap-3 align-items-start">
              <div class="step-badge">{{ $i + 1 }}</div>
              <div>
                <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                <div class="text-muted small">{{ $step['desc'] }}</div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Siswa Guide --}}
    <div id="guide-siswa" class="role-guide d-none">
      <div class="row g-4">
        @php $siswaSteps = [
          ['title' => 'QR Code Siswa', 'desc' => 'Tunjukkan QR Code Anda kepada petugas scan saat masuk dan pulang sekolah.', 'icon' => 'tabler-qrcode'],
          ['title' => 'Absensi Mandiri', 'desc' => 'Untuk absensi mandiri, buka halaman absensi dan scan QR dari perangkat Anda.', 'icon' => 'tabler-device-mobile'],
          ['title' => 'Lihat Riwayat', 'desc' => 'Pantau riwayat kehadiran Anda melalui portal siswa di menu Dashboard.', 'icon' => 'tabler-history'],
          ['title' => 'Pengajuan Izin', 'desc' => 'Ajukan izin atau sakit melalui menu Izin & Sakit sebelum atau sesudah hari absensi.', 'icon' => 'tabler-file-plus'],
        ]; @endphp
        @foreach($siswaSteps as $i => $step)
        <div class="col-md-6">
          <div class="guide-card">
            <div class="d-flex gap-3 align-items-start">
              <div class="step-badge">{{ $i + 1 }}</div>
              <div>
                <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                <div class="text-muted small">{{ $step['desc'] }}</div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Ortu Guide --}}
    <div id="guide-ortu" class="role-guide d-none">
      <div class="row g-4">
        @php $ortuSteps = [
          ['title' => 'Terima Notifikasi WA', 'desc' => 'Sistem otomatis mengirim pesan WhatsApp setiap anak Anda melakukan presensi. Pastikan nomor WA Anda terdaftar.', 'icon' => 'tabler-brand-whatsapp'],
          ['title' => 'Login Portal Orang Tua', 'desc' => 'Akses portal orang tua menggunakan akun yang telah didaftarkan oleh pihak sekolah.', 'icon' => 'tabler-login'],
          ['title' => 'Pantau Kehadiran', 'desc' => 'Lihat riwayat lengkap kehadiran anak Anda termasuk detail waktu masuk dan pulang.', 'icon' => 'tabler-eye'],
          ['title' => 'Ajukan Izin', 'desc' => 'Ajukan izin atau sakit untuk anak Anda langsung melalui portal orang tua.', 'icon' => 'tabler-file-text'],
        ]; @endphp
        @foreach($ortuSteps as $i => $step)
        <div class="col-md-6">
          <div class="guide-card">
            <div class="d-flex gap-3 align-items-start">
              <div class="step-badge">{{ $i + 1 }}</div>
              <div>
                <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                <div class="text-muted small">{{ $step['desc'] }}</div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Piket Guide --}}
    <div id="guide-piket" class="role-guide d-none">
      <div class="row g-4">
        @php $piketSteps = [
          ['title' => 'Akses Halaman Scan', 'desc' => 'Buka halaman scan-qr dari perangkat yang telah terdaftar dan diotorisasi oleh admin.', 'icon' => 'tabler-device-desktop'],
          ['title' => 'Login dengan Password', 'desc' => 'Masukkan password piket yang diberikan admin untuk mengaktifkan mode scan.', 'icon' => 'tabler-key'],
          ['title' => 'Scan QR Siswa', 'desc' => 'Arahkan kamera ke QR Code siswa. Sistem otomatis mencatat absensi dan mengirim notifikasi WA ke orang tua.', 'icon' => 'tabler-qrcode-scan'],
          ['title' => 'Konfirmasi Suara', 'desc' => 'Dengarkan bunyi konfirmasi: bel hijau = berhasil, nada merah = gagal/sudah absen.', 'icon' => 'tabler-volume'],
        ]; @endphp
        @foreach($piketSteps as $i => $step)
        <div class="col-md-6">
          <div class="guide-card">
            <div class="d-flex gap-3 align-items-start">
              <div class="step-badge">{{ $i + 1 }}</div>
              <div>
                <div class="fw-semibold mb-1">{{ $step['title'] }}</div>
                <div class="text-muted small">{{ $step['desc'] }}</div>
              </div>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section style="padding:4rem 0;">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Pertanyaan Umum</h2>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        @php $faqs = [
          ['q' => 'Bagaimana jika QR Code siswa rusak atau hilang?', 'a' => 'Admin atau operator dapat melakukan regenerate QR Code dari halaman profil siswa di menu Admin → Siswa → Profil. QR Code baru akan otomatis menggantikan yang lama.'],
          ['q' => 'Mengapa notifikasi WA tidak diterima orang tua?', 'a' => 'Pastikan: (1) Nomor WA orang tua sudah diisi dengan benar di data siswa, (2) WA Gateway aktif di pengaturan, (3) Nomor WA orang tua terdaftar dan aktif di WhatsApp. Sistem memvalidasi nomor sebelum mengirim pesan.'],
          ['q' => 'Apakah absensi bisa dilakukan dari smartphone?', 'a' => 'Ya, sistem mendukung absensi mandiri dari smartphone baik via browser maupun scan QR. Pastikan GPS aktif jika fitur validasi lokasi diaktifkan oleh admin.'],
          ['q' => 'Bagaimana cara mengajukan izin?', 'a' => 'Siswa, guru, atau orang tua dapat mengajukan izin melalui menu Izin & Sakit di portal masing-masing. Pengajuan akan diverifikasi oleh admin sekolah.'],
          ['q' => 'Data absensi tersimpan berapa lama?', 'a' => 'Data absensi tersimpan secara permanen di database selama tahun akademik berjalan. Admin dapat mengekspor laporan kapan saja.'],
        ]; @endphp
        @foreach($faqs as $faq)
        <div class="faq-item">
          <div class="faq-q" onclick="this.parentElement.classList.toggle('open')">
            <span>{{ $faq['q'] }}</span>
            <i class="ti tabler-chevron-down"></i>
          </div>
          <div class="faq-a">{{ $faq['a'] }}</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</section>

<section style="background:#f8fafc;padding:3rem 0;">
  <div class="container text-center">
    <h4 class="fw-bold mb-2">Masih butuh bantuan?</h4>
    <p class="text-muted mb-3">Tim kami siap membantu Anda</p>
    <a href="{{ route('public.bantuan') }}" class="btn btn-primary px-5 py-2 rounded-pill">
      <i class="ti tabler-help-circle me-2"></i>Hubungi Bantuan
    </a>
  </div>
</section>

@endsection

@push('scripts')
<script>
function showRole(role, btn) {
  document.querySelectorAll('.role-guide').forEach(el => el.classList.add('d-none'));
  document.querySelectorAll('.role-tab').forEach(el => {
    el.classList.remove('btn-primary');
    el.classList.add('btn-outline-secondary');
  });
  document.getElementById('guide-' + role).classList.remove('d-none');
  btn.classList.remove('btn-outline-secondary');
  btn.classList.add('btn-primary');
}
</script>
@endpush
