@php
  use Illuminate\Support\Facades\Route;
  $pageConfigs = ['myLayout' => 'blank'];
  $configData = Helper::appClasses();
  $customizerHidden = 'customizer-hide';

  $logoSrc = null;
  $logoUrl = \App\Models\Pengaturan::where('key', 'logo_url')->value('value');
  if ($logoUrl) {
    $logoSrc = $logoUrl;
  } else {
    $logoLocal = \App\Models\Pengaturan::where('key', 'logo_sekolah')->value('value');
    if ($logoLocal) {
      $logoSrc = asset('uploads/logo/' . $logoLocal);
    }
  }
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Login')

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
  <style>
    /* ===== NO SCROLL — FULL HEIGHT ===== */
    html, body { overflow: hidden; height: 100%; }
    .authentication-wrapper.authentication-cover {
      height: 100vh;
      max-height: 100vh;
      overflow: hidden;
    }
    .authentication-inner { height: 100vh !important; overflow: hidden; }

    /* ===== LEFT COLUMN BASE ===== */
    .auth-cover-bg {
      background: linear-gradient(160deg, #020617 0%, #0f0c29 55%, #050a1a 100%);
      position: relative;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    /* ===== PERSPECTIVE GRID ===== */
    .auth-cover-bg::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(99,102,241,0.08) 1px, transparent 1px),
        linear-gradient(90deg, rgba(99,102,241,0.08) 1px, transparent 1px);
      background-size: 60px 60px;
      transform: perspective(600px) rotateX(55deg) scale(2.5);
      transform-origin: 50% 0%;
      animation: grid-scroll 18s linear infinite;
      opacity: 0.9;
      pointer-events: none;
    }
    @keyframes grid-scroll {
      from { background-position: 0 0; }
      to   { background-position: 0 60px; }
    }

    /* ===== RADIAL VIGNETTE ===== */
    .auth-cover-bg::after {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(ellipse at 50% 100%, transparent 35%, #020617 80%);
      pointer-events: none;
      z-index: 2;
    }

    /* ===== FLOATING ORBS ===== */
    .glass-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(90px);
      opacity: 0.18;
      animation: orb-drift 22s infinite alternate ease-in-out;
      pointer-events: none;
      z-index: 1;
    }
    .orb-1 {
      width: 650px; height: 650px;
      background: radial-gradient(circle, #6366f1 0%, #4338ca 60%, transparent 100%);
      top: -200px; left: -200px;
    }
    .orb-2 {
      width: 500px; height: 500px;
      background: radial-gradient(circle, #a855f7 0%, #7e22ce 60%, transparent 100%);
      bottom: -180px; right: -120px;
      animation-delay: -9s; animation-duration: 27s;
    }
    .orb-3 {
      width: 380px; height: 380px;
      background: radial-gradient(circle, #06b6d4 0%, #0891b2 60%, transparent 100%);
      top: 38%; left: 38%;
      animation-delay: -15s; animation-duration: 20s;
    }
    @keyframes orb-drift {
      0%   { transform: translate(0,0) scale(1); }
      33%  { transform: translate(70px,-50px) scale(1.12); }
      66%  { transform: translate(-50px,80px) scale(0.9); }
      100% { transform: translate(25px,-25px) scale(1.06); }
    }

    /* ===== SCAN LINES ===== */
    .cyber-line {
      position: absolute; left: 0; width: 100%; height: 1px;
      background: linear-gradient(90deg, transparent 0%, #6366f1 30%, #a855f7 50%, #6366f1 70%, transparent 100%);
      opacity: 0;
      animation: scan-line 12s linear infinite;
      pointer-events: none;
      z-index: 5;
    }
    .line-1 { top: 22%; animation-delay: 0s; }
    .line-2 { top: 54%; animation-delay: 4s; }
    .line-3 { top: 82%; animation-delay: 8s; }
    @keyframes scan-line {
      0%   { opacity: 0; transform: translateX(-100%); }
      15%  { opacity: 0.7; }
      75%  { opacity: 0.4; }
      100% { opacity: 0; transform: translateX(100%); }
    }

    /* ===== PARTICLE NODES ===== */
    .particle-dot {
      position: absolute; width: 3px; height: 3px;
      background: #6366f1; border-radius: 50%;
      box-shadow: 0 0 7px 3px rgba(99,102,241,0.7);
      animation: dot-pulse 4s ease-in-out infinite alternate;
      pointer-events: none; z-index: 5;
    }
    @keyframes dot-pulse {
      from { opacity: 0.25; transform: scale(1); }
      to   { opacity: 1;    transform: scale(2); }
    }

    /* ===== CYBER CORNERS ===== */
    .cyber-corner {
      position: absolute; width: 44px; height: 44px;
      border-color: rgba(99,102,241,0.55);
      border-style: solid; pointer-events: none; z-index: 10;
      animation: corner-glow 3.5s ease-in-out infinite alternate;
    }
    .corner-tl { top: 20px; left: 20px; border-width: 2px 0 0 2px; }
    .corner-tr { top: 20px; right: 20px; border-width: 2px 2px 0 0; }
    .corner-bl { bottom: 20px; left: 20px; border-width: 0 0 2px 2px; }
    .corner-br { bottom: 20px; right: 20px; border-width: 0 2px 2px 0; }
    @keyframes corner-glow {
      from { border-color: rgba(99,102,241,0.35); }
      to   { border-color: rgba(168,85,247,0.85); box-shadow: inset 0 0 12px rgba(168,85,247,0.15); }
    }

    /* ===== RIGHT EDGE GLOW ===== */
    .edge-glow {
      position: absolute; right: 0; top: 0; width: 1px; height: 100%;
      background: linear-gradient(to bottom, transparent 0%, #6366f1 25%, #a855f7 50%, #6366f1 75%, transparent 100%);
      z-index: 10;
      animation: edge-pulse 4s ease-in-out infinite alternate;
    }
    @keyframes edge-pulse {
      from { opacity: 0.3; box-shadow: 0 0 8px rgba(99,102,241,0.4); }
      to   { opacity: 0.75; box-shadow: 0 0 22px rgba(168,85,247,0.7); }
    }

    /* ===== CENTRAL CONTENT ===== */
    .cyber-center {
      position: relative; z-index: 10;
      text-align: center; padding: 1.5rem 2rem; max-width: 460px;
    }

    /* Animated Ring */
    .cyber-ring {
      width: 150px; height: 150px; border-radius: 50%;
      border: 2px solid rgba(99,102,241,0.35);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.75rem; position: relative;
      animation: ring-spin 22s linear infinite;
    }
    .cyber-ring::before {
      content: ''; position: absolute; inset: -10px; border-radius: 50%;
      border: 1px dashed rgba(168,85,247,0.3);
      animation: ring-spin 16s linear infinite reverse;
    }
    .cyber-ring::after {
      content: ''; position: absolute; inset: 12px; border-radius: 50%;
      border: 1px solid rgba(6,182,212,0.22);
      animation: ring-spin 11s linear infinite;
    }
    @keyframes ring-spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
    .ring-dot {
      position: absolute; width: 9px; height: 9px;
      border-radius: 50%; background: #6366f1;
      box-shadow: 0 0 14px 5px rgba(99,102,241,0.85);
    }
    .ring-dot:nth-child(1) { top: -4px; left: 50%; transform: translateX(-50%); }
    .ring-dot:nth-child(2) { bottom: -4px; left: 50%; transform: translateX(-50%);
      background: #a855f7; box-shadow: 0 0 14px 5px rgba(168,85,247,0.85); }
    .ring-icon {
      font-size: 3.2rem; color: #818cf8;
      filter: drop-shadow(0 0 18px rgba(99,102,241,0.9));
      animation: icon-pulse 3s ease-in-out infinite alternate;
    }
    @keyframes icon-pulse {
      from { filter: drop-shadow(0 0 12px rgba(99,102,241,0.6)); transform: scale(1); }
      to   { filter: drop-shadow(0 0 32px rgba(168,85,247,1)); transform: scale(1.1); }
    }

    /* Badge & Titles */
    .cyber-badge {
      display: inline-block;
      background: linear-gradient(135deg, rgba(99,102,241,0.12), rgba(168,85,247,0.12));
      border: 1px solid rgba(99,102,241,0.35); border-radius: 4px;
      padding: 3px 14px; font-size: 0.62rem; letter-spacing: 0.3em;
      color: rgba(129,140,248,0.9); text-transform: uppercase; margin-bottom: 0.75rem;
    }
    .cyber-main-title {
      font-size: 3rem; font-weight: 900; margin: 0; line-height: 1;
      background: linear-gradient(135deg, #fff 0%, #c7d2fe 45%, #a855f7 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      filter: drop-shadow(0 0 28px rgba(99,102,241,0.45));
    }
    .cyber-subtitle {
      font-size: 0.68rem; letter-spacing: 0.22em;
      color: rgba(255,255,255,0.35); text-transform: uppercase;
      margin-top: 0.4rem; margin-bottom: 2rem;
    }

    /* Feature Cards */
    .cyber-features { display: flex; flex-direction: column; gap: 0.65rem; text-align: left; }
    .cyber-feature-item {
      display: flex; align-items: center; gap: 12px;
      background: rgba(255,255,255,0.025);
      border: 1px solid rgba(255,255,255,0.055); border-radius: 8px;
      padding: 9px 14px;
    }
    .feature-icon-wrap {
      width: 34px; height: 34px; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0; font-size: 0.95rem;
    }
    .fi-purple { background: rgba(168,85,247,0.14); color: #a855f7; }
    .fi-blue   { background: rgba(99,102,241,0.14); color: #818cf8; }
    .fi-cyan   { background: rgba(6,182,212,0.14);  color: #22d3ee; }
    .feature-text strong { display: block; font-size: 0.76rem; color: rgba(255,255,255,0.82); font-weight: 600; }
    .feature-text span   { font-size: 0.66rem; color: rgba(255,255,255,0.32); }

    /* Status */
    .cyber-status {
      display: flex; align-items: center; justify-content: center; gap: 7px;
      margin-top: 1.75rem; font-size: 0.62rem; letter-spacing: 0.18em;
      color: rgba(255,255,255,0.22); text-transform: uppercase;
    }
    .status-dot {
      width: 6px; height: 6px; background: #22c55e;
      border-radius: 50%; box-shadow: 0 0 8px #22c55e;
      animation: blink 2s ease-in-out infinite;
    }
    @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.25; } }

    /* ===== RIGHT FORM PANEL — BASE ===== */
    .authentication-bg {
      background: linear-gradient(160deg, #020617 0%, #0a0d1f 55%, #050a1a 100%) !important;
      position: relative;
      overflow: hidden;
      border-left: 1px solid rgba(99,102,241,0.18);
    }

    /* Subtle grid background kanan — lebih halus dari kiri */
    .authentication-bg::before {
      content: ''; position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(99,102,241,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(99,102,241,0.035) 1px, transparent 1px);
      background-size: 40px 40px;
      pointer-events: none; z-index: 0;
    }

    /* Radial glow sudut kanan */
    .authentication-bg::after {
      content: ''; position: absolute; inset: 0;
      background:
        radial-gradient(ellipse at 110% -10%, rgba(99,102,241,0.12) 0%, transparent 50%),
        radial-gradient(ellipse at -10% 110%, rgba(168,85,247,0.10) 0%, transparent 45%);
      pointer-events: none; z-index: 0;
    }

    /* Particle dots kanan — lebih sedikit dari kiri */
    .right-particle {
      position: absolute; width: 2px; height: 2px; border-radius: 50%;
      background: #6366f1;
      box-shadow: 0 0 6px 2px rgba(99,102,241,0.6);
      animation: dot-pulse 4s ease-in-out infinite alternate;
      pointer-events: none; z-index: 1;
    }

    /* Scan line kanan (1 saja, lebih subtle) */
    .right-scan {
      position: absolute; left: 0; width: 100%; height: 1px;
      background: linear-gradient(90deg, transparent 0%, rgba(99,102,241,0.2) 40%, rgba(168,85,247,0.2) 60%, transparent 100%);
      opacity: 0;
      animation: scan-line 16s linear infinite;
      pointer-events: none; z-index: 1;
      top: 55%;
    }

    /* ===== FORM CARD ===== */
    .auth-form-card {
      position: relative; z-index: 5;
      background: rgba(8, 12, 28, 0.55);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(99,102,241,0.2);
      border-radius: 16px;
      padding: 2.5rem;
      box-shadow:
        0 0 0 1px rgba(255,255,255,0.03),
        0 20px 50px rgba(0,0,0,0.45),
        0 0 40px rgba(99,102,241,0.06);
      animation: card-border-glow 5s ease-in-out infinite alternate;
    }
    @keyframes card-border-glow {
      from { box-shadow: 0 0 0 1px rgba(255,255,255,0.03), 0 20px 50px rgba(0,0,0,0.45), 0 0 20px rgba(99,102,241,0.04); }
      to   { box-shadow: 0 0 0 1px rgba(99,102,241,0.12), 0 20px 50px rgba(0,0,0,0.45), 0 0 50px rgba(99,102,241,0.12); }
    }

    /* Corner brackets pada form card */
    .auth-form-card::before, .auth-form-card::after {
      content: ''; position: absolute; width: 22px; height: 22px;
      border-color: rgba(99,102,241,0.5); border-style: solid;
      animation: corner-glow 3.5s ease-in-out infinite alternate;
    }
    .auth-form-card::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; border-radius: 3px 0 0 0; }
    .auth-form-card::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; border-radius: 0 0 3px 0; }

    /* Top accent line pada card */
    .card-top-accent {
      position: absolute; top: -1px; left: 20%; right: 20%; height: 1px;
      background: linear-gradient(90deg, transparent, #6366f1, #a855f7, #6366f1, transparent);
      border-radius: 0 0 4px 4px;
      opacity: 0.7;
    }

    /* Icon header form */
    .form-header-icon {
      width: 48px; height: 48px; border-radius: 12px;
      background: linear-gradient(135deg, rgba(99,102,241,0.2), rgba(168,85,247,0.2));
      border: 1px solid rgba(99,102,241,0.3);
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 1rem;
      font-size: 1.4rem; color: #818cf8;
      box-shadow: 0 0 20px rgba(99,102,241,0.2);
    }

    .login-title {
      background: linear-gradient(135deg, #fff 0%, #c7d2fe 60%, #a855f7 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      font-weight: 700;
    }
    .login-subtitle { font-size: 0.78rem; color: rgba(255,255,255,0.38); letter-spacing: 0.03em; }

    /* Divider nano */
    .form-divider {
      height: 1px; width: 100%;
      background: linear-gradient(90deg, transparent, rgba(99,102,241,0.25), transparent);
      margin: 1.25rem 0;
    }

    .form-label { color: rgba(255,255,255,0.55) !important; font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.4rem !important; }
    .form-control,
    .form-control:focus,
    .form-control.is-invalid,
    .form-control.is-valid,
    .form-control.is-invalid:focus,
    .form-control.is-valid:focus {
      background: rgba(255,255,255,0.025) !important;
      border: 1px solid rgba(255,255,255,0.08) !important;
      color: #e2e8f0 !important;
      padding: 0.7rem 1rem !important;
      padding-block: 0.7rem !important;
      padding-inline: 1rem !important;
      border-radius: 8px !important;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control::placeholder { color: rgba(255,255,255,0.2) !important; }
    .form-control:focus {
      border-color: rgba(99,102,241,0.55) !important;
      box-shadow: 0 0 0 3px rgba(99,102,241,0.1), 0 0 12px rgba(99,102,241,0.08) !important;
      background: rgba(99,102,241,0.04) !important;
      color: #fff !important;
    }
    .form-control.is-invalid { border-color: rgba(239,68,68,0.6) !important; }
    .input-group-text {
      background: rgba(255,255,255,0.025) !important;
      border: 1px solid rgba(255,255,255,0.08) !important;
      border-left: none !important;
      color: rgba(255,255,255,0.35) !important;
      border-radius: 0 8px 8px 0 !important;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    /* Fix alignment & seamless connection for password toggle */
    .form-password-toggle .input-group .form-control,
    .form-password-toggle .input-group .form-control:focus,
    .form-password-toggle .input-group .form-control.is-invalid,
    .form-password-toggle .input-group .form-control.is-valid {
      border-top-right-radius: 0 !important;
      border-bottom-right-radius: 0 !important;
      border-right: none !important;
      padding: 0.7rem 1rem !important;
      padding-block: 0.7rem !important;
      padding-inline: 1rem !important;
    }
    .form-password-toggle .input-group .input-group-text,
    .form-password-toggle .input-group .input-group-text:focus,
    .form-password-toggle .input-group:focus-within .input-group-text {
      padding: 0.7rem 0.85rem !important;
      padding-block: 0.7rem !important;
      padding-inline: 0.85rem !important;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }
    .form-password-toggle .input-group .input-group-text:hover {
      color: #818cf8 !important;
      background: rgba(99,102,241,0.08) !important;
    }
    .form-password-toggle .input-group .form-control:focus + .input-group-text {
      border-color: rgba(99,102,241,0.55) !important;
      box-shadow: 0 3px 12px rgba(99,102,241,0.08) !important;
      color: #818cf8 !important;
    }
    .form-check-input {
      background-color: rgba(255,255,255,0.06) !important;
      border-color: rgba(255,255,255,0.15) !important;
    }
    .form-check-input:checked {
      background-color: #6366f1 !important;
      border-color: #6366f1 !important;
    }

    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #7c3aed 50%, #a855f7 100%);
      border: none; border-radius: 8px !important;
      font-weight: 600; letter-spacing: 0.12em; font-size: 0.8rem;
      transition: all 0.3s ease;
      position: relative; overflow: hidden;
    }
    .btn-primary::before {
      content: ''; position: absolute; inset: 0;
      background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.08) 50%, transparent 100%);
      transform: translateX(-100%);
      transition: transform 0.4s ease;
    }
    .btn-primary:hover::before { transform: translateX(100%); }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(99,102,241,0.45), 0 0 40px rgba(168,85,247,0.2);
    }
    .btn-primary:active { transform: translateY(0); }

    /* Footer link */
    .form-footer-link { font-size: 0.7rem; color: rgba(255,255,255,0.28); }
    .form-footer-link a { color: #818cf8; text-decoration: none; }
    .form-footer-link a:hover { color: #a855f7; text-shadow: 0 0 8px rgba(168,85,247,0.5); }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1199.98px) {
      html, body { overflow: auto; height: auto; }
      .authentication-wrapper.authentication-cover { height: auto; min-height: 100vh; }
      .authentication-inner { height: auto !important; }
      .authentication-bg { border-left: none; padding: 1.5rem 0; }
      .auth-form-card { padding: 1.25rem; margin: 0.5rem; }
    }

    /* ===== SMARTPHONE (< 576px) ===== */
    @media (max-width: 575.98px) {
      .authentication-bg { padding: 0.75rem 0 !important; }
      .auth-form-card { padding: 1rem !important; margin: 0 !important; }
      .authentication-wrapper.authentication-cover .w-px-400 { max-width: 100%; width: 100%; }
      .mb-6 { margin-bottom: 0.85rem !important; }
      .mb-2 { margin-bottom: 0.4rem !important; }
      .login-title { font-size: 1.15rem; }
    }
  </style>
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/pages-auth.js'])
@endsection

@section('content')
  <div class="authentication-wrapper authentication-cover">
    <!-- Logo -->
    <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
      <span class="app-brand-logo demo">
        @if ($logoSrc)
          <img src="{{ $logoSrc }}" alt="Logo" style="height:22px;object-fit:contain;">
        @else
          @include('_partials.macros', ['width' => 22, 'height' => 22])
        @endif
      </span>
      <span class="app-brand-text demo text-heading fw-bold text-white">{{ config('variables.templateName') }}</span>
    </a>
    <!-- /Logo -->
    <div class="authentication-inner row m-0">
      <!-- Left Panel -->
      <div class="d-none d-xl-flex col-xl-8 p-0 position-relative overflow-hidden">
        <div class="auth-cover-bg w-100 h-100">

          <!-- Scan lines -->
          <div class="cyber-line line-1"></div>
          <div class="cyber-line line-2"></div>
          <div class="cyber-line line-3"></div>

          <!-- Floating orbs -->
          <div class="glass-orb orb-1"></div>
          <div class="glass-orb orb-2"></div>
          <div class="glass-orb orb-3"></div>

          <!-- Particle nodes -->
          <div class="particle-dot" style="top:12%;left:18%;animation-delay:0s;"></div>
          <div class="particle-dot" style="top:22%;left:78%;animation-delay:0.9s;background:#a855f7;box-shadow:0 0 7px 3px rgba(168,85,247,0.7);"></div>
          <div class="particle-dot" style="top:58%;left:12%;animation-delay:1.6s;background:#22d3ee;box-shadow:0 0 7px 3px rgba(34,211,238,0.7);"></div>
          <div class="particle-dot" style="top:72%;left:82%;animation-delay:0.4s;"></div>
          <div class="particle-dot" style="top:88%;left:42%;animation-delay:2.1s;background:#a855f7;box-shadow:0 0 7px 3px rgba(168,85,247,0.7);"></div>
          <div class="particle-dot" style="top:38%;left:90%;animation-delay:1.3s;background:#22d3ee;box-shadow:0 0 7px 3px rgba(34,211,238,0.7);"></div>
          <div class="particle-dot" style="top:8%;left:55%;animation-delay:0.6s;"></div>
          <div class="particle-dot" style="top:50%;left:6%;animation-delay:1.8s;background:#a855f7;box-shadow:0 0 7px 3px rgba(168,85,247,0.7);"></div>

          <!-- Cyber corner brackets -->
          <div class="cyber-corner corner-tl"></div>
          <div class="cyber-corner corner-tr"></div>
          <div class="cyber-corner corner-bl"></div>
          <div class="cyber-corner corner-br"></div>

          <!-- Right edge glow -->
          <div class="edge-glow"></div>

          <!-- Central content -->
          <div class="cyber-center">
            <div class="cyber-ring">
              <div class="ring-dot"></div>
              <div class="ring-dot"></div>
              <i class="ti tabler-fingerprint ring-icon"></i>
            </div>

            <div class="cyber-badge">Sistem Digital</div>
            <h1 class="cyber-main-title">ABSENSI</h1>
            <div class="cyber-subtitle">Sistem Manajemen Kehadiran Terpadu</div>

            <div class="cyber-features">
              <div class="cyber-feature-item">
                <div class="feature-icon-wrap fi-purple">
                  <i class="ti tabler-device-mobile"></i>
                </div>
                <div class="feature-text">
                  <strong>Absensi Real-time</strong>
                  <span>Pencatatan kehadiran langsung dan akurat</span>
                </div>
              </div>
              <div class="cyber-feature-item">
                <div class="feature-icon-wrap fi-blue">
                  <i class="ti tabler-chart-bar"></i>
                </div>
                <div class="feature-text">
                  <strong>Laporan Otomatis</strong>
                  <span>Rekap bulanan dan analitik kehadiran</span>
                </div>
              </div>
              <div class="cyber-feature-item">
                <div class="feature-icon-wrap fi-cyan">
                  <i class="ti tabler-shield-check"></i>
                </div>
                <div class="feature-text">
                  <strong>Keamanan Terjamin</strong>
                  <span>Data terenkripsi dan terlindungi</span>
                </div>
              </div>
            </div>

            <div class="cyber-status">
              <div class="status-dot"></div>
              <span>Sistem Aktif &amp; Terhubung</span>
            </div>
          </div>

        </div>
      </div>
      <!-- /Left Panel -->

      <!-- Login -->
      <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-8 p-3">

        <!-- Particle dots kanan -->
        <div class="right-particle" style="top:8%;left:12%;animation-delay:0.3s;"></div>
        <div class="right-particle" style="top:18%;right:10%;animation-delay:1.1s;background:#a855f7;box-shadow:0 0 6px 2px rgba(168,85,247,0.6);"></div>
        <div class="right-particle" style="top:75%;left:8%;animation-delay:1.7s;background:#22d3ee;box-shadow:0 0 6px 2px rgba(34,211,238,0.6);"></div>
        <div class="right-particle" style="top:88%;right:14%;animation-delay:0.6s;"></div>
        <div class="right-particle" style="top:45%;right:6%;animation-delay:2.2s;background:#a855f7;box-shadow:0 0 6px 2px rgba(168,85,247,0.6);"></div>
        <!-- Scan line kanan -->
        <div class="right-scan"></div>

        <div class="auth-form-card w-px-400 mx-auto">
          <!-- Top accent line -->
          <div class="card-top-accent"></div>

          <!-- Header icon -->
          <div class="form-header-icon">
            <i class="ti tabler-lock-open"></i>
          </div>

          <h4 class="mb-1 login-title">Selamat Datang Kembali</h4>
          <p class="login-subtitle mb-0">Masukkan kredensial Anda untuk mengakses sistem</p>

          <div class="form-divider"></div>

          @if (session('status'))
            <div class="alert border-0 mb-4" style="background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25)!important;color:#86efac;border-radius:8px;" role="alert">
              {{ session('status') }}
            </div>
          @endif

          <form id="formAuthentication" class="mb-4" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-4 form-control-validation">
              <label for="username" class="form-label">Username</label>
              <input type="text" class="form-control @error('username') is-invalid @enderror" id="username"
                name="username" placeholder="Masukkan username Anda" autofocus value="{{ old('username') }}" />
              @error('username')
                <span class="invalid-feedback" role="alert">
                  <span class="fw-medium">{{ $message }}</span>
                </span>
              @enderror
            </div>
            <div class="mb-4 form-password-toggle form-control-validation">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label mb-0" for="password">Password</label>
                @if (Route::has('password.request'))
                  <a href="{{ route('password.request') }}" style="font-size:0.68rem;color:#818cf8;text-decoration:none;" onmouseover="this.style.color='#a855f7'" onmouseout="this.style.color='#818cf8'">
                    Lupa Password?
                  </a>
                @endif
              </div>
              <div class="input-group input-group-merge @error('password') is-invalid @enderror">
                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror"
                  name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                  aria-describedby="password" />
                <span class="input-group-text cursor-pointer"><i class="ti tabler-eye-off"></i></span>
              </div>
              @error('password')
                <span class="invalid-feedback" role="alert">
                  <span class="fw-medium">{{ $message }}</span>
                </span>
              @enderror
            </div>
            <div class="mb-5">
              <div class="form-check ms-1">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                  {{ old('remember') ? 'checked' : '' }} />
                <label class="form-check-label form-footer-link" for="remember-me"> Ingat Saya </label>
              </div>
            </div>
            <button class="btn btn-primary d-grid w-100 py-3" type="submit">
              <span style="display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="ti tabler-login"></i> MASUK KE SISTEM
              </span>
            </button>
          </form>

          <div class="form-divider"></div>

          <p class="form-footer-link text-center mb-0">
            Butuh bantuan? <a href="#">Hubungi Admin</a>
          </p>
        </div>
      </div>
      <!-- /Login -->
    </div>
  </div>
@endsection

