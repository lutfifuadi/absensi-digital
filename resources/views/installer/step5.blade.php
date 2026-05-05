@extends('installer.layout')

@section('step-chip',    'Langkah 5 — Admin')
@section('step-num',     '5')
@section('progress',     '100')
@section('step-title',   'Akun Administrator')
@section('step-desc',    'Langkah terakhir — buat akun pengelola utama sistem.')
@section('form-action',  route('installer.process'))

@section('content')
    <div id="install-form-container">
        <div class="notice notice-info" style="margin-bottom: 22px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span>Data sekolah tersimpan. Sekarang, mari buat akun administrator Anda.</span>
        </div>

        <div class="section-label">Akun Administrator</div>
        <div class="grid-2 mt-12">
            <div class="field">
                <label class="lbl">Nama Lengkap</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" placeholder="Nama Admin" required>
                </div>
            </div>
            <div class="field">
                <label class="lbl">Email Admin</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin@sekolah.com" required>
                </div>
            </div>
        </div>

        <div class="grid-2 mt-12">
            <div class="field">
                <label class="lbl">Username Admin</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </span>
                    <input type="text" name="admin_username" value="{{ old('admin_username') }}" placeholder="admin_sekolah" required>
                </div>
            </div>
            <div class="field">
                <label class="lbl">Password Admin</label>
                <div class="inp-wrap has-icon">
                    <span class="inp-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" name="admin_password" placeholder="Min. 6 karakter" required>
                </div>
            </div>
        </div>

        <div class="mt-20" style="background: var(--surface-2); padding: 16px; border-radius: 12px; border: 1px dashed var(--surface-3);">
            <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                <input type="checkbox" name="include_dummy_data" value="1" style="margin-top: 4px; width: 18px; height: 18px; accent-color: var(--primary);">
                <div style="flex: 1;">
                    <span style="display: block; font-weight: 600; color: var(--text); font-size: 0.9375rem;">Sertakan Data Sampel (Dummy Data)</span>
                    <span style="display: block; font-size: 0.8125rem; color: var(--text-muted); margin-top: 2px;">
                        Centang ini untuk menambahkan data contoh seperti Tahun Akademik, Kelas, Siswa, dan Guru secara otomatis. Sangat disarankan untuk uji coba sistem.
                    </span>
                </div>
            </label>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="install-loading" style="display: none; padding: 60px 20px; text-align: center; animation: fade-in 0.4s cubic-bezier(0.4, 0, 0.2, 1) both;">
        <div class="loader-wrapper" style="position: relative; width: 80px; height: 80px; margin: 0 auto 32px;">
            <div class="spinner-outer" style="position: absolute; inset: 0; border: 4px solid var(--surface-3); border-top-color: var(--primary); border-radius: 50%; animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;"></div>
            <div class="spinner-inner" style="position: absolute; inset: 12px; border: 3px solid transparent; border-top-color: #7c6cf5; border-radius: 50%; opacity: 0.6; animation: spin 0.8s linear infinite reverse;"></div>
            <div class="check-icon" id="success-check" style="display: none; position: absolute; inset: 0; align-items: center; justify-content: center; color: var(--success); transform: scale(0.5); opacity: 0; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
        </div>

        <h3 id="loading-title" style="color: var(--text); margin-bottom: 12px; font-weight: 700; font-size: 1.25rem; letter-spacing: -0.02em;">Menginstal Sistem...</h3>
        <p style="color: var(--text-muted); font-size: 0.9375rem; max-width: 320px; margin: 0 auto; line-height: 1.6;" id="loading-status">Sistem sedang menyiapkan struktur database dan konfigurasi dasar. Mohon tunggu sejenak.</p>
        
        <div class="progress-container" style="width: 100%; height: 8px; background: var(--surface-3); border-radius: 10px; margin-top: 40px; overflow: hidden; position: relative;">
            <div id="progress-bar" style="width: 5%; height: 100%; background: linear-gradient(90deg, var(--primary), #9d92f8); transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 10px; position: relative;">
                <div style="position: absolute; inset: 0; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent); animation: pulse-shine 1.5s infinite;"></div>
            </div>
        </div>
        <div id="progress-percent" style="margin-top: 10px; font-size: 0.75rem; font-weight: 600; color: var(--primary); font-variant-numeric: tabular-nums;">5%</div>
    </div>

    <style>
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes pulse-shine { from { transform: translateX(-100%); } to { transform: translateX(100%); } }
        @keyframes fade-in { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        .progress-glow {
            box-shadow: 0 0 15px rgba(124, 108, 245, 0.4);
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            const form = $('#installer-form');
            const submitBtn = $('#btn-submit');
            const formContainer = $('#install-form-container');
            const loadingOverlay = $('#install-loading');
            const loadingTitle = $('#loading-title');
            const loadingStatus = $('#loading-status');
            const progressBar = $('#progress-bar');
            const progressPercent = $('#progress-percent');
            const backBtn = $('#back-btn');
            const successCheck = $('#success-check');
            const spinOuter = $('.spinner-outer');
            const spinInner = $('.spinner-inner');

            form.on('submit', function(e) {
                e.preventDefault();

                // Start transition
                formContainer.css({'pointer-events': 'none', 'opacity': '0.5'});
                
                setTimeout(() => {
                    formContainer.fadeOut(400, function() {
                        loadingOverlay.fadeIn(400);
                        backBtn.fadeOut(200);
                        submitBtn.fadeOut(200);
                        
                        // Smart Progress Simulation
                        let progress = 5;
                        const includeDummy = $('input[name="include_dummy_data"]').is(':checked');
                        const statusSteps = [
                            { p: 12, t: 'Menghubungkan ke database...', s: 'Memastikan koneksi aman dan stabil.' },
                            { p: 30, t: 'Migrasi Struktur...', s: 'Membangun tabel dan relasi sistem utama.' },
                            { p: 50, t: 'Konfigurasi Sekolah...', s: 'Menyimpan profil, alamat, dan kontak lembaga.' },
                            { p: 70, t: 'Membuat Akun Admin...', s: 'Mendaftarkan akses administrator utama Anda.' },
                        ];

                        if (includeDummy) {
                            statusSteps.push({ p: 85, t: 'Menyuntikkan Data Sampel...', s: 'Menyiapkan kelas, siswa, dan guru contoh untuk Anda.' });
                        }

                        statusSteps.push({ p: 94, t: 'Finalisasi...', s: 'Membersihkan cache dan mengaktifkan sistem.' });

                        let stepIdx = 0;
                        // Use a faster interval (400ms) for smoother animation
                        const interval = setInterval(() => {
                            if (progress < 98) {
                                // Dynamic increment: slower as it gets higher
                                let inc = (Math.random() * 1.5) + 0.5;
                                if (progress > 80) inc *= 0.5;
                                
                                progress += inc;
                                if (progress > 98) progress = 98;
                                
                                progressBar.css('width', progress + '%');
                                progressPercent.text(Math.round(progress) + '%');

                                if (stepIdx < statusSteps.length && progress >= statusSteps[stepIdx].p) {
                                    loadingTitle.fadeOut(200, function() {
                                        $(this).text(statusSteps[stepIdx].t).fadeIn(200);
                                    });
                                    loadingStatus.fadeOut(200, function() {
                                        $(this).text(statusSteps[stepIdx].s).fadeIn(200);
                                        stepIdx++;
                                    });
                                }
                            }
                        }, 400);

                        // Actual AJAX request
                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: form.serialize(),
                            headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() },
                            success: function(response) {
                                clearInterval(interval);
                                
                                // Success state UI
                                progressBar.addClass('progress-glow').css('width', '100%');
                                progressPercent.text('100%');
                                loadingTitle.text('Instalasi Selesai!');
                                loadingStatus.text('Sistem Anda siap digunakan. Mengalihkan...');
                                
                                spinOuter.fadeOut(300);
                                spinInner.fadeOut(300);
                                
                                setTimeout(() => {
                                    successCheck.css('display', 'flex');
                                    // Trigger reflow for animation
                                    successCheck[0].offsetHeight;
                                    successCheck.css({'opacity': '1', 'transform': 'scale(1)'});
                                }, 350);

                                setTimeout(() => { window.location.href = response.redirect; }, 2500);
                            },
                            error: function(xhr) {
                                clearInterval(interval);
                                loadingOverlay.hide();
                                formContainer.show().css({'pointer-events': 'auto', 'opacity': '1'});
                                backBtn.show();
                                submitBtn.show();
                                
                                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                                alert('Gagal: ' + msg);
                            }
                        });
                    });
                }, 300);
            });
        });
    </script>
@endsection

@section('foot-l')
    <a href="{{ route('installer.step4') }}" class="btn btn-ghost" id="back-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
@endsection

@section('foot-r')
    <button type="submit" class="btn btn-success btn-wide" id="btn-submit">
        <span data-icon>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
            </svg>
        </span>
        <span data-label>Selesaikan Instalasi</span>
    </button>
@endsection
