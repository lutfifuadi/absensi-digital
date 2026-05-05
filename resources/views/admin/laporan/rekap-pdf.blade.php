<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Rekap Absensi {{ $namaBulan }} {{ $tahun }}</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 9px;
      margin: 15px;
    }

    h2 {
      font-size: 13px;
      margin: 0;
    }

    h3 {
      font-size: 11px;
      margin: 0;
    }

    .header {
      text-align: center;
      margin-bottom: 10px;
      border-bottom: 2px solid #000;
      padding-bottom: 6px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }

    th,
    td {
      border: 1px solid #555;
      padding: 3px 4px;
      text-align: center;
    }

    th {
      background: #e8e8e8;
      font-size: 8px;
    }

    td.nama {
      text-align: left;
      white-space: nowrap;
    }

    .hadir {
      background: #d4edda;
      color: #155724;
      font-weight: bold;
    }

    .sakit {
      background: #cce5ff;
      color: #004085;
    }

    .izin {
      background: #fff3cd;
      color: #856404;
    }

    .alpha {
      background: #f8d7da;
      color: #721c24;
      font-weight: bold;
    }

    .terlambat {
      background: #e2e3e5;
      color: #383d41;
    }

    .legend {
      margin-top: 10px;
      font-size: 8px;
    }

    .ttd {
      margin-top: 30px;
    }

    .ttd table {
      border: none;
    }

    .ttd td {
      border: none;
      padding: 2px 10px;
    }
  </style>
</head>

<body>
  <div class="header">
    <h2>{{ $namaSekolah }}</h2>
    <h3>REKAP ABSENSI SISWA — {{ strtoupper($namaBulan) }} {{ $tahun }}</h3>
    @if ($kelas)
      <p>Kelas: {{ $kelas->nama }}</p>
    @endif
  </div>

  <table>
    <thead>
      <tr>
        <th rowspan="2" style="width:20px;">#</th>
        <th rowspan="2" style="min-width:100px;text-align:left;">Nama Siswa</th>
        @foreach ($dates as $date)
          <th style="width:14px;">{{ (int) \Carbon\Carbon::parse($date)->format('d') }}</th>
        @endforeach
        <th rowspan="2">H</th>
        <th rowspan="2">S</th>
        <th rowspan="2">I</th>
        <th rowspan="2">A</th>
        <th rowspan="2">T</th>
      </tr>
      <tr>
        @foreach ($dates as $date)
          <th>{{ substr(\Carbon\Carbon::parse($date)->translatedFormat('D'), 0, 1) }}</th>
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
        @endphp
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td class="nama">{{ $siswa->nama_lengkap }}</td>
          @foreach ($dates as $date)
            @php $st = $pivot[$date] ?? null; @endphp
            <td class="{{ $st }}">{{ $st ? strtoupper(substr($st, 0, 1)) : '' }}</td>
          @endforeach
          <td>{{ $h }}</td>
          <td>{{ $s }}</td>
          <td>{{ $i }}</td>
          <td>{{ $a }}</td>
          <td>{{ $t }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="legend">
    <strong>Keterangan:</strong> H=Hadir, S=Sakit, I=Izin, A=Alpha, T=Terlambat
  </div>

  <div class="ttd">
    <table style="width:100%;">
      <tr>
        <td style="width:50%;"></td>
        <td>
          ................................................, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
          Kepala Sekolah,<br><br><br><br>
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
