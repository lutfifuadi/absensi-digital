<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kartu QR Siswa — {{ $kelas->nama }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 11px;
      color: #222;
    }

    .page-title {
      text-align: center;
      margin-bottom: 12px;
      padding-bottom: 6px;
      border-bottom: 2px solid #333;
    }

    .page-title h2 {
      font-size: 14px;
      margin-bottom: 2px;
    }

    .page-title p {
      font-size: 11px;
      color: #555;
    }

    .grid {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: flex-start;
    }

    .card {
      width: 160px;
      border: 1px solid #ccc;
      border-radius: 6px;
      padding: 8px;
      text-align: center;
      page-break-inside: avoid;
    }

    .card img {
      width: 120px;
      height: 120px;
      margin-bottom: 4px;
    }

    .card .nama {
      font-size: 10px;
      font-weight: bold;
      line-height: 1.3;
    }

    .card .nis {
      font-size: 9px;
      color: #555;
    }

    .card .kelas {
      font-size: 9px;
      color: #777;
    }

    .card .sekolah {
      font-size: 8px;
      color: #999;
      margin-top: 3px;
      border-top: 1px dashed #ddd;
      padding-top: 3px;
    }
  </style>
</head>

<body>
  <div class="page-title">
    <h2>{{ $namaSekolah }}</h2>
    <p>Kartu Absensi QR Code — {{ $namaKelas }}</p>
  </div>

  <div class="grid">
    @foreach ($siswaList as $siswa)
      <div class="card">
        <img src="{{ $qrImages[$siswa->id] }}" alt="QR {{ $siswa->qr_code }}">
        <div class="nama">{{ $siswa->nama_lengkap }}</div>
        <div class="nis">NIS: {{ $siswa->nis }}</div>
        <div class="kelas">{{ optional($siswa->kelas)->nama ?? ($kelas ? $kelas->nama : '-') }}</div>
        <div class="sekolah">{{ $namaSekolah }}</div>
      </div>
    @endforeach
  </div>
</body>

</html>
