<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Rekap Absensi {{ $namaBulan }} {{ $tahun }}</title>
  <style>
    @page {
      margin: 12mm 15mm 15mm 15mm;
    }
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 8.5pt;
      color: #1e293b;
      line-height: 1.3;
    }

    /* KOP SURAT RESMI */
    .kop-container {
      width: 100%;
      margin-bottom: 8px;
    }
    .kop-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .kop-table td {
      border: none;
      padding: 0;
      vertical-align: middle;
    }
    .kop-logo {
      width: 65px;
      text-align: center;
    }
    .kop-logo img {
      max-width: 60px;
      max-height: 60px;
      object-fit: contain;
    }
    .kop-text {
      text-align: center;
      padding-left: 10px;
      padding-right: 65px;
    }
    .kop-text .nama-lembaga {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #0f172a;
      margin: 0;
    }
    .kop-text .nama-sekolah {
      font-size: 14pt;
      font-weight: 900;
      text-transform: uppercase;
      color: #1e293b;
      margin: 2px 0;
    }
    .kop-text .alamat-sekolah {
      font-size: 8pt;
      color: #475569;
      margin: 0;
    }

    /* GARIS KOP GANDA */
    .kop-divider {
      border: 0;
      border-top: 2px solid #0f172a;
      border-bottom: 1px solid #0f172a;
      height: 2px;
      margin-top: 5px;
      margin-bottom: 12px;
    }

    /* META JUDUL LAPORAN */
    .report-title-box {
      text-align: center;
      margin-bottom: 12px;
    }
    .report-title-box h3 {
      font-size: 11pt;
      font-weight: 800;
      text-transform: uppercase;
      margin: 0;
      color: #0f172a;
      letter-spacing: 0.5px;
    }
    .report-title-box p {
      font-size: 9pt;
      font-weight: 600;
      color: #334155;
      margin: 3px 0 0 0;
    }

    /* TABEL REKAP */
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 6px;
      font-size: 8pt;
    }
    table.data-table th, 
    table.data-table td {
      border: 1px solid #94a3b8;
      padding: 3px 2px;
      text-align: center;
    }
    table.data-table th {
      background-color: #1e293b;
      color: #ffffff;
      font-weight: bold;
      font-size: 7.5pt;
      text-transform: uppercase;
    }
    table.data-table th.sub-header {
      background-color: #334155;
    }
    table.data-table td.nama {
      text-align: left;
      padding-left: 6px;
      font-weight: 600;
      white-space: nowrap;
    }

    /* WEEKEND HIGHLIGHT */
    .weekend {
      background-color: #f1f5f9 !important;
      color: #94a3b8 !important;
    }

    /* STATUS COLORS */
    .hadir {
      background-color: #d1fae5 !important;
      color: #065f46 !important;
      font-weight: bold;
    }
    .sakit {
      background-color: #dbeafe !important;
      color: #1e40af !important;
      font-weight: bold;
    }
    .izin {
      background-color: #fef3c7 !important;
      color: #92400e !important;
      font-weight: bold;
    }
    .alpha {
      background-color: #fee2e2 !important;
      color: #991b1b !important;
      font-weight: bold;
    }
    .terlambat {
      background-color: #f3e8ff !important;
      color: #6b21a8 !important;
      font-weight: bold;
    }

    /* SUMMARY FOOTER */
    .legend-box {
      margin-top: 10px;
      font-size: 8pt;
      color: #334155;
    }
    .legend-item {
      display: inline-block;
      margin-right: 12px;
    }

    /* BLOK TANDA TANGAN */
    .ttd-box {
      margin-top: 25px;
      width: 100%;
      page-break-inside: avoid;
    }
    .ttd-table {
      width: 100%;
      border-collapse: collapse;
      border: none;
    }
    .ttd-table td {
      border: none;
      width: 50%;
      text-align: center;
      vertical-align: top;
      font-size: 8.5pt;
    }
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
    $hasLogo = !empty($logoPath) && file_exists($logoPath);
    $namaLembaga   = setting('nama_lembaga');
    $namaSekolah   = setting('nama_sekolah') ?: $namaSekolah ?? 'SEKOLAH';
    $alamatSekolah = setting('alamat_sekolah') ?: setting('alamat');
    $telpSekolah   = setting('telepon_sekolah') ?: setting('telepon');
    $emailSekolah  = setting('email_sekolah') ?: setting('email');
    $headerContact = array_filter([$telpSekolah ? 'Telp: '.$telpSekolah : null, $emailSekolah ? 'Email: '.$emailSekolah : null]);
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
          @if(!empty($namaLembaga))
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

  <!-- JUDUL LAPORAN -->
  <div class="report-title-box">
    <h3>LAPORAN REKAPITULASI PRESENSI SISWA</h3>
    <p>PERIODE: {{ strtoupper($namaBulan) }} {{ $tahun }} @if($kelas) | KELAS: {{ strtoupper($kelas->nama) }} @endif</p>
  </div>

  <!-- TABEL DATA -->
  <table class="data-table">
    <thead>
      <tr>
        <th rowspan="2" style="width:20px;">#</th>
        <th rowspan="2" style="min-width:130px; text-align:left; padding-left:6px;">Nama Siswa</th>
        @foreach ($dates as $date)
          @php
            $dt = \Carbon\Carbon::parse($date);
            $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
          @endphp
          <th class="{{ $isWeekend ? 'weekend' : '' }}" style="width:16px;">
            {{ (int) $dt->format('d') }}
          </th>
        @endforeach
        <th rowspan="2" style="width:22px;">H</th>
        <th rowspan="2" style="width:22px;">S</th>
        <th rowspan="2" style="width:22px;">I</th>
        <th rowspan="2" style="width:22px;">A</th>
        <th rowspan="2" style="width:22px;">T</th>
        <th rowspan="2" style="width:30px;">%</th>
      </tr>
      <tr>
        @foreach ($dates as $date)
          @php
            $dt = \Carbon\Carbon::parse($date);
            $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
          @endphp
          <th class="sub-header {{ $isWeekend ? 'weekend' : '' }}">
            {{ substr($dt->translatedFormat('D'), 0, 1) }}
          </th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      @foreach ($siswaList as $siswa)
        @php
          $pivot = $absensiPivot[$siswa->id] ?? [];
          $h = collect($pivot)->filter(fn($v) => $v === 'hadir')->count();
          $s = collect($pivot)->filter(fn($v) => $v === 'sakit')->count();
          $i = collect($pivot)->filter(fn($v) => $v === 'izin')->count();
          $a = collect($pivot)->filter(fn($v) => $v === 'alpha')->count();
          $t = collect($pivot)->filter(fn($v) => $v === 'terlambat')->count();
          
          $effectiveDays = count($dates);
          $persen = $effectiveDays > 0 ? round((($h + $t) / $effectiveDays) * 100) : 0;
        @endphp
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td class="nama">{{ $siswa->nama_lengkap }}</td>
          @foreach ($dates as $date)
            @php 
              $dt = \Carbon\Carbon::parse($date);
              $isWeekend = in_array($dt->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
              $st = $pivot[$date] ?? null; 
            @endphp
            <td class="{{ $st ? $st : ($isWeekend ? 'weekend' : '') }}">
              {{ $st ? strtoupper(substr($st, 0, 1)) : ($isWeekend ? '-' : '') }}
            </td>
          @endforeach
          <td class="hadir">{{ $h }}</td>
          <td class="sakit">{{ $s }}</td>
          <td class="izin">{{ $i }}</td>
          <td class="alpha">{{ $a }}</td>
          <td class="terlambat">{{ $t }}</td>
          <td style="font-weight:bold;">{{ $persen }}%</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="legend-box">
    <strong>Keterangan:</strong> 
    <span class="legend-item"><span style="color:#065f46;font-weight:bold;">H</span> = Hadir</span>
    <span class="legend-item"><span style="color:#1e40af;font-weight:bold;">S</span> = Sakit</span>
    <span class="legend-item"><span style="color:#92400e;font-weight:bold;">I</span> = Izin</span>
    <span class="legend-item"><span style="color:#991b1b;font-weight:bold;">A</span> = Alpha</span>
    <span class="legend-item"><span style="color:#6b21a8;font-weight:bold;">T</span> = Terlambat</span>
  </div>

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
