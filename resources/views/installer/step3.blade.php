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

    <div class="field">
        <label class="lbl">Tipe Database</label>
        <select name="db_connection" id="db_connection" class="select2">
            <option value="mysql" {{ old('db_connection', 'mysql') == 'mysql' ? 'selected' : '' }}>MySQL</option>
            <option value="mariadb" {{ old('db_connection') == 'mariadb' ? 'selected' : '' }}>MariaDB</option>
            <option value="sqlite" {{ old('db_connection') == 'sqlite' ? 'selected' : '' }}>SQLite (Lokal)</option>
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
                    <input type="text" name="db_host" value="{{ old('db_host','127.0.0.1') }}" placeholder="127.0.0.1">
                </div>
            </div>
            <div class="field">
                <label class="lbl">Port</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    </span>
                    <input type="text" name="db_port" value="{{ old('db_port','3306') }}" placeholder="3306">
                </div>
            </div>
            <div class="field">
                <label class="lbl">DB Username</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <input type="text" name="db_user" value="{{ old('db_user','root') }}" placeholder="root">
                </div>
            </div>
            <div class="field">
                <label class="lbl">DB Password</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" name="db_pass" placeholder="Password db">
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
            <input type="text" name="db_name" value="{{ old('db_name') }}" placeholder="absensi_db" required>
        </div>
    </div>

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
        });
    </script>
@endsection

@section('foot-l')
    <a href="{{ route('installer.step2') }}" class="btn btn-ghost">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
@endsection

@section('foot-r')
    <button type="submit" class="btn btn-primary btn-wide" id="btn-submit" data-loading="Menghubungkan...">
        <span data-label>Cek Koneksi & Lanjut</span>
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </svg>
    </button>
@endsection
