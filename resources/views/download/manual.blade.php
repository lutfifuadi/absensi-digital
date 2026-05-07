<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Download Manual — {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f4f6fb; margin:0; padding: 40px 20px; color:#333; }
        .container { max-width:640px; margin:0 auto; background:#fff; border-radius:12px; padding:40px; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
        h1 { color:#7367f0; font-size:22px; margin-bottom:24px; }
        .steps { background:#f8f9fa; border-radius:8px; padding:20px; }
        .steps ol li { margin-bottom:10px; line-height:1.6; }
        code { background:#e9ecef; padding:2px 6px; border-radius:4px; font-family:monospace; font-size:13px; }
    </style>
</head>
<body>
<div class="container">
    @if(session('warning'))
        <div style="background:#fff3e0; border-left:4px solid #ff9800; padding:12px 16px; border-radius:0 8px 8px 0; margin-bottom:24px; font-size:14px; color:#8a6000;">
            ⚠️ {{ session('warning') }}
        </div>
    @endif

    <h1>📥 Panduan Download Manual</h1>
    <p>Link otomatis tidak tersedia saat ini. Silakan ikuti langkah berikut untuk mendapatkan file aplikasi:</p>

    <div class="steps">
        <ol>
            <li>Hubungi tim support via email atau WhatsApp yang tertera di email lisensi Anda.</li>
            <li>Sebutkan <strong>license key</strong> Anda untuk verifikasi.</li>
            <li>Kami akan mengirimkan link download secara manual dalam waktu 1x24 jam.</li>
            <li>Setelah mendapat file ZIP, ekstrak ke server Anda dan jalankan <code>bash install.sh</code>.</li>
            <li>Akses <code>http://domain-anda.com/install</code> untuk melanjutkan instalasi.</li>
        </ol>
    </div>

    <p style="margin-top:24px; font-size:14px; color:#888;">
        Mohon maaf atas ketidaknyamanan ini. Tim kami siap membantu Anda.
    </p>
</div>
</body>
</html>
