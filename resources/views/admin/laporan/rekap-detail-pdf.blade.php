<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Rincian Presensi Siswa {{ $namaBulan }} {{ $tahun }}</title>
  <style>
    @page { margin: 10mm 12mm 12mm 12mm; }
    body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #1e293b; line-height: 1.3; }

    /* KOP SURAT RESMI */
    .kop-container { width: 100%; margin-bottom: 8px; }
    .kop-table { width: 100%; border-collapse: collapse; border: none; }
    .kop-table td { border: none; padding: 0; vertical-align: middle; }
    .kop-logo { width: 60px; text-align: center; }
    .kop-logo img { max-width: 55px; max-height: 55px; object-fit: contain; }
    .kop-text { text-align: center; padding-left: 10px; padding-right: 60px; }
    .kop-text .nama-lembaga { font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; margin: 0; }
    .kop-text .nama-sekolah { font-size: 13pt; font-weight: 900; text-transform: uppercase; color: #1e293b; margin: 2px 0; }
    .kop-text .alamat-sekolah { font-size: 7.5pt; color: #475569; margin: 0; }

    .kop-divider { border: 0; border-top: 2px solid #0f172a; border-bottom: 1px solid #0f172a; height: 2px; margin-top: 5px; margin-bottom: 10px; }

    .header { text-align: center; margin-bottom: 12px; }
    .header h3 { font-size: 11pt; font-weight: bold; text-transform: uppercase; color: #0f172a; margin: 0; }
    .header p { font-size: 8.5pt; color: #334155; font-weight: 600; margin-top: 3px; }

    /* TABEL DETAIL */
    .main-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
    .main-table thead th { background-color: #1e293b; color: #ffffff; font-size: 8pt; text-transform: uppercase; padding: 6px 4px; border: 1px solid #334155; text-align: center; }
    .main-table tbody td { padding: 4px 6px; border: 1px solid #cbd5e1; font-size: 8pt; }
    .main-table tbody tr:nth-child(even) td { background-color: #f8fafc; }

    .text-center { text-align: center; }
    .text-left { text-align: left; }
    .fw-bold { font-weight: bold; }

    /* STATUS COLORS */
    .hadir { background-color: #d1fae5 !important; color: #065f46 !important; font-weight: bold; }
    .sakit { background-color: #dbeafe !important; color: #1e40af !important; font-weight: bold; }
    .izin { background-color: #fef3c7 !important; color: #92400e !important; font-weight: bold; }
    .alpha { background-color: #fee2e2 !important; color: #991b1b !important; font-weight: bold; }
    .terlambat { background-color: #f3e8ff !important; color: #6b21a8 !important; font-weight: bold; }

    /* BLOK TANDA TANGAN */
    .ttd-box { margin-top: 25px; width: 100%; page-break-inside: avoid; }
    .ttd-table { width: 100%; border-collapse: collapse; border: none; }
    .ttd-table td { border: none; width: 50%; text-align: center; vertical-align: top; font-size: 8.5pt; }
  </style>
</head>
<body>

  @php
    $logoVal = setting('logo_sekolah') ?: setting('logo_url');
    $logoPath = null;
    if (!empty($logoVal)) {
      if (filter_var($logoVal, FILTER_VALIDATE_URL)) {
        $logoPath = $logoVal;
      } else {
        $logoPath = public_path('uploads/logo/' . $logoVal);
        if (!file_exists($logoPath)) {
          $logoPath = public_path($logoVal);
        }
      }
    }
    $hasLogo       = !empty($logoPath) && file_exists($logoPath);
    $namaLembaga   = setting('nama_lembaga');
    $namaSekolah   = setting('nama_sekolah') ?: $namaSekolah ?? 'SEKOLAH';
    $alamatSekolah = setting('alamat_sekolah') ?: setting('alamat');
    $telpSekolah   = setting('telepon_sekolah') ?: setting('telepon');
    $emailSekolah  = setting('email_sekolah') ?: setting('email');
    $headerContact = array_filter([$telpSekolah ? 'Telp: '.$telpSekolah : null, $emailSekolah ? 'Email: '.$emailSekolah : null]);

    $kepalaSekolah = !empty($kepalaSekolah) && $kepalaSekolah !== '-' ? $kepalaSekolah : (
        setting('nama_kepala_lembaga')
        ?: setting('kepala_sekolah')
        ?: setting('nama_kepala_sekolah')
        ?: setting('kepala_lembaga')
        ?: ''
    );

    $nipKepala     = !empty($nipKepala) ? $nipKepala : (
        setting('nip_kepala_lembaga')
        ?: setting('nip_kepala_sekolah')
        ?: setting('nip_kepala')
        ?: ''
    );
  @endphp

  <!-- KOP SURAT RESMI -->
  <div class="kop-container">
    <table class="kop-table">
      <tr>
        @if($hasLogo)
          <td class="kop-logo">
            <img src="{{ $logoPath }}" alt="Logo">
          </td>
        @endif
        <td class="kop-text" style="{{ !$hasLogo ? 'padding-left:0; padding-right:0;' : '' }}">
          @if(!empty($namaLembaga) && strtoupper(trim($namaLembaga)) !== strtoupper(trim($namaSekolah)))
            <div class="nama-lembaga">{{ strtoupper($namaLembaga) }}</div>
          @endif
          <div class="nama-sekolah">{{ strtoupper($namaSekolah) }}</div>
          @if(!empty($alamatSekolah) || count($headerContact) > 0)
            <div class="alamat-sekolah">
              @if(!empty($alamatSekolah)) {{ $alamatSekolah }} @endif
              @if(count($headerContact) > 0)
                @if(!empty($alamatSekolah)) | @endif {{ implode(' | ', $headerContact) }}
              @endif
            </div>
          @endif
        </td>
      </tr>
    </table>
    <div class="kop-divider"></div>
  </div>

  <div class="header">
    <h3>LAPORAN RINCIAN JAM PRESENSI SISWA (LOG DETAIL)</h3>
    <p>PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} @if($kelas) | KELAS: {{ strtoupper($kelas->nama) }} @endif</p>
  </div>

  <table class="main-table">
    <thead>
      <tr>
        <th style="width:25px;">#</th>
        <th style="width:65px;">Tanggal</th>
        <th style="width:55px;">Hari</th>
        <th style="width:70px;">NIS</th>
        <th style="text-align:left; padding-left:6px;">Nama Siswa</th>
        <th style="width:55px;">Kelas</th>
        <th style="width:65px;">Status</th>
        <th style="width:60px;">Jam Masuk</th>
        <th style="width:60px;">Jam Pulang</th>
        <th style="width:100px;">Metode / Ket</th>
      </tr>
    </thead>
    <tbody>
      @forelse($absensiLogs as $log)
        @php
          $st = strtolower($log->status ?? '');
        @endphp
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $log->tanggal ? $log->tanggal->format('d/m/Y') : '-' }}</td>
          <td class="text-center">{{ $log->tanggal ? $log->tanggal->translatedFormat('l') : '-' }}</td>
          <td class="text-center">{{ $log->siswa?->nis ?? '-' }}</td>
          <td class="text-left fw-bold">{{ $log->siswa?->nama_lengkap ?? '-' }}</td>
          <td class="text-center">{{ $log->kelas?->nama ?? '-' }}</td>
          <td class="text-center {{ $st }}">{{ ucfirst($log->status ?? '-') }}</td>
          <td class="text-center fw-bold">{{ $log->jam_masuk ? \Carbon\Carbon::parse($log->jam_masuk)->format('H:i:s') : '-' }}</td>
          <td class="text-center fw-bold">{{ $log->jam_pulang ? \Carbon\Carbon::parse($log->jam_pulang)->format('H:i:s') : '-' }}</td>
          <td class="text-left">{{ ucfirst($log->metode ?? 'manual') }} @if($log->keterangan) ({{ $log->keterangan }}) @endif</td>
        </tr>
      @empty
        <tr>
          <td colspan="10" class="text-center" style="padding:15px; color:#94a3b8;">
            Tidak ada data presensi pada periode ini.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <!-- BLOK TANDA TANGAN -->
  <div class="ttd-box">
    <table class="ttd-table">
      <tr>
        <td>
          Mengetahui,<br>
          Wali Kelas {{ $kelas ? $kelas->nama : '' }}<br><br><br><br><br>
          <strong>{{ $kelas && $kelas->waliKelas ? $kelas->waliKelas->nama_lengkap : '_________________________' }}</strong><br>
          @if($kelas && $kelas->waliKelas && $kelas->waliKelas->nip)
            NIP. {{ $kelas->waliKelas->nip }}
          @endif
        </td>
        <td>
          {{ setting('kota_sekolah', 'Kabupaten') }}, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
          Kepala Sekolah,<br><br><br><br><br>
          <strong>{{ $kepalaSekolah ?: '_________________________' }}</strong><br>
          @if ($nipKepala)
            NIP. {{ $nipKepala }}
          @endif
        </td>
      </tr>
    </table>
  </div>

</body>
</html>
