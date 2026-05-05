@extends('installer.layout')

@section('step-chip',    'Langkah 4 — Sekolah')
@section('step-num',     '4')
@section('progress',     '80')
@section('step-title',   'Profil Sekolah')
@section('step-desc',    'Lengkapi data dasar sekolah Anda.')
@section('form-action',  route('installer.step4Submit'))

@section('content')
    <div class="notice notice-ok" style="margin-bottom: 22px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span>Database terhubung! Sekarang, mari lengkapi profil sekolah Anda.</span>
    </div>

    <div class="grid-2 mt-12">
        <div class="field">
            <label class="lbl">Nama Sekolah</label>
            <div class="inp-wrap has-icon">
                <span class="inp-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                <input type="text" name="school_name" value="{{ old('school_name') }}" placeholder="SMK Negeri 1 Jakarta" required>
            </div>
        </div>
        <div class="field">
            <label class="lbl">Slogan Sekolah</label>
            <div class="inp-wrap has-icon">
                <span class="inp-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/></svg>
                </span>
                <input type="text" name="school_slogan" value="{{ old('school_slogan') }}" placeholder="Cerdas & Inovatif" required>
            </div>
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Alamat Sekolah</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            <input type="text" name="school_address" value="{{ old('school_address') }}" placeholder="Jl. Raya No. 123, Kota" required>
        </div>
    </div>

    <div class="grid-2 mt-12">
        <div class="field">
            <label class="lbl">Nomor Kontak</label>
            <div class="inp-wrap has-icon">
                <span class="inp-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <input type="text" name="school_phone" value="{{ old('school_phone') }}" placeholder="021-xxxxxx atau 0812-xxxx" required>
            </div>
        </div>
        <div class="field">
            <label class="lbl">Email Sekolah</label>
            <div class="inp-wrap has-icon">
                <span class="inp-icon">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <input type="email" name="school_email" value="{{ old('school_email') }}" placeholder="info@sekolah.sch.id" required>
            </div>
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl">Tampilkan Website Portal?</label>
        <div style="display: flex; gap: 24px; margin-top: 6px;">
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--text-2); cursor: pointer;">
                <input type="radio" name="enable_website" value="Ya" checked style="accent-color: var(--primary); width: 16px; height: 16px;">
                <span>Ya, tampilkan landing page</span>
            </label>
            <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; color: var(--text-2); cursor: pointer;">
                <input type="radio" name="enable_website" value="Tidak" style="accent-color: var(--primary); width: 16px; height: 16px;">
                <span>Tidak, langsung ke Login</span>
            </label>
        </div>
    </div>
@endsection

@section('foot-l')
    <a href="{{ route('installer.step3') }}" class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
@endsection

@section('foot-r')
    <button type="submit" class="btn btn-primary btn-wide" id="btn-submit" data-loading="Menyimpan...">
        <span data-label>Lanjut ke Akun Admin</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </button>
@endsection
