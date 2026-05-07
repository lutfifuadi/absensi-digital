<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Aktivasi Lisensi — Sistem Absensi Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        :root {
            --bg:           #08080e;
            --surface:      #0f0f19;
            --surface-2:    #141420;
            --border:       rgba(255,255,255,0.07);
            --text:         #e8e8f0;
            --text-muted:   #8888a8;
            --accent:       #7c6cf5;
            --accent-light: #9d90ff;
            --danger:       #ef4444;
            --success:      #22c55e;
            --warning:      #f59e0b;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background-image:
                radial-gradient(ellipse 80% 55% at 10% 0%,   rgba(245,158,11,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 100%, rgba(124,108,245,0.07) 0%, transparent 55%),
                radial-gradient(ellipse 100% 80% at 50% 50%, rgba(124,108,245,0.02) 0%, transparent 70%);
        }

        .page-wrapper {
            width: 100%;
            max-width: 560px;
            animation: slide-up 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse-ring {
            0%   { transform: scale(1);   opacity: 0.6; }
            100% { transform: scale(1.6); opacity: 0; }
        }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 32px 64px rgba(0,0,0,0.5);
        }

        /* ── Hero ── */
        .hero {
            padding: 2.5rem 2.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 1rem;
            background: linear-gradient(135deg, rgba(124,108,245,0.04) 0%, transparent 60%);
        }

        .icon-wrap {
            position: relative;
            width: 72px; height: 72px;
            display: flex; align-items: center; justify-content: center;
        }

        .icon-wrap::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            background: rgba(245,158,11,0.15);
            animation: pulse-ring 1.8s ease-out infinite;
        }

        .icon-circle {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: rgba(245,158,11,0.1);
            border: 1.5px solid rgba(245,158,11,0.3);
            display: flex; align-items: center; justify-content: center;
            z-index: 1;
        }

        .icon-circle svg { color: #f59e0b; }

        .hero-title {
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        .hero-sub {
            font-size: 0.875rem;
            color: var(--text-muted);
            max-width: 380px;
            line-height: 1.6;
        }

        .feat-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }

        .feat-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.72rem;
            font-weight: 500;
            color: var(--text-muted);
            background: var(--surface-2);
            border: 1px solid var(--border);
            padding: 4px 10px;
            border-radius: 999px;
        }

        .feat-tag svg { color: var(--success); flex-shrink: 0; }

        /* ── Form Section ── */
        .form-section {
            padding: 2rem 2.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .section-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: -0.25rem;
        }

        .field-group { display: flex; flex-direction: column; gap: 0.4rem; }

        .field-label {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text);
        }

        .field-input {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
            color: var(--text);
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            width: 100%;
        }

        .field-input::placeholder { color: var(--text-muted); }

        .field-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(124,108,245,0.15);
        }

        .field-hint {
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        /* ── Alerts ── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.85rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.2);
            color: #86efac;
        }

        /* ── Button ── */
        .btn-activate {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--accent) 0%, #6457e8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-size: 0.92rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.15s;
            width: 100%;
            margin-top: 0.25rem;
        }

        .btn-activate:hover  { opacity: 0.9; transform: translateY(-1px); }
        .btn-activate:active { transform: translateY(0); }

        .btn-activate:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        /* ── Footer ── */
        .card-footer {
            padding: 1rem 2.5rem;
            border-top: 1px solid var(--border);
            font-size: 0.78rem;
            color: var(--text-muted);
            text-align: center;
            background: rgba(255,255,255,0.01);
        }

        @media (max-width: 600px) {
            body { padding: 1rem; }
            .hero, .form-section { padding-left: 1.5rem; padding-right: 1.5rem; }
            .card-footer { padding-left: 1.5rem; padding-right: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="card">

            {{-- Hero Section --}}
            <div class="hero">
                <div class="icon-wrap">
                    <div class="icon-circle">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                </div>

                <div>
                    <h1 class="hero-title">Aktivasi Lisensi Diperlukan</h1>
                </div>

                <p class="hero-sub">
                    Lisensi aplikasi belum diaktifkan atau telah dicabut.
                    Masukkan kunci lisensi dan domain yang terdaftar untuk melanjutkan.
                </p>

                <div class="feat-tags">
                    <span class="feat-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Data Aman
                    </span>
                    <span class="feat-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Verifikasi Instan
                    </span>
                    <span class="feat-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Aktivasi Sekali
                    </span>
                </div>
            </div>

            {{-- Form Section --}}
            <form class="form-section" method="POST" action="{{ route('license.activate') }}" id="license-form" autocomplete="off">
                @csrf

                <div class="section-label">Masukkan Detail Lisensi</div>

                {{-- Error alert --}}
                @if(session('error'))
                    <div class="alert alert-error" id="alert-err">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Success alert --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                {{-- License Key --}}
                <div class="field-group">
                    <label class="field-label" for="license_key">Kunci Lisensi</label>
                    <input
                        type="text"
                        id="license_key"
                        name="license_key"
                        class="field-input"
                        placeholder="XXXX-XXXX-XXXX-XXXX"
                        value="{{ old('license_key') }}"
                        required
                        minlength="5"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    @error('license_key')
                        <span class="field-hint" style="color:#fca5a5;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Registered Domain --}}
                <div class="field-group">
                    <label class="field-label" for="registered_domain">Domain Terdaftar</label>
                    <input
                        type="text"
                        id="registered_domain"
                        name="registered_domain"
                        class="field-input"
                        placeholder="contoh.sekolah.sch.id"
                        value="{{ old('registered_domain', request()->getHost()) }}"
                        required
                        minlength="3"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <span class="field-hint">Domain yang didaftarkan saat pembelian lisensi.</span>
                    @error('registered_domain')
                        <span class="field-hint" style="color:#fca5a5;">{{ $message }}</span>
                    @enderror
                </div>

                {{-- School Name --}}
                <div class="field-group">
                    <label class="field-label" for="school_name">Nama Sekolah</label>
                    <input
                        type="text"
                        id="school_name"
                        name="school_name"
                        class="field-input"
                        placeholder="SMP / SMA / SMK Nama Sekolah"
                        value="{{ old('school_name', \App\Models\Pengaturan::where('key','nama_sekolah')->value('value')) }}"
                        required
                        minlength="3"
                        autocomplete="off"
                    >
                    @error('school_name')
                        <span class="field-hint" style="color:#fca5a5;">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn-activate" id="btn-submit">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Aktifkan Lisensi
                </button>
            </form>

            <div class="card-footer">
                Butuh bantuan? Hubungi tim support kami.
                &nbsp;·&nbsp; Sistem Absensi Digital &copy; {{ date('Y') }}
            </div>
        </div>
    </div>

    <script>
        document.getElementById('license-form').addEventListener('submit', function() {
            var btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Memverifikasi...';
        });

        document.head.insertAdjacentHTML('beforeend', '<style>@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}</style>');
    </script>
</body>
</html>
