@extends('installer.layout')

@section('step-chip',    'Langkah 1 — Sistem')
@section('step-num',     '1')
@section('progress',     '20')
@section('step-title',   'Pengecekan Sistem')
@section('step-desc',    'Pastikan server memenuhi semua persyaratan sebelum melanjutkan instalasi.')
@section('form-action',  route('installer.step2'))

@section('content')
    <div class="grid-2">
        @foreach($requirements as $label => $passed)
            <div class="req-row" style="margin-bottom: 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 28px; height: 28px; border-radius: 6px; background: {{ $passed ? 'var(--success-dim)' : 'var(--danger-dim)' }}; display: flex; align-items: center; justify-content: center; color: {{ $passed ? 'var(--success)' : 'var(--danger)' }};">
                        @if($passed)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @else
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        @endif
                    </div>
                    <span class="req-name" style="font-size: 0.8125rem;">{{ $label }}</span>
                </div>
                <span class="badge {{ $passed ? 'badge-ok' : 'badge-fail' }}">
                    {{ $passed ? 'Aktif' : 'Gagal' }}
                </span>
            </div>
        @endforeach
    </div>

    @if(!$allPassed)
        <div class="notice notice-err mt-16 mb-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="13"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
            </svg>
            <span>Server Anda belum memenuhi syarat. Perbaiki error di atas lalu refresh.</span>
        </div>
    @else
        <div class="notice notice-info mt-16 mb-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
            <span>Server siap digunakan! Silakan lanjut ke aktivasi lisensi.</span>
        </div>
    @endif
@endsection

@section('foot-l')
    <a href="{{ route('installer.step1') }}" class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="1 4 1 10 7 10"/>
            <path d="M3.51 15a9 9 0 1 0 .49-5"/>
        </svg>
        Refresh
    </a>
@endsection

@section('foot-r')
    @if($allPassed)
        <a href="{{ route('installer.step2') }}" class="btn btn-primary">
            Lanjut
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
        </a>
    @else
        <button type="button" class="btn btn-primary" disabled>
            Lanjut
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
        </button>
    @endif
@endsection
