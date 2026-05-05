<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Absensi - {{ $guru->nama_lengkap }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Helvetica', sans-serif; background: #fff; width: 8cm; height: 10cm; }
        .card {
            width: 8cm;
            height: 10cm;
            border: 1px solid #eee;
            position: relative;
            text-align: center;
            padding: 15px;
        }
        .header {
            border-bottom: 1.5px solid #333;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h2 { font-size: 14px; color: #111; text-transform: uppercase; }
        .qr-section { margin: 20px 0; }
        .qr-section img { width: 4.5cm; height: 4.5cm; }
        .info { margin-top: 10px; }
        .info .nama { font-size: 12px; font-weight: bold; color: #000; margin-bottom: 4px; }
        .info .nip { font-size: 11px; color: #444; }
        .info .role { 
            display: inline-block; 
            margin-top: 8px; 
            background: #eee; 
            padding: 2px 10px; 
            border-radius: 10px; 
            font-size: 10px; 
            font-weight: bold;
            color: #333;
        }
        .footer {
            position: absolute;
            bottom: 15px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h2>{{ $namaSekolah }}</h2>
        </div>
        
        <div class="qr-section">
            <img src="{{ $qrImage }}" alt="QR Code">
        </div>

        <div class="info">
            <div class="nama">{{ $guru->nama_lengkap }}</div>
            <div class="nip">NIP: {{ $guru->nip }}</div>
            <div class="role">GURU / TENAGA PENDIDIK</div>
        </div>

        <div class="footer">
            Kartu ini digunakan untuk presensi harian.<br>
            Harap simpan dan jaga kartu tetap bersih.
        </div>
    </div>
</body>
</html>
