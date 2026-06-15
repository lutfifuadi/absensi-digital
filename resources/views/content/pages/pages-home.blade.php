@php
  $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Digital Attendance System — ' . $namaSekolah)

@php
  $pageConfigs = ['myLayout' => 'front'];
@endphp

@section('content')
  <link rel="stylesheet" href="{{ asset('assets/css/local-fonts.css') }}">

  <style>
    /* ─── CSS Variables ──────────────────────────────────────────── */
    :root {
      --bg: #07090f;
      --surface: #0d1120;
      --surface2: #121829;
      --border: rgba(255, 255, 255, 0.07);
      --primary: #6c63ff;
      --primary-glow: rgba(108, 99, 255, 0.18);
      --info: #22d3ee;
      --gold: #e2b96f;
      --text: #e8eaf0;
      --muted: #5a6478;
      --muted2: #8892a4;
      --radius: 14px;
    }

    /* ─── Base ───────────────────────────────────────────────────── */
    body {
      background-color: var(--bg);
      color: var(--text);
      font-family: 'Product Sans', sans-serif;
      overflow-x: hidden;
    }

    /* ─── Trajan helper class ────────────────────────────────────── */
    .trajan {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      letter-spacing: 0.04em;
    }

    /* ─── NAVBAR ─────────────────────────────────────────────────── */

    /* ─── HERO ───────────────────────────────────────────────────── */
    .section-hero {
      padding: 80px 0 72px;
      background:
        radial-gradient(ellipse at 6% 50%, rgba(108, 99, 255, .10) 0%, transparent 52%),
        radial-gradient(ellipse at 96% 20%, rgba(34, 211, 238, .05) 0%, transparent 45%),
        var(--bg);
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(108, 99, 255, 0.12);
      border: 0.5px solid rgba(108, 99, 255, 0.35);
      color: #a89ff7;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: 99px;
      margin-bottom: 18px;
    }

    .badge-dot {
      width: 6px;
      height: 6px;
      background: var(--primary);
      border-radius: 50%;
      flex-shrink: 0;
      animation: pulseAnim 1.8s ease-in-out infinite;
    }

    @keyframes pulseAnim {

      0%,
      100% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: .6;
        transform: scale(1.4);
      }
    }

    .hero-eyeline {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: 11px;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 12px;
      font-weight: 400;
    }

    .hero-title {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: clamp(1.9rem, 3vw, 2.8rem);
      font-weight: 400;
      line-height: 1.2;
      color: #fff;
      letter-spacing: 0.01em;
      margin-bottom: 8px;
    }

    .hero-title-sub {
      font-family: 'Product Sans', sans-serif;
      font-size: clamp(1.4rem, 2vw, 1.9rem);
      font-weight: 700;
      background: linear-gradient(90deg, #a89ff7 0%, #22d3ee 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 20px;
      line-height: 1.3;
    }

    .hero-subtitle {
      font-size: 13.5px;
      font-weight: 500;
      color: var(--muted2);
      line-height: 1.8;
      max-width: 480px;
      margin-bottom: 28px;
    }

    /* Buttons */
    .btn-primary-live {
      background: var(--primary);
      color: #fff !important;
      font-family: 'Product Sans', sans-serif;
      font-size: 13px;
      font-weight: 700;
      padding: 11px 26px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      box-shadow: 0 6px 20px rgba(108, 99, 255, .35);
      transition: all .22s ease;
      white-space: nowrap;
    }

    .btn-primary-live:hover {
      background: #5b53e8;
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(108, 99, 255, .45);
      color: #fff !important;
    }

    .btn-ghost-live {
      background: transparent;
      color: var(--text) !important;
      font-family: 'Product Sans', sans-serif;
      font-size: 13px;
      font-weight: 700;
      padding: 11px 22px;
      border: 0.5px solid rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      transition: all .22s ease;
      white-space: nowrap;
    }

    .btn-ghost-live:hover {
      background: rgba(255, 255, 255, .06);
      border-color: rgba(255, 255, 255, .25);
      color: #fff !important;
      transform: translateY(-2px);
    }

    .hero-trust {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 22px;
      font-size: 10.5px;
      font-weight: 600;
      color: var(--muted);
      letter-spacing: 0.06em;
      text-transform: uppercase;
    }

    .hero-trust-divider {
      flex: 1;
      height: 0.5px;
      background: var(--border);
      border: none;
      margin: 0;
    }

    /* ─── Hero Mockup Card ───────────────────────────────────────── */
    .hero-card-wrap {
      border-radius: 18px;
      border: 0.5px solid var(--border);
      background: var(--surface);
      overflow: hidden;
      animation: floatAnim 8s ease-in-out infinite;
    }

    @keyframes floatAnim {

      0%,
      100% {
        transform: translateY(0);
      }

      50% {
        transform: translateY(-14px);
      }
    }

    .hero-card-header {
      background: var(--surface2);
      border-bottom: 0.5px solid var(--border);
      padding: 13px 18px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .wdot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }

    .wdot-r {
      background: #ff5f57;
    }

    .wdot-y {
      background: #febc2e;
    }

    .wdot-g {
      background: #28c840;
    }

    .card-header-label {
      margin-left: auto;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--muted2);
    }

    .live-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: rgba(34, 211, 238, 0.1);
      border: 0.5px solid rgba(34, 211, 238, 0.28);
      color: var(--info);
      font-size: 9px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 99px;
      margin-left: 8px;
    }

    .live-pill-dot {
      width: 5px;
      height: 5px;
      background: var(--info);
      border-radius: 50%;
      animation: pulseAnim 1.2s ease-in-out infinite;
    }

    .hero-card-body {
      padding: 18px;
    }

    .scan-section-label {
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 10px;
    }

    .scan-row {
      display: flex;
      align-items: center;
      gap: 11px;
      background: rgba(108, 99, 255, 0.05);
      border: 0.5px solid rgba(108, 99, 255, 0.16);
      border-radius: 10px;
      padding: 11px 13px;
      margin-bottom: 8px;
    }

    .scan-row:last-of-type {
      margin-bottom: 0;
    }

    .scan-avatar {
      width: 36px;
      height: 36px;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }

    .scan-name {
      font-size: 12.5px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 2px;
    }

    .scan-class {
      font-size: 10px;
      font-weight: 500;
      color: var(--muted2);
    }

    .scan-status {
      font-family: 'Product Sans', sans-serif;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 0.3px;
      padding: 3px 10px;
      border-radius: 6px;
      white-space: nowrap;
    }

    .s-hadir {
      background: rgba(34, 197, 94, 0.14);
      color: #4ade80;
      border: 0.5px solid rgba(34, 197, 94, 0.24);
    }

    .s-terlambat {
      background: rgba(251, 191, 36, 0.12);
      color: #fbbf24;
      border: 0.5px solid rgba(251, 191, 36, 0.22);
    }

    .s-izin {
      background: rgba(96, 165, 250, 0.12);
      color: #60a5fa;
      border: 0.5px solid rgba(96, 165, 250, 0.22);
    }

    .recap-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin-top: 14px;
    }

    .recap-cell {
      background: var(--surface2);
      border: 0.5px solid var(--border);
      border-radius: 9px;
      padding: 10px;
      text-align: center;
    }

    .recap-val {
      font-size: 19px;
      font-weight: 700;
      line-height: 1.1;
      margin-bottom: 3px;
    }

    .recap-label {
      font-size: 9px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: var(--muted);
    }

    /* ─── STATS BAR ──────────────────────────────────────────────── */
    .section-stats {
      background: var(--surface);
      border-top: 0.5px solid var(--border);
      border-bottom: 0.5px solid var(--border);
      padding: 48px 0;
    }

    .stat-val {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: clamp(1.6rem, 2.5vw, 2.4rem);
      font-weight: 400;
      color: #fff;
      letter-spacing: 0.02em;
      margin-bottom: 6px;
      line-height: 1;
    }

    .stat-accent {
      color: var(--gold);
    }

    .stat-label {
      font-size: 9.5px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--muted);
      margin: 0;
    }

    /* ─── SECTION SHARED ─────────────────────────────────────────── */
    .section-label {
      display: inline-block;
      font-size: 9.5px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--primary);
      background: rgba(108, 99, 255, 0.1);
      border: 0.5px solid rgba(108, 99, 255, 0.25);
      padding: 4px 12px;
      border-radius: 99px;
      margin-bottom: 10px;
    }

    .section-title {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: clamp(1.3rem, 2vw, 1.9rem);
      font-weight: 400;
      color: #fff;
      letter-spacing: 0.03em;
      margin-bottom: 10px;
    }

    /* ─── FEATURES ───────────────────────────────────────────────── */
    .section-features {
      padding: 84px 0;
    }

    .feature-card {
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: var(--radius);
      padding: 22px;
      height: 100%;
      position: relative;
      overflow: hidden;
      transition: border-color .25s, transform .25s;
    }

    .feature-card:hover {
      border-color: rgba(108, 99, 255, 0.35);
      transform: translateY(-5px);
    }

    .feature-card-line {
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--primary), var(--info));
      opacity: 0;
      transition: opacity .25s;
    }

    .feature-card:hover .feature-card-line {
      opacity: 1;
    }

    .feature-icon {
      width: 42px;
      height: 42px;
      background: rgba(108, 99, 255, 0.1);
      border: 0.5px solid rgba(108, 99, 255, 0.24);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 14px;
      font-size: 16px;
      color: #a89ff7;
      position: relative;
      z-index: 1;
    }

    .feature-card h5 {
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
      position: relative;
      z-index: 1;
    }

    .feature-card p {
      font-size: 11.5px;
      font-weight: 500;
      color: var(--muted2);
      line-height: 1.7;
      margin: 0;
      position: relative;
      z-index: 1;
    }

    /* ─── CTA ────────────────────────────────────────────────────── */
    .section-cta {
      padding: 80px 0;
    }

    .cta-box {
      background: var(--surface);
      border: 0.5px solid rgba(108, 99, 255, 0.22);
      border-radius: 20px;
      padding: 64px 40px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .cta-glow {
      position: absolute;
      width: 340px;
      height: 340px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(108, 99, 255, 0.12), transparent 70%);
      top: -160px;
      right: -80px;
      pointer-events: none;
    }

    .cta-glow-2 {
      position: absolute;
      width: 260px;
      height: 260px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(34, 211, 238, 0.07), transparent 70%);
      bottom: -100px;
      left: -60px;
      pointer-events: none;
    }

    .cta-eyeline {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: 9.5px;
      letter-spacing: 4px;
      text-transform: uppercase;
      color: var(--gold);
      margin-bottom: 14px;
      position: relative;
      z-index: 1;
    }

    .cta-title {
      font-family: 'Trajan Pro', 'Book Antiqua', 'Palatino Linotype', Georgia, serif;
      font-size: clamp(1.4rem, 2.2vw, 2rem);
      font-weight: 400;
      color: #fff;
      letter-spacing: 0.03em;
      margin-bottom: 6px;
      position: relative;
      z-index: 1;
    }

    .cta-title-q {
      font-family: 'Product Sans', sans-serif;
      font-size: clamp(1.1rem, 1.6vw, 1.45rem);
      font-weight: 700;
      background: linear-gradient(90deg, #a89ff7 0%, #22d3ee 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      position: relative;
      z-index: 1;
      margin-bottom: 18px;
    }

    .cta-desc {
      font-size: 13px;
      font-weight: 500;
      color: var(--muted2);
      max-width: 460px;
      margin: 0 auto 28px;
      line-height: 1.75;
      position: relative;
      z-index: 1;
    }

    .cta-btns {
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    /* ─── RESPONSIVE ─────────────────────────────────────────────── */

    /* Tablet Landscape */
    @media (max-width: 1199px) {
      .section-hero {
        padding: 68px 0 60px;
      }

      .section-features {
        padding: 68px 0;
      }

      .section-cta {
        padding: 68px 0;
      }

      .cta-box {
        padding: 52px 32px;
      }
    }

    /* Tablet Portrait */
    @media (max-width: 991px) {
      .section-hero {
        padding: 56px 0 48px;
      }

      .hero-title {
        font-size: 2.1rem;
      }

      .hero-card-wrap {
        animation: none;
        margin-top: 36px;
      }

      .section-features {
        padding: 56px 0;
      }

      .section-cta {
        padding: 56px 0;
      }

      .cta-box {
        padding: 44px 24px;
      }

      .cta-title {
        font-size: 1.65rem;
      }

      .section-stats {
        padding: 40px 0;
      }
    }

    /* Large Phone */
    @media (max-width: 767px) {
      .section-hero {
        padding: 44px 0 40px;
      }

      .hero-title {
        font-size: 1.85rem;
        letter-spacing: -.3px;
      }

      .hero-subtitle {
        font-size: .88rem;
      }

      .hero-img-col {
        display: none !important;
      }

      .btn-primary-live,
      .btn-ghost-live {
        width: 100%;
        justify-content: center;
      }

      .d-flex.hero-btns {
        flex-direction: column;
      }

      .stat-val {
        font-size: 1.75rem;
      }

      .section-features {
        padding: 44px 0;
      }

      .section-cta {
        padding: 44px 0;
      }

      .cta-box {
        padding: 36px 18px;
        border-radius: 14px;
      }

      .cta-title {
        font-size: 1.4rem;
      }

      .cta-btns {
        flex-direction: column;
      }

      .cta-btns .btn-primary-live,
      .cta-btns .btn-ghost-live {
        width: 100%;
        justify-content: center;
      }
    }

    /* Small Phone */
    @media (max-width: 575px) {
      .section-hero {
        padding: 36px 0 32px;
      }

      .hero-title {
        font-size: 1.55rem;
      }

      .hero-eyeline {
        font-size: 9.5px;
        letter-spacing: 2.5px;
      }

      .feature-card {
        padding: 18px;
        border-radius: 10px;
      }

      .feature-icon {
        width: 38px;
        height: 38px;
        font-size: 14px;
      }

      .feature-card h5 {
        font-size: 12px;
      }

      .feature-card p {
        font-size: 11px;
      }

      .cta-box {
        padding: 28px 14px;
        border-radius: 12px;
      }

      .cta-title {
        font-size: 1.2rem;
        letter-spacing: 0;
      }

      .cta-desc {
        font-size: .82rem;
      }

      .section-cta {
        padding: 32px 0;
      }

      .stat-val {
        font-size: 1.55rem;
      }

      .stat-label {
        font-size: 8.5px;
        letter-spacing: 1px;
      }
    }
  </style>


  {{-- ══════════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════════════ --}}
  <section class="section-hero">
    <div class="container">
      <div class="row align-items-center g-4 g-xl-5">

        {{-- Copy --}}
        <div class="col-lg-6" data-aos="fade-up">
          <div class="hero-badge">
            <span class="badge-dot"></span>
            Smart Attendance Platform v3.0
          </div>
          <div class="hero-eyeline">Sistem Absensi Digital</div>
          <h1 class="hero-title">
            Hadir. Tercatat.<br>Terpantau.
          </h1>
          <div class="hero-title-sub">Real-Time. Tanpa Kertas.</div>
          <p class="hero-subtitle">
            Kelola kehadiran siswa, guru, dan staff dengan teknologi QR Code terintegrasi. Pantau data secara live melalui
            tampilan Live Board yang elegan.
          </p>

          <div class="d-flex flex-wrap hero-btns gap-3">
            @if(auth()->check())
              <a href="{{ route('dashboard') }}" class="btn-primary-live">
                <i class="ti tabler-layout-dashboard me-2"></i> Masuk ke Dashboard
              </a>
            @else
              <a href="{{ route('login') }}" class="btn-primary-live">
                Mulai Sekarang <i class="ti tabler-arrow-right ms-1"></i>
              </a>
            @endif
            <a href="{{ route('public.live-board') }}" target="_blank" class="btn-ghost-live">
              <i class="ti tabler-device-tv"></i> Live Board Demo
            </a>
          </div>

          <div class="hero-trust">
            <span>Ideal untuk</span>
            <hr class="hero-trust-divider">
            <span>MA &middot; SMP &middot; SMK &middot; SD</span>
          </div>
        </div>

        {{-- Mockup Visual --}}
        <div class="col-lg-6 hero-img-col" data-aos="zoom-in" data-aos-delay="150">
          <div class="hero-card-wrap">

            {{-- Window chrome --}}
            <div class="hero-card-header">
              <span class="wdot wdot-r"></span>
              <span class="wdot wdot-y"></span>
              <span class="wdot wdot-g"></span>
              <span class="card-header-label">Live Attendance Board</span>
              <div class="live-pill">
                <span class="live-pill-dot"></span> LIVE
              </div>
            </div>

            {{-- Card body --}}
            <div class="hero-card-body">
              <div class="scan-section-label">Scan Terbaru</div>

              <div class="scan-row">
                <div class="scan-avatar" style="background: linear-gradient(135deg, #6c63ff, #22d3ee);">AR</div>
                <div class="flex-grow-1">
                  <div class="scan-name">Ahmad Ridwan</div>
                  <div class="scan-class">XII IPA 1 &middot; 07:14 WIB</div>
                </div>
                <span class="scan-status s-hadir">Hadir</span>
              </div>

              <div class="scan-row">
                <div class="scan-avatar" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">SF</div>
                <div class="flex-grow-1">
                  <div class="scan-name">Siti Fatimah</div>
                  <div class="scan-class">XI IPS 2 &middot; 07:22 WIB</div>
                </div>
                <span class="scan-status s-terlambat">Terlambat</span>
              </div>

              <div class="scan-row" style="margin-bottom:0;">
                <div class="scan-avatar" style="background: linear-gradient(135deg, #3b82f6, #8b5cf6);">MN</div>
                <div class="flex-grow-1">
                  <div class="scan-name">M. Naufal</div>
                  <div class="scan-class">X MIPA 3 &middot; 07:30 WIB</div>
                </div>
                <span class="scan-status s-izin">Izin</span>
              </div>

              {{-- Recap mini --}}
              <div class="scan-section-label mt-3 mb-2">Rekap Hari Ini</div>
              <div class="recap-grid">
                <div class="recap-cell">
                  <div class="recap-val" style="color:#4ade80;">
                    {{ max($siswaCount, 250) }}
                  </div>
                  <div class="recap-label">Hadir</div>
                </div>
                <div class="recap-cell">
                  <div class="recap-val" style="color:#fbbf24;">18</div>
                  <div class="recap-label">Terlambat</div>
                </div>
                <div class="recap-cell">
                  <div class="recap-val" style="color:#f87171;">7</div>
                  <div class="recap-label">Absen</div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>

  {{-- ══════════════════════════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════════════════════════════ --}}
  <section id="statistik" class="section-stats">
    <div class="container">
      <div class="row g-4 text-center justify-content-center">

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
          <div class="stat-val">{{ max($siswaCount, 250) }}<span class="stat-accent">+</span></div>
          <p class="stat-label">Siswa Terdaftar</p>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="80">
          <div class="stat-val">{{ max($guruCount + $staffCount, 45) }}<span class="stat-accent">+</span></div>
          <p class="stat-label">Tenaga Pendidik</p>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="160">
          <div class="stat-val">99.9<span class="stat-accent">%</span></div>
          <p class="stat-label">Keamanan Data</p>
        </div>

        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="240">
          <div class="stat-val">&lt;1<span class="stat-accent">s</span></div>
          <p class="stat-label">Kecepatan Scan</p>
        </div>

      </div>
    </div>
  </section>

  {{-- ══════════════════════════════════════════════════════════════
     FEATURES
══════════════════════════════════════════════════════════════════ --}}
  <section id="fitur" class="section-features">
    <div class="container">
      <div class="text-center mb-4 mb-lg-5" data-aos="fade-up">
        <div class="section-label">Fitur Unggulan</div>
        <h2 class="section-title">Teknologi Pintar untuk Sekolah Modern</h2>
        <p class="text-muted mx-auto" style="max-width:500px; font-size:13px; line-height:1.75; font-weight:500;">
          Satu platform terintegrasi untuk semua kebutuhan manajemen kehadiran dan kedisiplinan sekolah Anda.
        </p>
      </div>

      <div class="row g-3">
        @foreach ([
          ['icon' => 'tabler-bolt', 'title' => 'Ultra-Fast QR Scan', 'desc' => 'Algoritma pemindaian yang dioptimalkan untuk mengenali QR Code siswa dalam hitungan milidetik.'],
          ['icon' => 'tabler-broadcast', 'title' => 'Live Board Display', 'desc' => 'Tampilkan data kehadiran secara live di lobi sekolah menggunakan layar TV untuk transparansi publik.'],
          ['icon' => 'tabler-shield-check', 'title' => 'Fraud Protection', 'desc' => 'Dilengkapi Geofencing & sistem anti-duplicate untuk mencegah manipulasi data presensi.'],
          ['icon' => 'tabler-chart-dots', 'title' => 'Analitik Mendalam', 'desc' => 'Laporan statistik kehadiran harian, mingguan, hingga bulanan yang diolah secara otomatis.'],
          ['icon' => 'tabler-file-download', 'title' => 'Export Laporan', 'desc' => 'Unduh rekapitulasi absensi dalam format Excel & PDF yang rapi dan siap cetak kapan saja.'],
          ['icon' => 'tabler-id', 'title' => 'Smart ID Card', 'desc' => 'Generate otomatis kartu identitas siswa dengan QR Code presensi terintegrasi dalam satu klik.'],
      ] as $i => $feat)
          <div class="col-sm-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $i * 60 }}">
            <div class="feature-card">
              <div class="feature-icon">
                <i class="ti {{ $feat['icon'] }}"></i>
              </div>
              <h5>{{ $feat['title'] }}</h5>
              <p>{{ $feat['desc'] }}</p>
              <div class="feature-card-line"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ══════════════════════════════════════════════════════════════
     CTA
══════════════════════════════════════════════════════════════════ --}}
  <section class="section-cta">
    <div class="container">
      <div class="cta-box" data-aos="fade-up">
        <div class="cta-glow"></div>
        <div class="cta-glow-2"></div>

        <div class="cta-eyeline">Mulai Hari Ini</div>
        <h2 class="cta-title">Siap Digitalisasi Sekolah Anda?</h2>
        <div class="cta-title-q">Bergabung. Gratis. Sekarang.</div>
        <p class="cta-desc">
          Tingkatkan efisiensi administrasi madrasah Anda bersama ekosistem sekolah modern — tanpa biaya, tanpa batas.
        </p>

        <div class="cta-btns">
          @if(auth()->check())
            <a href="{{ route('dashboard') }}" class="btn-primary-live">
              <i class="ti tabler-layout-dashboard me-2"></i> Buka Dashboard Saya
            </a>
          @else
            @php
              $ijinkanRegister = \App\Models\Pengaturan::where('key', 'ijinkan_pembuatan_akun_mandiri')->value('value') === 'Ya';
            @endphp
            @if (\Illuminate\Support\Facades\Route::has('register') && $ijinkanRegister)
              <a href="{{ route('register') }}" class="btn-primary-live">
                Daftar Gratis Sekarang <i class="ti tabler-arrow-right ms-1"></i>
              </a>
            @endif
          @endif
          <a href="{{ route('public.live-board') }}" target="_blank" class="btn-ghost-live">
            <i class="ti tabler-device-tv"></i> Lihat Live Demo
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- AOS --}}
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof AOS !== 'undefined') {
        AOS.init({
          duration: 700,
          once: true,
          easing: 'ease-out-quart',
          offset: 40
        });
      }
    });
  </script>
@endsection
