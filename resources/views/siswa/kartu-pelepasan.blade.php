@extends('layouts/layoutMaster')

@section('title', 'Kartu Pelepasan')

@section('page-style')
<style>
  body, .layout-page, .content-wrapper {
    background: #0a0e1a !important;
  }
  .kp-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 70vh;
    padding: 2rem 1rem;
    font-family: 'Product Sans', sans-serif;
  }
  .kp-title {
    color: #d4af37;
    font-family: 'Product Sans', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    letter-spacing: 2px;
    margin-bottom: 1.5rem;
    text-align: center;
    text-transform: uppercase;
  }
  .kp-title small {
    display: block;
    color: rgba(255,255,255,0.45);
    font-size: 0.75rem;
    font-family: 'Product Sans', sans-serif;
    letter-spacing: 1.5px;
    margin-top: 4px;
    text-transform: none;
    font-weight: 400;
  }

  /* === KARTU (1011x638 px = kartu ATM) === */
  .kp-card {
    position: relative;
    width: 1011px;
    height: 638px;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(212,175,55,0.18), 0 0 0 1px rgba(212,175,55,0.25);
    font-family: 'Product Sans', sans-serif;
  }

  /* Background layer */
  .kp-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
    z-index: 1;
  }
  .kp-bg::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
      radial-gradient(circle at 15% 85%, rgba(212,175,55,0.08) 0%, transparent 50%),
      radial-gradient(circle at 85% 15%, rgba(212,175,55,0.06) 0%, transparent 50%),
      radial-gradient(circle at 50% 50%, rgba(30,58,95,0.3) 0%, transparent 70%);
    z-index: 1;
  }
  .kp-bg::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(30deg, rgba(212,175,55,0.03) 12%, transparent 12.5%, transparent 87%, rgba(212,175,55,0.03) 87.5%),
      linear-gradient(150deg, rgba(212,175,55,0.03) 12%, transparent 12.5%, transparent 87%, rgba(212,175,55,0.03) 87.5%),
      linear-gradient(30deg, rgba(212,175,55,0.03) 12%, transparent 12.5%, transparent 87%, rgba(212,175,55,0.03) 87.5%),
      linear-gradient(150deg, rgba(212,175,55,0.03) 12%, transparent 12.5%, transparent 87%, rgba(212,175,55,0.03) 87.5%);
    background-size: 80px 140px;
    background-position: 0 0, 0 0, 40px 70px, 40px 70px;
    z-index: 2;
  }

  /* Gold border frame */
  .kp-frame {
    position: absolute;
    inset: 8px;
    border: 1.5px solid rgba(212,175,55,0.35);
    border-radius: 12px;
    z-index: 5;
  }
  .kp-frame::before {
    content: '';
    position: absolute;
    inset: 3px;
    border: 0.5px solid rgba(212,175,55,0.15);
    border-radius: 10px;
  }

  /* Corner ornaments */
  .kp-corner {
    position: absolute;
    width: 40px;
    height: 40px;
    z-index: 6;
  }
  .kp-corner::before, .kp-corner::after {
    content: '';
    position: absolute;
    background: linear-gradient(135deg, #d4af37, #f0c75e);
  }
  .kp-corner-tl { top: 14px; left: 14px; }
  .kp-corner-tl::before { top: 0; left: 0; width: 20px; height: 2px; }
  .kp-corner-tl::after { top: 0; left: 0; width: 2px; height: 20px; }
  .kp-corner-tr { top: 14px; right: 14px; }
  .kp-corner-tr::before { top: 0; right: 0; width: 20px; height: 2px; }
  .kp-corner-tr::after { top: 0; right: 0; width: 2px; height: 20px; }
  .kp-corner-bl { bottom: 14px; left: 14px; }
  .kp-corner-bl::before { bottom: 0; left: 0; width: 20px; height: 2px; }
  .kp-corner-bl::after { bottom: 0; left: 0; width: 2px; height: 20px; }
  .kp-corner-br { bottom: 14px; right: 14px; }
  .kp-corner-br::before { bottom: 0; right: 0; width: 20px; height: 2px; }
  .kp-corner-br::after { bottom: 0; right: 0; width: 2px; height: 20px; }

  /* Gold accent line top */
  .kp-accent-top {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, transparent, #d4af37, #f0c75e, #d4af37, transparent);
    z-index: 10;
  }

  /* Content */
  .kp-content {
    position: relative;
    z-index: 10;
    width: 100%;
    height: 100%;
    padding: 28px 36px;
    display: flex;
    flex-direction: column;
  }

  /* Header */
  .kp-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 4px;
  }
  .kp-logo {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: contain;
    border: 2px solid rgba(212,175,55,0.5);
    background: rgba(255,255,255,0.1);
    padding: 5px;
    flex-shrink: 0;
  }
  .kp-header-text {
    flex: 1;
  }
  .kp-school-name {
    font-family: 'Product Sans', sans-serif;
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin: 0;
    line-height: 1.2;
  }
  .kp-subtitle {
    font-family: 'Product Sans', sans-serif;
    font-size: 10px;
    font-weight: 400;
    color: rgba(255,255,255,0.4);
    letter-spacing: 3px;
    text-transform: uppercase;
    margin: 4px 0 0;
  }

  /* Divider */
  .kp-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,0.4), transparent);
    margin: 10px 0 10px;
  }

  /* Card type label */
  .kp-type-label {
    text-align: center;
    margin-bottom: 8px;
  }
  .kp-type-label span {
    font-family: 'Product Sans', sans-serif;
    font-size: 32px; /* Perbesar judul utama */
    font-weight: 700;
    color: #d4af37;
    letter-spacing: 8px;
    text-transform: uppercase;
    display: inline-block;
    position: relative;
    padding: 0 32px;
  }
  .kp-type-label span::before,
  .kp-type-label span::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 80px;
    height: 1.5px;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,0.6));
  }
  .kp-type-label span::before { right: 100%; }
  .kp-type-label span::after { left: 100%; transform: scaleX(-1); }

  .kp-tahun {
    text-align: center;
    font-family: 'Product Sans', sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: rgba(240,199,94,0.6);
    letter-spacing: 5px;
    margin-top: -4px;
    margin-bottom: 25px; /* Jarak lebih ke bawah untuk nama */
  }

  /* Name below year */
  .kp-name-wrap {
    text-align: center;
    margin-bottom: 25px;
  }
  .kp-name {
    font-family: 'Product Sans', sans-serif;
    font-size: 42px; /* Nama lebih besar karena di tengah */
    font-weight: 700;
    color: #ffffff; /* Ganti ke putih agar kontras dengan judul emas */
    margin: 0;
    line-height: 1.1;
    text-shadow: 0 4px 15px rgba(0,0,0,0.5);
    letter-spacing: 1px;
    display: inline-block;
    border-bottom: 2px solid #d4af37;
    padding-bottom: 10px;
  }

  /* Body layout adjustment */
  .kp-body {
    display: flex;
    gap: 50px;
    flex: 1;
    align-items: center;
    justify-content: center; /* Center everything */
    margin-top: 10px;
  }

  /* Info column adjustment for central placement */
  .kp-info {
    flex: 0 0 450px; /* Fixed width for better centering */
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  /* Remove name from info column since it's moved up */
  .kp-info .kp-name { display: none; }
  .kp-detail-row {
    display: flex;
    align-items: baseline; /* Align by text baseline */
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .kp-detail-row:last-child {
    border-bottom: none;
  }
  .kp-detail-label {
    width: 140px; /* Fixed width for labels to align data */
    font-family: 'Product Sans', sans-serif;
    font-size: 11px;
    font-weight: 400;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 2.5px;
    flex-shrink: 0;
  }
  .kp-detail-value {
    font-family: 'Product Sans', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: 0.5px;
  }

  /* QR section */
  .kp-qr-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .kp-qr-box {
    background: #ffffff;
    border-radius: 15px;
    padding: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.4), 0 0 20px rgba(212,175,55,0.1);
    border: 3px solid rgba(212,175,55,0.3);
  }
  .kp-qr-box img {
    width: 240px; /* Dominant large size */
    height: 240px;
    display: block;
  }
  .kp-qr-label {
    margin-top: 15px;
    font-family: 'Product Sans', sans-serif;
    font-size: 10px;
    font-weight: 700;
    color: #d4af37;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-align: center;
  }

  /* Footer */
  .kp-footer {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: auto;
    padding-top: 8px;
  }
  .kp-footer-left,
  .kp-footer-right {
    font-family: 'Product Sans', sans-serif;
    font-size: 9px;
    font-weight: 400;
    color: rgba(255,255,255,0.25);
    letter-spacing: 2px;
  }
  .kp-footer-right {
    text-align: right;
  }

  /* Decorative circle */
  .kp-deco-circle {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(212,175,55,0.08);
    z-index: 3;
  }
  .kp-deco-1 {
    width: 300px;
    height: 300px;
    bottom: -100px;
    right: -80px;
  }
  .kp-deco-2 {
    width: 200px;
    height: 200px;
    top: -60px;
    left: -40px;
  }
  .kp-deco-3 {
    width: 150px;
    height: 150px;
    bottom: 60px;
    left: 200px;
    border-color: rgba(212,175,55,0.04);
  }

  /* Buttons */
  .kp-actions {
    display: flex;
    gap: 12px;
    margin-top: 1.5rem;
    flex-wrap: wrap;
    justify-content: center;
  }
  .kp-btn-download {
    background: linear-gradient(135deg, #d4af37, #f0c75e);
    color: #0f172a;
    border: none;
    padding: 12px 32px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(212,175,55,0.3);
  }
  .kp-btn-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(212,175,55,0.4);
  }
  .kp-btn-download:disabled {
    opacity: 0.6;
    cursor: wait;
  }
  .kp-btn-back {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.15);
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
  }
  .kp-btn-back:hover {
    background: rgba(255,255,255,0.12);
    color: #fff;
  }

  /* Scale for smaller screens */
  .kp-scale-wrapper {
    transform-origin: top center;
  }
  @media (max-width: 1100px) {
    .kp-scale-wrapper { transform: scale(0.75); margin-bottom: -160px; }
  }
  @media (max-width: 800px) {
    .kp-scale-wrapper { transform: scale(0.5); margin-bottom: -320px; }
  }
  @media (max-width: 550px) {
    .kp-scale-wrapper { transform: scale(0.38); margin-bottom: -400px; }
  }
