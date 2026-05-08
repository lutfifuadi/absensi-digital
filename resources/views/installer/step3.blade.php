@extends('installer.layout')

@section('step-chip',    'Langkah 3 — Database')
@section('step-num',     '3')
@section('progress',     '60')
@section('step-title',   'Konfigurasi Database')
@section('step-desc',    'Hubungkan aplikasi dengan database MySQL atau SQLite.')
@section('form-action',  route('installer.step3Submit'))

@section('content')
    <div class="notice notice-ok" style="margin-bottom: 22px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span>Lisensi aktif! Sekarang silakan hubungkan database Anda.</span>
    </div>

    @if(session('db_warning'))
    <div class="notice notice-warn" style="margin-bottom: 22px; border-left: 4px solid var(--warning); background: rgba(255, 171, 0, 0.08); padding: 16px; border-radius: 8px;">
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffab00" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <div>
                <strong style="color: #ffab00; display: block; margin-bottom: 4px;">Peringatan Data Eksis</strong>
                <span style="font-size: 0.875rem; color: var(--text-2);">{{ session('db_warning') }}</span>
                
                <label style="display: flex; align-items: center; gap: 10px; margin-top: 15px; cursor: pointer; padding: 10px; background: rgba(255,171,0,0.12); border-radius: 6px; border: 1px dashed #ffab00;">
                    <input type="checkbox" name="confirm_wipe" value="1" style="width: 18px; height: 18px; accent-color: #ffab00;">
                    <span style="font-size: 0.8125rem; font-weight: 600; color: #ffab00;">Ya, hapus data lama dan lakukan instalasi bersih</span>
                </label>
            </div>
        </div>
    </div>
    @endif

    <div class="field">
        <label class="lbl">Tipe Database</label>
        <select name="db_connection" id="db_connection" class="select2">
            <option value="mysql" {{ old('db_connection', session('install_db_connection', 'mysql')) == 'mysql' ? 'selected' : '' }}>MySQL</option>
            <option value="mariadb" {{ old('db_connection', session('install_db_connection')) == 'mariadb' ? 'selected' : '' }}>MariaDB</option>
            <option value="sqlite" {{ old('db_connection', session('install_db_connection')) == 'sqlite' ? 'selected' : '' }}>SQLite (Lokal)</option>
        </select>
    </div>

    <div id="db_mysql_fields">
        <div class="grid-2 mt-16">
            <div class="field">
                <label class="lbl">Host Database</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </span>
                    <input type="text" name="db_host" value="{{ old('db_host', session('install_db_host', '127.0.0.1')) }}" placeholder="127.0.0.1">
                </div>
            </div>
            <div class="field">
                <label class="lbl">Port</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </span>
                    <input type="text" name="db_port" value="{{ old('db_port', session('install_db_port', '3306')) }}" placeholder="3306">
                </div>
            </div>
            <div class="field">
                <label class="lbl">DB Username</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <input type="text" name="db_user" value="{{ old('db_user', session('install_db_user', 'root')) }}" placeholder="root">
                </div>
            </div>
            <div class="field">
                <label class="lbl">DB Password</label>
                <div class="inp-wrap has-icon" style="position: relative;">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" name="db_pass" id="db_pass" value="{{ old('db_pass', session('install_db_pass')) }}" placeholder="Password db" style="padding-right: 36px;">
                    <span class="toggle-password" data-target="db_pass" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-sub); display: flex; align-items: center; justify-content: center; opacity: 0.7; transition: opacity 0.2s;">
                        <svg class="eye-off" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                        <svg class="eye" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="field mt-12">
        <label class="lbl" id="db_name_label">Nama Database</label>
        <div class="inp-wrap has-icon">
            <span class="inp-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
            </span>
            <input type="text" name="db_name" value="{{ old('db_name', session('install_db_name')) }}" placeholder="absensi_db" required>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#db_connection').on('change', function() {
                const val = $(this).val();
                const mysqlFields = $('#db_mysql_fields');
                const dbNameLabel = $('#db_name_label');
                const dbNameInput = $('input[name="db_name"]');
                
                if (val === 'mysql' || val === 'mariadb') {
                    mysqlFields.slideDown(200);
                    dbNameLabel.text('Nama Database');
                    dbNameInput.attr('placeholder', 'absensi_db');
                } else if (val === 'sqlite') {
                    mysqlFields.slideUp(200);
                    dbNameLabel.text('Path Database SQLite');
                    dbNameInput.attr('placeholder', 'database/database.sqlite');
                }
            });

            // Trigger on load
            $('#db_connection').trigger('change');

            $('.toggle-password').on('click', function() {
                const target = $('#' + $(this).data('target'));
                const eye = $(this).find('.eye');
                const eyeOff = $(this).find('.eye-off');
                
                if (target.attr('type') === 'password') {
                    target.attr('type', 'text');
                    eye.show();
                    eyeOff.hide();
                    $(this).css('opacity', '1');
                } else {
                    target.attr('type', 'password');
                    eye.hide();
                    eyeOff.show();
                    $(this).css('opacity', '0.7');
                }
            });
        });
    </script>
@endsection


@section('foot-l')
    <a href="{{ route('installer.step2') }}" class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
    <button type="button" class="btn btn-ghost btn-publish-assets" title="Publish Livewire Assets" style="color: #f59e0b; border-color: rgba(245,158,11,0.3);">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        Livewire Assets
    </button>
@endsection

@section('foot-r')
    <button type="submit" class="btn btn-primary btn-wide" id="btn-submit" data-loading="Menghubungkan...">
        <span data-label>Cek Koneksi & Lanjut</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </button>
@endsection
