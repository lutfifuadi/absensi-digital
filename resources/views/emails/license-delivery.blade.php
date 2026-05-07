<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisensi & Link Download Aplikasi Absensi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 0; color: #333; }
        .container { max-width: 620px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #696cff 0%, #7367f0 100%); padding: 36px 40px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; font-weight: 700; }
        .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 14px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 16px; margin-bottom: 20px; }
        .license-box { background: #f0efff; border: 2px dashed #696cff; border-radius: 10px; padding: 20px 24px; margin: 24px 0; text-align: center; }
        .license-box .label { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #696cff; font-weight: 600; margin-bottom: 8px; }
        .license-box .key { font-family: 'Courier New', monospace; font-size: 22px; font-weight: 700; letter-spacing: 3px; color: #4a4593; word-break: break-all; }
        .steps { background: #f8f9fa; border-radius: 8px; padding: 20px 24px; margin: 24px 0; }
        .steps h3 { margin: 0 0 12px; font-size: 14px; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
        .steps ol { margin: 0; padding-left: 20px; }
        .steps li { margin-bottom: 8px; font-size: 14px; line-height: 1.6; }
        .steps code { background: #e9ecef; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-size: 12px; }
        .btn { display: inline-block; background: linear-gradient(135deg, #696cff, #7367f0); color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 8px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-row .info-label { color: #888; }
        .info-row .info-value { font-weight: 600; }
        .warning { background: #fff3e0; border-left: 4px solid #ff9800; padding: 14px 16px; border-radius: 0 8px 8px 0; font-size: 13px; color: #8a6000; margin: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px 40px; text-align: center; font-size: 12px; color: #aaa; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Lisensi Anda Sudah Siap!</h1>
            <p>{{ config('app.name') }} — Sistem Absensi Digital</p>
        </div>

        <div class="body">
            <p class="greeting">Halo <strong>{{ $namaKlien }}</strong>,</p>
            <p>Terima kasih atas kepercayaan Anda. Pembayaran Anda telah dikonfirmasi dan lisensi aplikasi sudah diterbitkan. Berikut adalah informasi lisensi dan tautan unduh Anda:</p>

            <div class="license-box">
                <div class="label">License Key Anda</div>
                <div class="key">{{ $licenseKey }}</div>
            </div>

            <div>
                <div class="info-row">
                    <span class="info-label">Domain Terdaftar</span>
                    <span class="info-value">{{ $domain ?: '(belum dikonfigurasi)' }}</span>
                </div>
                @if($expiresAt)
                <div class="info-row">
                    <span class="info-label">Berlaku Hingga</span>
                    <span class="info-value">{{ $expiresAt->format('d M Y') }}</span>
                </div>
                @else
                <div class="info-row">
                    <span class="info-label">Masa Aktif</span>
                    <span class="info-value">Seumur Hidup</span>
                </div>
                @endif
            </div>

            <br>
            <p style="text-align:center; font-size:15px;">
                <strong>Langkah 1:</strong> Unduh aplikasi menggunakan tombol di bawah
            </p>
            <div style="text-align:center; margin:16px 0;">
                <a href="{{ $downloadUrl }}" class="btn">⬇️ Unduh Aplikasi Absensi</a>
            </div>

            <div class="steps">
                <h3>📋 Langkah Instalasi</h3>
                <ol>
                    <li>Klik tombol <strong>Unduh Aplikasi</strong> di atas dan simpan file ZIP</li>
                    <li>Upload ke server Anda, ekstrak ke folder web (misal: <code>/var/www/absensi</code>)</li>
                    <li>Jalankan script instalasi: <code>bash install.sh</code></li>
                    <li>Buka browser → <code>http://domain-anda.com/install</code></li>
                    <li>Ikuti wizard instalasi — masukkan <strong>License Key</strong> di Step 2</li>
                    <li>Setelah selesai, aplikasi siap digunakan</li>
                </ol>
            </div>

            <div class="warning">
                ⚠️ <strong>Penting:</strong> License key ini bersifat rahasia dan terikat pada domain <strong>{{ $domain ?: 'yang Anda daftarkan' }}</strong>. Jangan bagikan ke pihak lain. Hubungi kami jika Anda perlu mengganti domain.
            </div>

            <p style="font-size:14px; color:#666;">Butuh bantuan? Balas email ini atau hubungi tim support kami. Kami siap membantu proses instalasi Anda.</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
            <p>Email ini dikirim secara otomatis, mohon jangan balas langsung.</p>
        </div>
    </div>
</body>
</html>
