<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kartu QR — {{ $siswa->nama_lengkap }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 12px;
      color: #222;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100%;
    }

    .kartu {
      width: 200px;
      border: 1.5px solid #333;
      border-radius: 8px;
      padding: 12px;
      text-align: center;
      margin: 10px auto;
    }

    .header {
      font-size: 9px;
      font-weight: bold;
      color: #444;
      margin-bottom: 8px;
      padding-bottom: 6px;
      border-bottom: 1px solid #ddd;
    }

    .kartu img {
      width: 160px;
      height: 160px;
      margin-bottom: 8px;
    }

    .nama {
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 4px;
    }

    .info {
      font-size: 10px;
      color: #555;
      margin-bottom: 2px;
    }

    .nisn {
      font-size: 11px;
      font-weight: bold;
      color: #222;
      margin-bottom: 2px;
    }

    .footer {
      margin-top: 8px;
      padding-top: 6px;
      border-top: 1px dashed #ddd;
      font-size: 8px;
      color: #999;
    }
  </style>
</head>

<body>
  <div class="kartu">
    <div class="header">{{ $namaSekolah }}</div>
    <img src="{{ $qrImage }}" alt="QR Code {{ $siswa->nisn }}">
    <div class="nama">{{ $siswa->nama_lengkap }}</div>
    <div class="info">NIS: {{ $siswa->nis }}</div>
    <div class="nisn">NISN: {{ $siswa->nisn }}</div>
    <div class="info">{{ optional($siswa->kelas)->nama ?? '-' }}</div>
    <div class="footer">Kartu Absensi QR Code</div>
  </div>
</body>

</html>
