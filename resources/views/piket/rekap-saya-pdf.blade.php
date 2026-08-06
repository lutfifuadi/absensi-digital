<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Laporan Shift Piket — {{ $tanggal }}</title>
  <style>
    body {
      font-family: 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #333;
      margin: 0;
      padding: 0;
    }
    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
    }
    .header h2 {
      margin: 0;
      font-size: 16px;
      text-transform: uppercase;
    }
    .header p {
      margin: 3px 0 0 0;
      font-size: 10px;
      color: #666;
    }
    .meta-table {
      width: 100%;
      margin-bottom: 15px;
      font-size: 11px;
    }
    .meta-table td {
      padding: 3px 0;
    }
    .table-data {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    .table-data th, .table-data td {
      border: 1px solid #ccc;
      padding: 6px 8px;
      text-align: left;
    }
    .table-data th {
      background-color: #f2f2f2;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 10px;
    }
    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    .footer-sig {
      margin-top: 40px;
      width: 100%;
    }
    .footer-sig td {
      text-align: center;
      vertical-align: top;
    }
  </style>
</head>
<body>
  <div class="header">
    <h2>{{ $namaSekolah }}</h2>
    <p>LAPORAN TRANSAKSI SHIFT GURU PIKET</p>
    <p>Tanggal: {{ \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMMM Y') }}</p>
  </div>

  <table class="meta-table">
    <tr>
      <td style="width: 15%;"><strong>Petugas Piket</strong></td>
      <td style="width: 35%;">: {{ $user->name ?? session('piket_user_name', 'Guru Piket') }}</td>
      <td style="width: 15%;"><strong>Total Transaksi</strong></td>
      <td style="width: 35%;">: {{ $logs->count() }} Siswa</td>
    </tr>
    <tr>
      <td><strong>Role / Jabatan</strong></td>
      <td>: Guru Piket Bertugas</td>
      <td><strong>Waktu Cetak</strong></td>
      <td>: {{ now()->translatedFormat('d F Y H:i:s') }} WIB</td>
    </tr>
  </table>

  <table class="table-data">
    <thead>
      <tr>
        <th class="text-center" style="width: 5%;">No</th>
        <th style="width: 25%;">Nama Siswa</th>
        <th style="width: 15%;">Kelas</th>
        <th class="text-center" style="width: 12%;">Jam Masuk</th>
        <th class="text-center" style="width: 12%;">Jam Pulang</th>
        <th class="text-center" style="width: 12%;">Status</th>
        <th style="width: 19%;">Metode / Ket.</th>
      </tr>
    </thead>
    <tbody>
      @forelse($logs as $index => $row)
        <tr>
          <td class="text-center">{{ $index + 1 }}</td>
          <td>
            <strong>{{ $row->siswa->nama_lengkap ?? '-' }}</strong><br>
            <small style="color:#666;">NISN: {{ $row->siswa->nisn ?? '-' }}</small>
          </td>
          <td>{{ $row->siswa->kelas->nama ?? '-' }}</td>
          <td class="text-center">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</td>
          <td class="text-center">{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</td>
          <td class="text-center">
            {{ strtoupper($row->status) }}
          </td>
          <td>{{ strtoupper($row->metode ?? 'QR') }} - {{ $row->keterangan ?? '-' }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center" style="padding: 20px;">
            Tidak ada transaksi scan/presensi yang dicatat pada shift tanggal ini.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>

  <table class="footer-sig">
    <tr>
      <td style="width: 50%;">
        Mengetahui,<br>
        Kepala Sekolah / Admin<br><br><br><br>
        ( _______________________ )
      </td>
      <td style="width: 50%;">
        Guru Piket Bertugas,<br><br><br><br><br>
        ( <strong>{{ $user->name ?? session('piket_user_name', 'Guru Piket') }}</strong> )
      </td>
    </tr>
  </table>
</body>
</html>