</style>
@endsection

@section('content')
<div class="kp-wrapper">
  <div class="kp-title">
    Preview Kartu Pelepasan
    <small>Klik tombol download untuk menyimpan sebagai gambar</small>
  </div>

  <div class="kp-scale-wrapper">
    {{-- ====== KARTU PELEPASAN ====== --}}
    <div class="kp-card" id="kartuPelepasan">
      {{-- Background --}}
      <div class="kp-bg">
        <div class="kp-deco-circle kp-deco-1"></div>
        <div class="kp-deco-circle kp-deco-2"></div>
        <div class="kp-deco-circle kp-deco-3"></div>
      </div>

      {{-- Gold border frame --}}
      <div class="kp-frame"></div>

      {{-- Corner ornaments --}}
      <div class="kp-corner kp-corner-tl"></div>
      <div class="kp-corner kp-corner-tr"></div>
      <div class="kp-corner kp-corner-bl"></div>
      <div class="kp-corner kp-corner-br"></div>

      {{-- Gold accent top --}}
      <div class="kp-accent-top"></div>

      {{-- Content --}}
      <div class="kp-content">
        {{-- Header --}}
        <div class="kp-header">
          @if($logoSekolah)
            <img src="{{ $logoSekolah }}" alt="Logo" class="kp-logo">
          @else
            <div class="kp-logo" style="display:flex;align-items:center;justify-content:center;font-size:28px;color:#d4af37;font-weight:700;font-family:'Product Sans', sans-serif;">
              {{ strtoupper(substr($namaSekolah, 0, 1)) }}
            </div>
          @endif
          <div class="kp-header-text">
            <p class="kp-school-name">{{ $namaSekolah }}</p>
            <p class="kp-subtitle">Kementerian Agama Republik Indonesia</p>
          </div>
        </div>

        <div class="kp-divider"></div>

        {{-- Type label --}}
        <div class="kp-type-label">
          <span>Kartu Peserta Pelepasan</span>
        </div>
        <div class="kp-tahun">TAHUN PELAJARAN {{ $tahunAkademik }}</div>

        {{-- Nama Peserta (Moved here) --}}
        <div class="kp-name-wrap">
          <h2 class="kp-name">{{ strtoupper($siswa->nama_lengkap) }}</h2>
        </div>

        {{-- Body --}}
        <div class="kp-body">
          {{-- Info --}}
          <div class="kp-info">
            <div class="kp-detail-row">
              <span class="kp-detail-label">NISN</span>
              <span class="kp-detail-value">{{ $siswa->nisn ?? '-' }}</span>
            </div>
            <div class="kp-detail-row">
              <span class="kp-detail-label">NIS</span>
              <span class="kp-detail-value">{{ $siswa->nis ?? '-' }}</span>
            </div>
            <div class="kp-detail-row">
              <span class="kp-detail-label">Jenis Kelamin</span>
              <span class="kp-detail-value">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
            </div>
            <div class="kp-detail-row">
              <span class="kp-detail-label">Kelas</span>
              <span class="kp-detail-value">{{ $siswa->kelas->nama ?? '-' }}</span>
            </div>
          </div>

          {{-- QR Code (Dominant Large) --}}
          <div class="kp-qr-section">
            <div class="kp-qr-box">
              <img src="{{ $qrImage }}" alt="QR Code">
            </div>
            <div class="kp-qr-label">IDENTITAS DIGITAL</div>
          </div>
        </div>

        {{-- Footer --}}
        <div class="kp-footer">
          <div class="kp-footer-left">
            ID: {{ $siswa->qr_code ?? $siswa->nisn }}
          </div>
          <div class="kp-footer-right">
            Dicetak: {{ now()->locale('id')->translatedFormat('d F Y') }}
          </div>
        </div>
      </div>
    </div>
    {{-- ====== END KARTU ====== --}}
  </div>

  {{-- Action Buttons --}}
  <div class="kp-actions">
    <button type="button" class="kp-btn-download" id="btnDownloadKartu" onclick="downloadKartu()">
      <i class="ti tabler-download" style="font-size:18px;"></i>
      Download Kartu (PNG)
    </button>
    <a href="{{ route('dashboard') }}" class="kp-btn-back">
      <i class="ti tabler-arrow-left" style="font-size:16px;"></i>
      Kembali ke Dashboard
    </a>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
function downloadKartu() {
  const btn = document.getElementById('btnDownloadKartu');
  const card = document.getElementById('kartuPelepasan');

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

  html2canvas(card, {
    scale: 2,
    useCORS: true,
    allowTaint: true,
    backgroundColor: null,
    width: 1011,
    height: 638,
    logging: false,
  }).then(function(canvas) {
    const link = document.createElement('a');
    link.download = 'kartu-pelepasan-{{ \Illuminate\Support\Str::slug($siswa->nama_lengkap) }}.png';
    link.href = canvas.toDataURL('image/png', 1.0);
    link.click();

    btn.disabled = false;
    btn.innerHTML = '<i class="ti tabler-download" style="font-size:18px;"></i> Download Kartu (PNG)';
  }).catch(function(err) {
    console.error('Gagal generate kartu:', err);
    alert('Gagal membuat gambar kartu. Silakan coba lagi.');
    btn.disabled = false;
    btn.innerHTML = '<i class="ti tabler-download" style="font-size:18px;"></i> Download Kartu (PNG)';
  });
}
</script>
@endpush
