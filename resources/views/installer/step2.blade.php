@extends('installer.layout')

@section('step-chip',    'Langkah 2 — Lisensi')
@section('step-num',     '2')
@section('progress',     '40')
@section('step-title',   'Aktivasi Produk')
@section('step-desc',    'Masukkan kode lisensi untuk memverifikasi kepemilikan aplikasi.')
@section('form-action',  route('installer.step2Submit'))

@section('content')
@if(session('install_school_name'))
    {{-- STATE: Lisensi sudah terverifikasi — tampilkan konfirmasi --}}
    <div class="notice notice-ok" style="margin-bottom: 22px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span>Lisensi berhasil diverifikasi! Silakan periksa data di bawah lalu lanjutkan.</span>
    </div>

    <div class="field">
        <label class="lbl">Kode Lisensi</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </span>
            <input type="text" value="{{ session('install_license_key') }}" readonly
                style="background: var(--bg-2, #f8f9fa); color: #334155; cursor: not-allowed; font-weight: 600;">
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Domain Terdaftar</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </span>
            <input type="text" value="{{ session('install_registered_domain') }}" readonly
                style="background: var(--bg-2, #f8f9fa); color: #334155; cursor: not-allowed; font-weight: 600;">
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Nama Sekolah Terdaftar</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            <input type="text" value="{{ session('install_school_name') }}" readonly
                style="background: var(--bg-2, #f8f9fa); color: #334155; cursor: not-allowed; font-weight: 600;">
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
            Nama ini diambil dari database pusat. Akan otomatis terisi di langkah pengisian data lembaga.
        </div>
    </div>

@else
    {{-- STATE: Belum verifikasi — tampilkan form --}}
    <div class="notice notice-info" style="margin-bottom: 22px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        <span>Masukkan kode lisensi produk yang Anda dapatkan saat pembelian.</span>
    </div>

    <div class="field">
        <label class="lbl">Kode Lisensi</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </span>
            <input type="text" name="license_key" value="{{ old('license_key', session('install_license_key')) }}" placeholder="Contoh: LIC-XXXX-XXXX" required autofocus>
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Domain Terdaftar</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </span>
            <input type="text" name="registered_domain" value="{{ old('registered_domain', session('install_registered_domain', request()->getHost())) }}" placeholder="domain.com" required>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
            Pastikan domain sesuai dengan yang terdaftar di sistem pusat.
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Nama Sekolah/Lembaga</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </span>
            <input type="text" name="school_name" value="{{ old('school_name', session('install_school_name')) }}" placeholder="Contoh: SMA Negeri 1 Bandung" required>
        </div>
        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 5px;">
            Masukkan nama resmi instansi Anda.
        </div>
    </div>
@endif
@endsection

@section('foot-l')
    <a href="{{ route('installer.step1') }}" class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
@endsection

@section('foot-r')
@if(session('install_school_name'))
    <a href="{{ route('installer.step3') }}" class="btn btn-primary btn-wide">
        <span>Lanjut ke Konfigurasi Database</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </a>
@else
    <button type="submit" class="btn btn-primary btn-wide" id="btn-submit" data-loading="Memverifikasi...">
        <span data-label>Aktivasi & Lanjut</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </button>
@endif
@endsection
