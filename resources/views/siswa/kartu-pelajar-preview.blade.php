@extends('layouts/layoutMaster')

@section('title', 'Preview Kartu Pelajar')

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

  /* Kartu Container */
  .kp-card-container {
    position: relative;
    border-radius: 0 !important;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(212,175,55,0.18), 0 0 0 1px rgba(212,175,55,0.25);
    font-family: 'Product Sans', sans-serif;
  }

  /* Fallback QR Card (1011x638 px = kartu ATM) */
  .kp-card-fallback {
    position: relative;
    width: 1011px;
    height: 638px;
    border-radius: 0 !important;
    overflow: hidden;
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
    font-size: 32px;
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
    margin-bottom: 25px;
  }

  /* Name below year */
  .kp-name-wrap {
    text-align: center;
    margin-bottom: 25px;
  }
  .kp-name {
    font-family: 'Product Sans', sans-serif;
    font-size: 42px;
    font-weight: 700;
    color: #ffffff;
    margin: 0;
    line-height: 1.1;
    text-shadow: 0 4px 15px rgba(0,0,0,0.5);
    letter-spacing: 1px;
    display: inline-block;
    border-bottom: 2px solid #d4af37;
    padding-bottom: 10px;
  }

  /* Body layout */
  .kp-body {
    display: flex;
    gap: 50px;
    flex: 1;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
  }

  /* Info column */
  .kp-info {
    flex: 0 0 450px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .kp-detail-row {
    display: flex;
    align-items: baseline;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
  }
  .kp-detail-row:last-child {
    border-bottom: none;
  }
  .kp-detail-label {
    width: 140px;
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
    width: 240px;
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

  /* CSS untuk Template Kustom */
  .template-wrapper {
    position: relative;
    overflow: hidden;
  }
  .template-card {
    position: relative;
    background: #ffffff;
    box-shadow: 0 25px 80px rgba(0,0,0,0.3);
  }
  .template-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: 1;
  }
  .template-element {
    position: absolute;
    z-index: 10;
  }
  .template-photo {
    border: 1px solid #ccc;
    object-fit: cover;
  }
  .template-qr {
    background: #fff;
    object-fit: contain;
  }
  .template-text {
    font-weight: bold;
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
    Preview Kartu Pelajar
    <small>Klik tombol download untuk menyimpan sebagai gambar</small>
  </div>

  <div class="kp-scale-wrapper">
    @if($template && $config)
      {{-- ====== KARTU PELAJAR TEMPLATE KUSTOM ====== --}}
      <div class="kp-card-container template-wrapper" id="kartuPelajar" 
           style="
             width: {{ $config['canvas']['width'] }}pt; 
             height: {{ $config['canvas']['height'] }}pt; 
             border-radius: 0 !important;
             @if($bgBase64) background-image: url('{{ $bgBase64 }}'); background-size: cover; background-position: center; @endif
           ">

        @php
          $elements = $config['elements'];
          $masaBerlakuText = $siswa->_masa_berlaku ?? 'Selama menjadi siswa aktif';
          if (isset($elements['masa_berlaku']) && $elements['masa_berlaku']['show']) {
              $service = app(\App\Services\IdCardPdfService::class);
              $jumlahTahun = $lembagaData['jumlah_tahun_sekolah'] ?? 3;
              $masaBerlakuText = $service->hitungMasaBerlakuSiswa($siswa, $jumlahTahun);
          }
        @endphp

        <!-- PHOTO -->
        @if(isset($elements['photo']) && $elements['photo']['show'])
          <div class="template-element" style="left: {{ $elements['photo']['x'] }}pt; top: {{ $elements['photo']['y'] }}pt;">
            @if($fotoBase64)
              <img class="template-photo" src="{{ $fotoBase64 }}" 
                   style="width: {{ $elements['photo']['w'] }}pt; height: {{ $elements['photo']['h'] }}pt;" alt="Foto">
            @else
              <img class="template-photo" src="{{ asset('assets/img/avatars/1.png') }}" 
                   style="width: {{ $elements['photo']['w'] }}pt; height: {{ $elements['photo']['h'] }}pt;" alt="Default Foto">
            @endif
          </div>
        @endif

        <!-- Name -->
        @if(isset($elements['name']) && $elements['name']['show'])
          <div class="template-element template-text" style="
              left: {{ $elements['name']['align'] == 'center' ? 0 : $elements['name']['x'] . 'pt' }}; 
              top: {{ $elements['name']['y'] }}pt;
              width: {{ $elements['name']['align'] == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['name']['align'] }};
              font-size: {{ $elements['name']['size'] }}pt;
              color: {{ $elements['name']['color'] }};
          ">
            {{ strtoupper($siswa->nama_lengkap) }}
          </div>
        @endif

        <!-- ID Card (NIS) -->
        @if(isset($elements['id_number']) && $elements['id_number']['show'])
          <div class="template-element template-text" style="
              left: {{ $elements['id_number']['align'] == 'center' ? 0 : $elements['id_number']['x'] . 'pt' }}; 
              top: {{ $elements['id_number']['y'] }}pt;
              width: {{ $elements['id_number']['align'] == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['id_number']['align'] }};
              font-size: {{ $elements['id_number']['size'] }}pt;
              color: {{ $elements['id_number']['color'] }};
          ">
            {{ $siswa->nis }}
          </div>
        @endif

        <!-- NIS (Siswa) -->
        @if(isset($elements['nis']) && $elements['nis']['show'])
          <div class="template-element template-text" style="
              left: {{ ($elements['nis']['align'] ?? 'center') == 'center' ? 0 : $elements['nis']['x'] . 'pt' }}; 
              top: {{ $elements['nis']['y'] }}pt;
              width: {{ ($elements['nis']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['nis']['align'] ?? 'center' }};
              font-size: {{ $elements['nis']['size'] ?? 12 }}pt;
              color: {{ $elements['nis']['color'] ?? '#555555' }};
          ">
            {{ $siswa->nis }}
          </div>
        @endif

        <!-- NISN (Siswa) -->
        @if(isset($elements['nisn']) && $elements['nisn']['show'])
          <div class="template-element template-text" style="
              left: {{ ($elements['nisn']['align'] ?? 'center') == 'center' ? 0 : $elements['nisn']['x'] . 'pt' }}; 
              top: {{ $elements['nisn']['y'] }}pt;
              width: {{ ($elements['nisn']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['nisn']['align'] ?? 'center' }};
              font-size: {{ $elements['nisn']['size'] ?? 12 }}pt;
              color: {{ $elements['nisn']['color'] ?? '#555555' }};
          ">
            {{ $siswa->nisn }}
          </div>
        @endif

        <!-- Class -->
        @if(isset($elements['class']) && $elements['class']['show'])
          <div class="template-element template-text" style="
              left: {{ $elements['class']['align'] == 'center' ? 0 : $elements['class']['x'] . 'pt' }}; 
              top: {{ $elements['class']['y'] }}pt;
              width: {{ $elements['class']['align'] == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['class']['align'] }};
              font-size: {{ $elements['class']['size'] }}pt;
              color: {{ $elements['class']['color'] }};
          ">
            {{ $siswa->kelas->nama ?? '-' }}
          </div>
        @endif

        <!-- QR Code -->
        @if(isset($elements['qr']) && $elements['qr']['show'])
          <div class="template-element" style="left: {{ $elements['qr']['x'] }}pt; top: {{ $elements['qr']['y'] }}pt;">
            <img class="template-qr" src="{{ $qrImage }}" style="width: {{ $elements['qr']['w'] }}pt; height: {{ $elements['qr']['h'] }}pt;" alt="QR Code">
          </div>
        @endif

        <!-- Logo Lembaga -->
        @if(isset($elements['logo_lembaga']) && $elements['logo_lembaga']['show'] && $lembagaData['logo_base64'])
          <div class="template-element" style="left: {{ $elements['logo_lembaga']['x'] }}pt; top: {{ $elements['logo_lembaga']['y'] }}pt;">
            <img src="{{ $lembagaData['logo_base64'] }}" style="width: {{ $elements['logo_lembaga']['w'] ?? 40 }}pt; height: {{ $elements['logo_lembaga']['h'] ?? 40 }}pt; object-fit: contain;" alt="Logo Lembaga">
          </div>
        @endif

        <!-- Nama Lembaga -->
        @if(isset($elements['nama_lembaga']) && $elements['nama_lembaga']['show'])
          <div class="template-element template-text" style="
              left: {{ ($elements['nama_lembaga']['align'] ?? 'left') == 'center' ? 0 : $elements['nama_lembaga']['x'] . 'pt' }};
              top: {{ $elements['nama_lembaga']['y'] }}pt;
              width: {{ ($elements['nama_lembaga']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['nama_lembaga']['align'] ?? 'left' }};
              font-size: {{ $elements['nama_lembaga']['size'] ?? 8 }}pt;
              color: {{ $elements['nama_lembaga']['color'] ?? '#000000' }};
          ">
            {{ $lembagaData['nama_sekolah'] }}
          </div>
        @endif

        <!-- Alamat Lembaga -->
        @if(isset($elements['alamat_lembaga']) && $elements['alamat_lembaga']['show'])
          <div class="template-element" style="
              left: {{ ($elements['alamat_lembaga']['align'] ?? 'left') == 'center' ? 0 : $elements['alamat_lembaga']['x'] . 'pt' }};
              top: {{ $elements['alamat_lembaga']['y'] }}pt;
              width: {{ ($elements['alamat_lembaga']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['alamat_lembaga']['align'] ?? 'left' }};
              font-size: {{ $elements['alamat_lembaga']['size'] ?? 7 }}pt;
              color: {{ $elements['alamat_lembaga']['color'] ?? '#333333' }};
          ">
            {{ $lembagaData['alamat_lembaga'] }}
          </div>
        @endif

        <!-- Jenis Kelamin -->
        @if(isset($elements['gender']) && $elements['gender']['show'])
          <div class="template-element template-text" style="
              left: {{ ($elements['gender']['align'] ?? 'left') == 'center' ? 0 : $elements['gender']['x'] . 'pt' }};
              top: {{ $elements['gender']['y'] }}pt;
              width: {{ ($elements['gender']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['gender']['align'] ?? 'left' }};
              font-size: {{ $elements['gender']['size'] ?? 8 }}pt;
              color: {{ $elements['gender']['color'] ?? '#000000' }};
          ">
            {{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
          </div>
        @endif

        <!-- TTL -->
        @if(isset($elements['ttl']) && $elements['ttl']['show'] && $siswa->tempat_lahir && $siswa->tanggal_lahir)
          <div class="template-element" style="
              left: {{ ($elements['ttl']['align'] ?? 'left') == 'center' ? 0 : $elements['ttl']['x'] . 'pt' }};
              top: {{ $elements['ttl']['y'] }}pt;
              width: {{ ($elements['ttl']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['ttl']['align'] ?? 'left' }};
              font-size: {{ $elements['ttl']['size'] ?? 7 }}pt;
              color: {{ $elements['ttl']['color'] ?? '#333333' }};
          ">
            {{ $siswa->tempat_lahir }}, {{ \Carbon\Carbon::parse($siswa->tanggal_lahir)->isoFormat('D MMMM Y') }}
          </div>
        @endif

        <!-- Masa Berlaku -->
        @if(isset($elements['masa_berlaku']) && $elements['masa_berlaku']['show'])
          <div class="template-element" style="
              left: {{ ($elements['masa_berlaku']['align'] ?? 'left') == 'center' ? 0 : $elements['masa_berlaku']['x'] . 'pt' }};
              top: {{ $elements['masa_berlaku']['y'] }}pt;
              width: {{ ($elements['masa_berlaku']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['masa_berlaku']['align'] ?? 'left' }};
              font-size: {{ $elements['masa_berlaku']['size'] ?? 7 }}pt;
              color: {{ $elements['masa_berlaku']['color'] ?? '#333333' }};
          ">
            {{ $masaBerlakuText }}
          </div>
        @endif

        <!-- Tempat Tanggal Terbit -->
        @if(isset($elements['tempat_tanggal_terbit']) && $elements['tempat_tanggal_terbit']['show'])
          <div class="template-element" style="
              left: {{ ($elements['tempat_tanggal_terbit']['align'] ?? 'left') == 'center' ? 0 : $elements['tempat_tanggal_terbit']['x'] . 'pt' }};
              top: {{ $elements['tempat_tanggal_terbit']['y'] }}pt;
              width: {{ ($elements['tempat_tanggal_terbit']['align'] ?? 'left') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['tempat_tanggal_terbit']['align'] ?? 'left' }};
              font-size: {{ $elements['tempat_tanggal_terbit']['size'] ?? 7 }}pt;
              color: {{ $elements['tempat_tanggal_terbit']['color'] ?? '#333333' }};
          ">
            {{ $lembagaData['kota_penerbitan'] }}, {{ now()->locale('id')->isoFormat('D MMMM Y') }}
          </div>
        @endif

        <!-- TTD Kepala Sekolah -->
        @if(isset($elements['ttd_kepala_sekolah']) && $elements['ttd_kepala_sekolah']['show'] && $lembagaData['ttd_base64'])
          <div class="template-element" style="left: {{ $elements['ttd_kepala_sekolah']['x'] }}pt; top: {{ $elements['ttd_kepala_sekolah']['y'] }}pt;">
            <img src="{{ $lembagaData['ttd_base64'] }}" style="width: {{ $elements['ttd_kepala_sekolah']['w'] ?? 60 }}pt; height: {{ $elements['ttd_kepala_sekolah']['h'] ?? 30 }}pt; object-fit: contain;" alt="TTD">
          </div>
        @endif

        <!-- Cap Lembaga -->
        @if(isset($elements['cap_lembaga']) && $elements['cap_lembaga']['show'] && $lembagaData['cap_base64'])
          <div class="template-element" style="left: {{ $elements['cap_lembaga']['x'] }}pt; top: {{ $elements['cap_lembaga']['y'] }}pt;">
            <img src="{{ $lembagaData['cap_base64'] }}" style="width: {{ $elements['cap_lembaga']['w'] ?? 50 }}pt; height: {{ $elements['cap_lembaga']['h'] ?? 50 }}pt; object-fit: contain;" alt="Cap">
          </div>
        @endif

        <!-- Nama Kepala Sekolah -->
        @if(isset($elements['nama_kepala_sekolah']) && $elements['nama_kepala_sekolah']['show'])
          <div class="template-element template-text" style="
              left: {{ ($elements['nama_kepala_sekolah']['align'] ?? 'center') == 'center' ? 0 : $elements['nama_kepala_sekolah']['x'] . 'pt' }};
              top: {{ $elements['nama_kepala_sekolah']['y'] }}pt;
              width: {{ ($elements['nama_kepala_sekolah']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['nama_kepala_sekolah']['align'] ?? 'center' }};
              font-size: {{ $elements['nama_kepala_sekolah']['size'] ?? 8 }}pt;
              color: {{ $elements['nama_kepala_sekolah']['color'] ?? '#000000' }};
          ">
            {{ $lembagaData['nama_kepala_lembaga'] }}
          </div>
        @endif

        <!-- NIP Kepala Sekolah -->
        @if(isset($elements['nip_kepala_sekolah']) && $elements['nip_kepala_sekolah']['show'])
          <div class="template-element" style="
              left: {{ ($elements['nip_kepala_sekolah']['align'] ?? 'center') == 'center' ? 0 : $elements['nip_kepala_sekolah']['x'] . 'pt' }};
              top: {{ $elements['nip_kepala_sekolah']['y'] }}pt;
              width: {{ ($elements['nip_kepala_sekolah']['align'] ?? 'center') == 'center' ? '100%' : 'auto' }};
              text-align: {{ $elements['nip_kepala_sekolah']['align'] ?? 'center' }};
              font-size: {{ $elements['nip_kepala_sekolah']['size'] ?? 7 }}pt;
              color: {{ $elements['nip_kepala_sekolah']['color'] ?? '#333333' }};
          ">
            NIP. {{ $lembagaData['nip_kepala_lembaga'] }}
          </div>
        @endif
      </div>
    @else
      {{-- ====== KARTU PELAJAR FALLBACK (QR & DATA STANDARD) ====== --}}
      <div class="kp-card-container kp-card-fallback" id="kartuPelajar">
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
            @if($lembagaData['logo_base64'])
              <img src="{{ $lembagaData['logo_base64'] }}" alt="Logo" class="kp-logo">
            @else
              <div class="kp-logo" style="display:flex;align-items:center;justify-content:center;font-size:28px;color:#d4af37;font-weight:700;font-family:'Product Sans', sans-serif;">
                {{ strtoupper(substr($lembagaData['nama_sekolah'], 0, 1)) }}
              </div>
            @endif
            <div class="kp-header-text">
              <p class="kp-school-name">{{ $lembagaData['nama_sekolah'] }}</p>
              <p class="kp-subtitle">Kartu Identitas Digital Pelajar</p>
            </div>
          </div>

          <div class="kp-divider"></div>

          {{-- Type label --}}
          <div class="kp-type-label">
            <span>KARTU PELAJAR</span>
          </div>
          <div class="kp-tahun">TAHUN PELAJARAN {{ $tahunAkademik }}</div>

          {{-- Nama Peserta --}}
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

            {{-- QR Code --}}
            <div class="kp-qr-section">
              <div class="kp-qr-box">
                <img src="{{ $qrImage }}" alt="QR Code">
              </div>
              <div class="kp-qr-label">ABSENSI DIGITAL</div>
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
    @endif
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
  const card = document.getElementById('kartuPelajar');
  const wrapper = document.querySelector('.kp-scale-wrapper');

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

  // Simpan style transform asli sebelum render
  const originalTransform = wrapper ? wrapper.style.transform : '';
  const originalMarginBottom = wrapper ? wrapper.style.marginBottom : '';

  // Hitung lebar dan tinggi kartu
  @if($template && $config)
    const cardWidth = Math.round({{ $config['canvas']['width'] }} * 1.33333); // convert pt to px
    const cardHeight = Math.round({{ $config['canvas']['height'] }} * 1.33333);
  @else
    const cardWidth = 1011;
    const cardHeight = 638;
  @endif

  // Nonaktifkan transform scaling sementara agar html2canvas membaca dimensi asli
  if (wrapper) {
    wrapper.style.transform = 'none';
    wrapper.style.marginBottom = '0';
  }

  // Tambahkan sedikit delay untuk memastikan layout terhitung ulang oleh browser
  setTimeout(() => {
    html2canvas(card, {
      scale: 6,
      useCORS: true,
      allowTaint: true,
      backgroundColor: null,
      imageTimeout: 0,
    }).then(function(canvas) {
      // Kembalikan style transform asli setelah render selesai
      if (wrapper) {
        wrapper.style.transform = originalTransform;
        wrapper.style.marginBottom = originalMarginBottom;
      }

      const link = document.createElement('a');
      link.download = 'Kartu_Pelajar_{{ $siswa->nama_lengkap }}_{{ $siswa->nis }}.png';
      link.href = canvas.toDataURL('image/png', 1.0);
      link.click();

      btn.disabled = false;
      btn.innerHTML = '<i class="ti tabler-download" style="font-size:18px;"></i> Download Kartu (PNG)';
    }).catch(function(err) {
      // Kembalikan style transform asli jika terjadi error
      if (wrapper) {
        wrapper.style.transform = originalTransform;
        wrapper.style.marginBottom = originalMarginBottom;
      }

      console.error('Gagal generate kartu:', err);
      alert('Gagal membuat gambar kartu. Silakan coba lagi.');
      btn.disabled = false;
      btn.innerHTML = '<i class="ti tabler-download" style="font-size:18px;"></i> Download Kartu (PNG)';
    });
  }, 350);
}
</script>
@endpush