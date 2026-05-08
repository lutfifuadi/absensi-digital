<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Web Installer - Sistem Absensi Digital">
    <title>Setup Wizard — Sistem Absensi Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;1,14..32,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* ── Reset ── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; }

        /* ── Design Tokens ── */
        :root {
            --bg:           #08080e;
            --surface:      #0f0f19;
            --surface-2:    #141420;
            --surface-3:    #1a1a2a;
            --border:       rgba(255,255,255,0.06);
            --border-2:     rgba(255,255,255,0.10);
            --border-3:     rgba(255,255,255,0.15);

            --primary:      #7c6cf5;
            --primary-h:    #6b5ce7;
            --primary-dim:  rgba(124,108,245,0.1);
            --primary-glow: rgba(124,108,245,0.3);

            --success:      #22c55e;
            --success-dim:  rgba(34,197,94,0.1);
            --danger:       #ef4444;
            --danger-dim:   rgba(239,68,68,0.1);
            --info:         #818cf8;
            --info-dim:     rgba(129,140,248,0.08);

            --text:         #f0f4ff;
            --text-2:       #c4cde0;
            --text-muted:   #5f6b82;
            --text-sub:     #8898aa;

            --r:            4px;
            --r-sm:         2px;
        }

        /* ── Base ── */
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
                radial-gradient(ellipse 70% 50% at 15% 5%,  rgba(124,108,245,0.07) 0%, transparent 60%),
                radial-gradient(ellipse 55% 40% at 85% 95%, rgba(34,197,94,0.04) 0%,  transparent 55%),
                radial-gradient(ellipse 90% 70% at 50% 50%, rgba(124,108,245,0.02) 0%, transparent 70%);
        }

        /* Prevent body scroll on PC */
        @media (min-width: 768px) {
            html, body { 
                height: 100vh;
                overflow: hidden; 
            }
            .page-wrapper {
                height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }

        /* ── Wrapper ── */
        .page-wrapper {
            width: 100%;
            max-width: 600px;
        }

        /* ── Brand (Inside Card) ── */
        .card-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 22px;
            background: rgba(255,255,255,0.02);
            border-bottom: 1px solid var(--border);
        }
        .brand-mark {
            width: 38px; height: 38px;
            border-radius: var(--r);
            background: linear-gradient(145deg, var(--primary) 0%, #a78bfa 100%);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 0 1px rgba(124,108,245,0.3), 0 4px 12px rgba(124,108,245,0.2);
            flex-shrink: 0;
        }
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
            line-height: 1.2;
        }
        .brand-tag {
            font-size: 0.6875rem;
            color: var(--text-muted);
            font-weight: 400;
            margin-top: 1px;
        }

        /* ── Global Error ── */
        .global-error {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: var(--danger-dim);
            border: 1px solid rgba(239,68,68,0.18);
            border-radius: var(--r);
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: 0.8125rem;
            color: #fca5a5;
            line-height: 1.5;
            animation: slide-down 0.4s ease 0.05s both;
        }
        .global-error svg { flex-shrink: 0; margin-top: 1px; }

        /* ── Card ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            box-shadow:
                0 1px 0 rgba(255,255,255,0.04) inset,
                0 -1px 0 rgba(0,0,0,0.3) inset,
                0 4px 6px -1px rgba(0,0,0,0.3),
                0 20px 48px -8px rgba(0,0,0,0.6);
            overflow: hidden;
            animation: slide-up 0.45s ease 0.08s both;
            display: flex;
            flex-direction: column;
        }

        @media (min-width: 768px) {
            .card {
                max-height: 600px; /* Force a proportional fixed height for better PC experience */
            }
        }

        /* ── Card Header ── */
        .card-head {
            padding: 18px 22px 20px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(160deg, rgba(124,108,245,0.05) 0%, rgba(124,108,245,0) 100%);
            position: relative;
        }
        .card-head::before {
            content: '';
            position: absolute;
            top: 0; left: 22px; right: 22px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(124,108,245,0.3), transparent);
        }

        .step-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .step-chip {
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--primary);
            background: var(--primary-dim);
            border: 1px solid rgba(124,108,245,0.2);
            border-radius: var(--r-sm);
            padding: 3px 9px;
        }
        .step-of {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-variant-numeric: tabular-nums;
        }
        .step-of strong { color: var(--text-sub); font-weight: 600; }

        /* Progress bar */
        .prog-track {
            height: 2px;
            background: var(--border-2);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .prog-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, #a78bfa 100%);
            border-radius: 2px;
            box-shadow: 0 0 6px var(--primary-glow);
            transition: width 0.7s cubic-bezier(0.4,0,0.2,1);
        }

        .head-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
            line-height: 1.3;
        }
        .head-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 5px;
            line-height: 1.55;
        }

        /* ── Card Body ── */
        .card-body { 
            padding: 22px; 
            overflow-y: auto;
            flex: 1;
            min-height: 280px;
        }

        /* Custom Scrollbar for Card Body */
        .card-body::-webkit-scrollbar { width: 6px; }
        .card-body::-webkit-scrollbar-track { background: transparent; }
        .card-body::-webkit-scrollbar-thumb { background: var(--border-3); border-radius: 10px; }
        .card-body::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        /* ── Card Footer ── */
        .card-foot {
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            background: rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .foot-l, .foot-r { display: flex; gap: 8px; align-items: center; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--r);
            font-size: 0.8125rem;
            font-weight: 600;
            font-family: inherit;
            line-height: 1;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: background 0.15s, box-shadow 0.15s, transform 0.15s, opacity 0.15s;
            white-space: nowrap;
            vertical-align: middle;
        }
        .btn:disabled, .btn[disabled] { opacity: 0.45; cursor: not-allowed !important; pointer-events: none; }
        .btn svg { flex-shrink: 0; }

        .btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
            box-shadow: 0 1px 2px rgba(0,0,0,0.3), 0 2px 10px rgba(124,108,245,0.25);
        }
        .btn-primary:hover { background: var(--primary-h); box-shadow: 0 2px 4px rgba(0,0,0,0.4), 0 4px 16px rgba(124,108,245,0.4); transform: translateY(-1px); }
        .btn-primary:active { transform: translateY(0); }

        .btn-success {
            background: var(--success);
            color: #fff;
            border-color: var(--success);
            box-shadow: 0 1px 2px rgba(0,0,0,0.3), 0 2px 10px rgba(34,197,94,0.2);
        }
        .btn-success:hover { background: #16a34a; box-shadow: 0 2px 4px rgba(0,0,0,0.4), 0 4px 16px rgba(34,197,94,0.35); transform: translateY(-1px); }

        .btn-ghost {
            background: transparent;
            color: var(--text-sub);
            border-color: var(--border-2);
        }
        .btn-ghost:hover { background: var(--surface-2); color: var(--text-2); border-color: var(--border-3); }

        .btn-wide { justify-content: center; width: 100%; }

        /* ── Notices ── */
        .notice {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 11px 14px;
            border-radius: var(--r);
            font-size: 0.8125rem;
            line-height: 1.55;
            border: 1px solid;
        }
        .notice svg { flex-shrink: 0; margin-top: 1px; }

        .notice-err  { background: var(--danger-dim);  border-color: rgba(239,68,68,0.18);  color: #fca5a5; }
        .notice-ok   { background: var(--success-dim); border-color: rgba(34,197,94,0.18);  color: #86efac; }
        .notice-info { background: var(--info-dim);    border-color: rgba(129,140,248,0.18); color: #a5b4fc; }

        /* ── Requirement List ── */
        .req-stack {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .req-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 13px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--r);
            gap: 10px;
            transition: background 0.12s;
        }
        .req-row:hover { background: var(--surface-3); }
        .req-name {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-2);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: var(--r-sm);
            font-size: 0.625rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            border: 1px solid;
            flex-shrink: 0;
            line-height: 1.6;
        }
        .badge-ok   { background: var(--success-dim); color: var(--success); border-color: rgba(34,197,94,0.22); }
        .badge-fail { background: var(--danger-dim);  color: var(--danger);  border-color: rgba(239,68,68,0.22); }

        /* ── Form ── */
        .field { display: flex; flex-direction: column; gap: 5px; }
        .field + .field { margin-top: 14px; }
        .grid-2   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .grid-3-1 { display: grid; grid-template-columns: 3fr 1fr; gap: 12px; }
        .grid-2 .field, .grid-3-1 .field { margin-top: 0; }

        label.lbl {
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: var(--text-muted);
        }

        .inp-wrap { position: relative; }
        .inp-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            display: flex;
            pointer-events: none;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            display: block;
            width: 100%;
            background: var(--surface-3);
            border: 1px solid var(--border-2);
            color: var(--text);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 9px 12px;
            border-radius: var(--r);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
            -webkit-appearance: none;
        }
        .has-icon input { padding-left: 34px; }
        input::placeholder { color: var(--text-muted); font-weight: 400; }
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,108,245,0.14);
            background: rgba(124,108,245,0.04);
        }
        input:hover:not(:focus) { border-color: var(--border-3); }
        
        /* ── Autofill Override ── */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active  {
            -webkit-text-fill-color: var(--text) !important;
            -webkit-box-shadow: 0 0 0 30px var(--surface-3) inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* ── Section Label ── */
        .section-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 18px 0 14px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
        }
        .section-label::before,
        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ── Spinner ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin {
            display: inline-block;
            width: 13px; height: 13px;
            border: 2px solid rgba(255,255,255,0.25);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            flex-shrink: 0;
        }

        /* ── Keyframes ── */
        @keyframes slide-up {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes slide-down {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Utilities ── */
        .mt-4  { margin-top: 4px; }
        .mt-12 { margin-top: 12px; }
        .mt-16 { margin-top: 16px; }
        .mt-18 { margin-top: 18px; }
        .mb-0  { margin-bottom: 0; }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .grid-2, .grid-3-1 { grid-template-columns: 1fr; }
            .card-foot { flex-wrap: wrap; }
            .btn-wide { width: 100%; }
        }
        /* ── Select2 Custom Premium Dark ── */
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single {
            background: var(--surface-3);
            border: 1px solid var(--border-2);
            border-radius: var(--r);
            height: 38px;
            display: flex;
            align-items: center;
            transition: all 0.15s;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--text);
            font-size: 0.875rem;
            padding-left: 12px;
            font-weight: 500;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
            right: 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: var(--text-muted) transparent transparent transparent;
        }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124,108,245,0.14);
            background: rgba(124,108,245,0.04);
        }
        .select2-dropdown {
            background: var(--surface-2);
            border: 1px solid var(--border-3);
            border-radius: var(--r);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            margin-top: 4px;
            overflow: hidden;
            z-index: 9999;
            left: 0 !important;
            width: 100% !important;
            animation: slide-up 0.2s ease;
        }
        .select2-results__option {
            padding: 8px 12px;
            font-size: 0.875rem;
            color: var(--text-2);
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary);
            color: #fff;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--primary-dim);
            color: var(--primary);
        }
        .select2-search--dropdown { padding: 8px; background: var(--surface-2); }
        .select2-search--dropdown .select2-search__field {
            background: var(--surface-3);
            border: 1px solid var(--border-2);
            color: var(--text);
            border-radius: var(--r-sm);
            padding: 6px 10px;
        }
    </style>
