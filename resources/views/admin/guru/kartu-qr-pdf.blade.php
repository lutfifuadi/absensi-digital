<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Kartu QR Guru — {{ $namaSekolah }}</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      padding: 30px;
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 11px;
      color: #222;
    }

    .page-title {
      text-align: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #333;
    }

    .page-title h2 {
      font-size: 16px;
      margin-bottom: 2px;
    }

    .page-title p {
      font-size: 12px;
      color: #555;
    }

    /* Simple grid using table for PDF compatibility */
    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      padding: 10px;
      vertical-align: top;
      width: 33.33%;
    }

    .card {
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      text-align: center;
      page-break-inside: avoid;
    }

    .card img {
      width: 120px;
      height: 120px;
      margin-bottom: 6px;
    }

    .card .nama {
      font-size: 10px;
      font-weight: bold;
      line-height: 1.3;
      margin-bottom: 2px;
    }

    .card .nip {
      font-size: 9px;
      color: #555;
      margin-bottom: 1px;
    }

    .card .mapel {
      font-size: 9px;
      color: #777;
    }

    .card .sekolah {
      font-size: 8px;
      color: #999;
      margin-top: 5px;
      border-top: 1px dashed #ddd;
      padding-top: 4px;
    }
  </style>
</head>

<body>
  <div class="page-title">
    <h2>{{ $namaSekolah }}</h2>
    <p>Kartu Absensi QR Code Guru</p>
  </div>

  <table>
    @foreach ($guruList->chunk(3) as $row)
      <tr>
        @foreach ($row as $guru)
          <td>
            <div class="card">
              <div style="margin-bottom: 6px;">
                @if(is_array($qrImages[$guru->id] ?? null))
                  <div style="display: inline-block; text-align: center; margin: 0 3px;">
                    <img src="{{ $qrImages[$guru->id]['unik'] }}" alt="QR Unik" style="width: 75px; height: 75px; margin-bottom:2px;">
                    <div style="font-size: 7px; color: #444; font-weight: bold;">QR ID Unik</div>
                  </div>
                  <div style="display: inline-block; text-align: center; margin: 0 3px;">
                    <img src="{{ $qrImages[$guru->id]['nip'] }}" alt="QR NIP" style="width: 75px; height: 75px; margin-bottom:2px;">
                    <div style="font-size: 7px; color: #444; font-weight: bold;">QR NIP</div>
                  </div>
                @else
                  <img src="{{ $qrImages[$guru->id] }}" alt="QR {{ $guru->qr_code }}">
                @endif
              </div>
              <div class="nama">{{ $guru->nama_lengkap }}</div>
              <div class="nip">NIP: {{ $guru->nip }}</div>
              <div class="mapel">{{ $guru->mata_pelajaran }}</div>
              <div class="sekolah">{{ $namaSekolah }}</div>
            </div>
          </td>
        @endforeach
        @if ($row->count() < 3)
           @for ($i = 0; $i < (3 - $row->count()); $i++)
             <td></td>
           @endfor
        @endif
      </tr>
    @endforeach
  </table>
</body>

</html>
