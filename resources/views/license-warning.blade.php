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
            --surface-3:    #1a1a2a;
            --border:       rgba(255,255,255,0.06);
            --border-2:     rgba(255,255,255,0.10);
            --border-3:     rgba(255,255,255,0.16);

            --primary:      #7c6cf5;
            --primary-h:    #6b5ce7;
            --primary-dim:  rgba(124,108,245,0.12);
            --primary-glow: rgba(124,108,245,0.35);

            --warning:      #f59e0b;
            --warning-dim:  rgba(245,158,11,0.10);
            --warning-glow: rgba(245,158,11,0.25);

            --success:      #22c55e;
            --success-dim:  rgba(34,197,94,0.10);

            --danger:       #ef4444;
            --danger-dim:   rgba(239,68,68,0.10);

            --info-dim:     rgba(129,140,248,0.08);

            --text:         #f0f4ff;
            --text-2:       #c4cde0;
            --text-muted:   #5f6b82;
            --text-sub:     #8898aa;

            --r:  5px;
            --r-sm: 3px;
            --r-lg: 10px;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
            background-image:
                radial-gradient(ellipse 80% 55% at 10% 0%,   rgba(245,158,11,0.06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 90% 100%, rgba(124,108,245,0.07) 0%, transparent 55%),
                radial-gradient(ellipse 100% 80% at 50% 50%, rgba(124,108,245,0.02) 0%, transparent 70%);
        }

        /* ── Wrapper ── */
        .page-wrapper {
            width: 100%;
            max-width: 560px; /* Slightly wider for more space */
            animation: slide-up 0.5s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-ring {
            0%   { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245,158,11,0.5); }
            70%  { transform: scale(1);    box-shadow: 0 0 0 14px rgba(245,158,11,0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(245,158,11,0); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%  { transform: translateX(-6px); }
            40%  { transform: translateX(6px); }
            60%  { transform: translateX(-4px); }
            80%  { transform: translateX(4px); }
        }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px; /* Smoother radius */
            box-shadow:
                0 1px 0 rgba(255,255,255,0.04) inset,
                0 -1px 0 rgba(0,0,0,0.3) inset,
                0 4px 6px -1px rgba(0,0,0,0.35),
                0 32px 80px -12px rgba(0,0,0,0.8);
            overflow: hidden;
        }

        /* ── Header Inside Card ── */
        .warn-header {
            background: linear-gradient(135deg, rgba(245,158,11,0.12) 0%, rgba(245,158,11,0.04) 100%);
            border-bottom: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }
        .warn-header::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--warning) 50%, transparent 100%);
            opacity: 0.8;
        }

        .header-grid {
            display: grid;
            grid-template-columns: 110px 1fr; /* Narrow left for icon only */
            border-bottom: 1px solid var(--border);
            background: rgba(0,0,0,0.25);
        }

        .header-left {
            display: flex;
            align-items: center;
            justify-content: center; /* Center the icon */
            padding: 20px;
            border-right: 1px solid var(--border);
        }
        .header-right {
            padding: 18px 24px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: left;
        }

        .header-bottom {
            padding: 16px 28px 24px; /* More compact */
            text-align: center;
            position: relative;
        }

        .brand-mark {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: linear-gradient(145deg, var(--primary) 0%, #a78bfa 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 0 1px rgba(124,108,245,0.3), 0 8px 20px rgba(124,108,245,0.25);
            flex-shrink: 0;
        }

        .warn-title {
            font-size: 1.1rem;
            font-weight: 850;
            color: var(--text);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }
        .warn-subtitle {
            font-size: 0.85rem;
            color: var(--text-sub);
            line-height: 1.5;
            max-width: 320px;
        }

        /* ── Status Pill ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245,158,11,0.1);
            border: 1px solid rgba(245,158,11,0.22);
            border-radius: 99px;
            padding: 5px 12px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--warning);
            margin-bottom: 10px;
            width: fit-content;
        }
        .status-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--warning);
            box-shadow: 0 0 0 3px rgba(245,158,11,0.25);
            animation: pulse-ring 1.5s ease-in-out infinite;
        }

        /* ── Form Section ── */
        .form-section {
            padding: 24px 28px 28px;
        }

        .section-label {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-2);
        }

        /* Alert flash messages */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 0.8125rem;
            line-height: 1.55;
            border: 1px solid;
            margin-bottom: 20px;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-error   { background: var(--danger-dim);  border-color: rgba(239,68,68,0.18);  color: #fca5a5; animation: shake 0.45s ease; }
        .alert-success { background: var(--success-dim); border-color: rgba(34,197,94,0.18);  color: #86efac; }

        /* ── Fields ── */
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field + .field { margin-top: 18px; }
        .field-group { display: grid; gap: 18px; }

        label.lbl {
            font-size: 0.7rem;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .inp-wrap { position: relative; }
        .inp-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            display: flex;
            pointer-events: none;
            transition: color 0.2s;
        }

        input[type="text"] {
            display: block;
            width: 100%;
            background: var(--surface-2);
            border: 1px solid var(--border-2);
            color: var(--text);
            font-family: inherit;
            font-size: 0.9375rem;
            font-weight: 500;
            padding: 12px 14px;
            border-radius: 10px;
            outline: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            -webkit-appearance: none;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .has-icon input[type="text"] { padding-left: 42px; }
        input::placeholder { color: var(--text-muted); font-weight: 400; opacity: 0.6; }
        
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-dim), 0 4px 12px rgba(0,0,0,0.2);
            background: var(--surface-3);
        }
        input:focus + .inp-icon, 
        .inp-wrap:focus-within .inp-icon {
            color: var(--primary);
        }

        input.is-warning:focus {
            border-color: var(--warning);
            box-shadow: 0 0 0 4px var(--warning-dim);
        }

        .field-hint {
            font-size: 0.72rem;
            color: var(--text-muted);
            line-height: 1.5;
            margin-top: 2px;
        }

        /* ── Submit Button ── */
        .btn-activate {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px 18px;
            margin-top: 12px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--primary) 0%, #9d8df8 100%);
            color: #fff;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            cursor: pointer;
            box-shadow:
                0 1px 0 rgba(255,255,255,0.12) inset,
                0 2px 4px rgba(0,0,0,0.3),
                0 6px 20px rgba(124,108,245,0.30);
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.15s;
            position: relative;
            overflow: hidden;
        }
        .btn-activate::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.08) 100%);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .btn-activate:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow:
                0 1px 0 rgba(255,255,255,0.12) inset,
                0 4px 8px rgba(0,0,0,0.4),
                0 10px 28px rgba(124,108,245,0.45);
        }
        .btn-activate:hover::before { opacity: 1; }
        .btn-activate:active:not(:disabled) { transform: translateY(0); }
        .btn-activate:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* Loading spinner */
        .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            display: none;
            flex-shrink: 0;
        }

        /* ── Info Footer ── */
        .info-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 24px 32px; /* Much more padding */
            border-top: 1px solid var(--border);
            background: rgba(0,0,0,0.2);
            font-size: 0.8125rem;
            color: var(--text-muted);
        }
        .info-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .info-footer a:hover { text-decoration: underline; }
        .dot-sep { opacity: 0.4; }
 
        /* ── Features strip ── */
        .features-strip {
            display: flex;
            gap: 8px;
            margin-top: 12px; /* Much more compact */
            flex-wrap: wrap;
            justify-content: center;
        }
        .feat-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.725rem;
            font-weight: 600;
            color: var(--text-sub);
        }
        .feat-tag svg { color: var(--primary); }

        /* ── Responsive ── */
        @media (max-width: 540px) {
            .header-grid {
                grid-template-columns: 1fr;
            }
            .header-left {
                border-right: none;
                border-bottom: 1px solid var(--border);
                padding: 22px 24px;
            }
            .header-right {
                padding: 22px 24px;
            }
            .form-section { padding: 24px 24px 28px; }
            .info-footer { padding: 18px 24px; }
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <div class="card">

        {{-- Integrated 2-Column Header --}}
        <div class="warn-header">
            <div class="header-grid">
                {{-- Left Column: Brand Icon --}}
                <div class="header-left">
                    <div class="brand-mark">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>

                {{-- Right Column: Warning Info --}}
                <div class="header-right">
                    <div class="status-pill">
                        <div class="status-dot"></div>
                        Lisensi Diperlukan
                    </div>

                    <div class="warn-title">Aktivasi Lisensi Diperlukan</div>
                    <p class="warn-subtitle">
                        Sistem tidak dapat diakses karena lisensi belum diaktifkan.
                    </p>
                </div>
            </div>

            {{-- Centered Tags below columns --}}
            <div class="header-bottom">
                <div class="features-strip">
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

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="alert alert-error">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- License Key Field --}}
            <div class="field">
                <label class="lbl" for="license_key">Kode Lisensi</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="license_key"
                        name="license_key"
                        value="{{ old('license_key') }}"
                        placeholder="LIC-XXXX-XXXX-XXXX"
                        required
                        autofocus
                        autocomplete="off"
                        spellcheck="false"
                        class="{{ $errors->has('license_key') ? 'is-warning' : '' }}"
                        oninput="this.value = this.value.toUpperCase()"
                    >
                </div>
                <div class="field-hint">Kode lisensi diberikan saat pembelian produk. Pastikan diisi dengan tepat.</div>
            </div>

            {{-- Domain Field --}}
            <div class="field">
                <label class="lbl" for="registered_domain">Domain Terdaftar</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="registered_domain"
                        name="registered_domain"
                        value="{{ old('registered_domain', request()->getHost()) }}"
                        placeholder="contoh.com"
                        required
                        autocomplete="off"
                        spellcheck="false"
                        class="{{ $errors->has('registered_domain') ? 'is-warning' : '' }}"
                    >
                </div>
                <div class="field-hint">Domain harus sesuai dengan yang terdaftar di sistem pusat lisensi.</div>
            </div>

            {{-- Submit Button --}}
            <button type="submit" class="btn-activate" id="btn-submit">
                <div class="spinner" id="spinner"></div>
                <svg id="btn-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span id="btn-text">Aktivasi &amp; Buka Akses</span>
            </button>
        </form>

        {{-- Footer --}}
        <div class="info-footer">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Butuh bantuan?
            <a href="mailto:support@lutfifuadi.my.id">Hubungi Support</a>
            <span class="dot-sep">·</span>
            <a href="https://saas-presensi.lutfifuadi.my.id" target="_blank" rel="noopener">Portal Lisensi</a>
        </div>
    </div>

</div>

<script>
    (function () {
        const form    = document.getElementById('license-form');
        const btn     = document.getElementById('btn-submit');
        const spinner = document.getElementById('spinner');
        const icon    = document.getElementById('btn-icon');
        const text    = document.getElementById('btn-text');

        form.addEventListener('submit', function (e) {
            const licenseVal = document.getElementById('license_key').value.trim();
            const domainVal  = document.getElementById('registered_domain').value.trim();

            if (!licenseVal || !domainVal) return;

            btn.disabled     = true;
            spinner.style.display = 'block';
            icon.style.display    = 'none';
            text.textContent      = 'Memverifikasi lisensi…';
        });
    })();
</script>

</body>
</html>