</head>
<body>

<div class="page-wrapper">

    <!-- Global Error -->
    @if(session('error'))
    <div class="global-error">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Card -->
    <div class="card">
        <!-- Brand Integration -->
        <div class="card-brand">
            <div class="brand-mark">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="#fff" stroke-width="1.75" stroke-linecap="round"/>
                    <rect x="9" y="3" width="6" height="4" rx="1" stroke="#fff" stroke-width="1.75"/>
                    <path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="brand-text">
                <div class="brand-name">Sistem Absensi Digital</div>
                <div class="brand-tag">Web Installer · Setup Wizard</div>
            </div>
        </div>

        <!-- Header -->
        <div class="card-head">
            <div class="step-top">
                <span class="step-chip">@yield('step-chip', 'Setup')</span>
                <span class="step-of">Langkah <strong>@yield('step-num', '1')</strong> dari 5</span>
            </div>
            <div class="prog-track">
                <div class="prog-fill" style="width:@yield('progress','33')%"></div>
            </div>
            <div class="head-title">@yield('step-title')</div>
            <div class="head-desc">@yield('step-desc')</div>
        </div>

        <!-- Body + Form -->
        <form action="@yield('form-action','#')" method="POST" id="installer-form" autocomplete="off" style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
            @csrf
            <div class="card-body">@yield('content')</div>
            <div class="card-foot">
                <div class="foot-l">@yield('foot-l')</div>
                <div class="foot-r">@yield('foot-r')</div>
            </div>
        </form>

    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').each(function() {
        var $this = $(this);
        $this.wrap('<div class="select2-wrapper" style="position:relative;"></div>').select2({
            width: '100%',
            dropdownParent: $this.parent(),
            minimumResultsForSearch: 10
        });
    });
});
</script>
<script>
$(document).ready(function() {
    // Publish Livewire Assets button (step 3, 4, 5)
    $(document).on('click', '.btn-publish-assets', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spin"></span> Memproses...');
        $.ajax({
            url: '{{ route("installer.publishAssets") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(data) {
                alert(data.success ? '✓ ' + data.message : '✗ ' + data.message);
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghubungi server.';
                alert('✗ ' + msg);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg> Livewire Assets');
            }
        });
    });
});
$(document).ready(function() {
    var $form = $('#installer-form');
    var $btn = $('#btn-submit');

    // 1. Submit Listener (Loading State)
    $form.on('submit', function() {
        if ($form.attr('id') === 'installer-form') { // Standard form submit
            $btn.prop('disabled', true);
            var label = $btn.find('[data-label]');
            var icon = $btn.find('[data-icon]');
            if (label.length) label.text($btn.data('loading') || 'Memproses...');
            if (icon.length) icon.html('<span class="spin"></span>');
        }
    });

    // 2. Back Button Listener (Save Progress)
    $('.btn-ghost').on('click', function(e) {
        var $this = $(this);
        
        if ($form.length && $this.attr('href')) {
            e.preventDefault();
            var targetUrl = $this.attr('href');
            
            $this.addClass('disabled').css('pointer-events', 'none');
            
            $.ajax({
                url: '{{ route("installer.saveProgress") }}',
                method: 'POST',
                data: $form.serialize(),
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                complete: function() {
                    window.location.href = targetUrl;
                }
            });
        }
    });
});
</script>
    @yield('scripts')
</body>
</html>
