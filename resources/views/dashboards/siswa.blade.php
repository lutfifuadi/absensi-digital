@extends('layouts/layoutMaster')

@section('title', 'Dashboard Siswa')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .glass-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2) !important;
      background: rgba(255, 255, 255, 0.06) !important;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
      font-size: 1.5rem;
    }

    .absen-pulse {
      box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
      animation: absen-pulse 2s infinite;
    }

    @keyframes absen-pulse {
      0% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.4); }
      70% { box-shadow: 0 0 0 15px rgba(40, 199, 111, 0); }
      100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0); }
    }
  </style>
@endsection

@section('content')
  @php
    $siswaRecord = \App\Models\Siswa::where('user_id', $user->id)->first();
    $kelasNama = $siswaRecord && $siswaRecord->kelas ? $siswaRecord->kelas->nama : 'Belum Ada Kelas';
    $totalIzinSaya = $siswaRecord
        ? \App\Models\IzinSakit::where('tipe', 'siswa')->where('reference_id', $siswaRecord->id)->count()
        : 0;
    $izinDisetujui = $siswaRecord
        ? \App\Models\IzinSakit::where('tipe', 'siswa')
            ->where('reference_id', $siswaRecord->id)
            ->where('status', 'disetujui')
            ->count()
        : 0;
    $absensiSaya = $siswaRecord
        ? \App\Models\AbsensiSiswa::where('siswa_id', $siswaRecord->id)->whereDate('tanggal', today())->first()
        : null;
    
    $absenMandiriEnabled = \App\Models\Pengaturan::where('key', 'izinkan_lokasi_absensi_mandiri')->value('value') === 'Ya';
    $aktifkanBunyi = \App\Models\Pengaturan::where('key', 'aktifkan_bunyi_notif_absensi')->value('value') === 'Ya';
    $freqHadir = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_hadir')->value('value') ?: 880);
    $freqTerlambat = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_terlambat')->value('value') ?: 440);
    $freqStreak = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_streak')->value('value') ?: 523);
    $freqEarly = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_early')->value('value') ?: 698);
    $freqNormal = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_normal')->value('value') ?: 523);
    $freqLate = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_late')->value('value') ?: 349);
    $freqCheckout = (int)(\App\Models\Pengaturan::where('key', 'freq_bunyi_checkout')->value('value') ?: 392);
  @endphp

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%); border-radius: 12px;">
        <div class="card-body p-4 p-md-5">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-lg"
                  style="width:64px;height:64px;border-radius:16px !important;background:rgba(255,255,255,0.2);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.3);">
                  <i class="ti tabler-school text-white fs-2"></i>
                </div>
                <div>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Halo, {{ explode(' ', $user->name)[0] }}!</h4>
                  <p class="mb-0 text-white opacity-90 fw-medium">Siswa Kelas <span class="text-warning fw-bold">{{ $kelasNama }}</span></p>
                </div>
              </div>
              <p class="mb-0 text-white opacity-75">Pantau jadwal, pengajuan izin, dan lakukan absensi mandiri dengan cepat.</p>
            </div>
            <div class="col-md-5 text-md-end mt-4 mt-md-0">
               <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                  <a href="{{ route('siswa.download-kartu') }}" class="btn btn-white text-primary fw-bold shadow-sm" target="_blank">
                    <i class="ti tabler-id-badge me-2"></i> Unduh Kartu Pelajar
                  </a>
                  <div class="badge bg-black bg-opacity-20 p-2 px-3 border border-white border-opacity-10 text-white d-flex align-items-center justify-content-center">
                    <i class="ti tabler-calendar me-1"></i> {{ now()->locale('id')->translatedFormat('d M Y') }}
                  </div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row gy-4 mb-4">
    {{-- STATS AREA --}}
    <div class="col-md-4">
       <div class="row gy-4">
          <div class="col-6 col-md-12">
            <div class="card glass-card border-0 shadow-sm overflow-hidden">
               <div class="card-body p-4 text-center">
                  <div class="stat-icon mx-auto bg-label-warning">
                    <i class="ti tabler-door"></i>
                  </div>
                  <h5 class="mb-1 text-white fw-bold">{{ $kelasNama }}</h5>
                  <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Kelas Aktif</small>
               </div>
            </div>
          </div>
          <div class="col-6 col-md-12">
            <div class="card glass-card border-0 shadow-sm overflow-hidden">
               <div class="card-body p-4 text-center">
                  <div class="stat-icon mx-auto bg-label-info">
                    <i class="ti tabler-circle-check"></i>
                  </div>
                  <h5 class="mb-1 text-white fw-bold">{{ $izinDisetujui }} / {{ $totalIzinSaya }}</h5>
                  <small class="text-white-50 opacity-50 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Izin Disetujui</small>
               </div>
            </div>
          </div>
          <div class="col-12 col-md-12">
            <div class="card glass-card border-0 shadow-sm overflow-hidden" style="background: linear-gradient(135deg, rgba(40,199,111,0.2) 0%, rgba(40,199,111,0.05) 100%) !important; border-color: rgba(40,199,111,0.2) !important;">
               <div class="card-body p-4 text-center">
                  <div class="stat-icon mx-auto bg-label-success absen-pulse">
                    <i class="ti tabler-flame"></i>
                  </div>
                  <h5 class="mb-1 text-white fw-bold">{{ $attendance_streak ?? 0 }} Hari</h5>
                  <small class="text-white-50 opacity-75 text-uppercase fw-bold" style="font-size:0.65rem; letter-spacing:1px;">Attendance Streak</small>
               </div>
            </div>
          </div>
       </div>
    </div>

    {{-- ABSENSI MANDIRI / GEOFENCING AREA --}}
    <div class="col-md-8">
      <div class="card glass-card border-0 shadow-sm h-100 overflow-hidden">
        <div class="card-header border-bottom py-3" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
          <h6 class="mb-0 text-white d-flex align-items-center gap-2">
            <i class="ti tabler-map-pin text-danger"></i> Absensi Mandiri (Lokasi Terdeteksi)
          </h6>
        </div>
        <div class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4">
            @if($absensiSaya && $absensiSaya->jam_masuk && $absensiSaya->jam_pulang)
                {{-- KASUS 1: SUDAH MASUK & PULANG --}}
                <div class="text-center">
                    <div class="avatar avatar-xl bg-label-success mx-auto mb-3 shadow-lg" style="width:80px; height:80px;">
                        <span class="avatar-initial rounded-circle"><i class="ti tabler-circle-check fs-1"></i></span>
                    </div>
                    <h4 class="mb-1 text-white fw-bold">Selesai Untuk Hari Ini!</h4>
                    <p class="text-success mb-3 fw-bold fs-6">{{ $greeting_message ?? 'Terima kasih, Anda sudah melakukan absensi hari ini.' }}</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <div class="p-3 bg-black bg-opacity-20 rounded border border-white border-opacity-10">
                            <div class="text-white fw-bold font-monospace fs-5">{{ $absensiSaya->jam_masuk }}</div>
                            <div class="text-white-50 small opacity-50" style="font-size:0.7rem;">Masuk</div>
                        </div>
                        <div class="p-3 bg-black bg-opacity-20 rounded border border-white border-opacity-10">
                            <div class="text-white fw-bold font-monospace fs-5">{{ $absensiSaya->jam_pulang }}</div>
                            <div class="text-white-50 small opacity-50" style="font-size:0.7rem;">Pulang</div>
                        </div>
                    </div>
                </div>
            @elseif($absenMandiriEnabled)
                {{-- KASUS 2: ABSEN MANDIRI AKTIF --}}
                <div class="max-w-px-450 mx-auto w-100">
                    <div class="row g-3">
                        {{-- TOMBOL MASUK --}}
                        <div class="col-6">
                            @if($absensiSaya && $absensiSaya->jam_masuk)
                                <div class="bg-black bg-opacity-20 p-3 rounded h-100 border border-success border-opacity-30 d-flex flex-column align-items-center justify-content-center">
                                    <i class="ti tabler-circle-check text-success fs-2 mb-1"></i>
                                    <div class="text-white fw-bold fs-5">{{ $absensiSaya->jam_masuk }}</div>
                                    <div class="text-success small fw-bold" style="font-size:0.7rem;">Tercatat Masuk</div>
                                </div>
                            @else
                                <button type="button" class="btn btn-primary btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex flex-column align-items-center gap-1" id="btnAbsenMasuk">
                                    <i class="ti tabler-login fs-2"></i>
                                    <span>Absen Masuk</span>
                                </button>
                            @endif
                        </div>
                        {{-- TOMBOL PULANG --}}
                        <div class="col-6">
                            @if($absensiSaya && $absensiSaya->jam_pulang)
                                <div class="bg-black bg-opacity-20 p-3 rounded h-100 border border-info border-opacity-30 d-flex flex-column align-items-center justify-content-center">
                                    <i class="ti tabler-circle-check text-info fs-2 mb-1"></i>
                                    <div class="text-white fw-bold fs-5">{{ $absensiSaya->jam_pulang }}</div>
                                    <div class="text-info small fw-bold" style="font-size:0.7rem;">Tercatat Pulang</div>
                                </div>
                            @else
                                <button type="button" class="btn btn-warning btn-lg w-100 py-3 fw-bold shadow-lg h-100 d-flex flex-column align-items-center gap-1 {{ !$absensiSaya ? 'opacity-50' : '' }}" id="btnAbsenPulang" {{ !$absensiSaya ? 'disabled' : '' }}>
                                    <i class="ti tabler-logout fs-2"></i>
                                    <span>Absen Pulang</span>
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <div id="absenMessage" class="mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold d-none"></div>
                    
                    @if(!$absensiSaya)
                        <div class="mt-4 p-3 rounded-lg bg-label-info border-info border-opacity-10">
                            <p class="mb-0 text-white small"><i class="ti tabler-info-circle me-1"></i> Silakan tekan tombol <strong>Absen Masuk</strong> untuk memulai hari.</p>
                        </div>
                    @endif
                </div>
            @else
                {{-- KASUS 3: ABSEN MANDIRI NONAKTIF --}}
                <div class="py-5">
                    <div class="avatar avatar-xl bg-label-secondary mx-auto mb-3" style="width:72px; height:72px;">
                        <span class="avatar-initial rounded-circle"><i class="ti tabler-lock fs-1"></i></span>
                    </div>
                    <h5 class="text-white fw-bold">Absensi Mandiri Nonaktif</h5>
                    <p class="text-white-50 opacity-50 small mx-auto" style="max-width:320px;">Silakan hubungi Guru Piket atau Wali Kelas untuk melakukan pencatatan kehadiran.</p>
                </div>
            @endif
        </div>
      </div>
    </div>
  </div>

  <div class="row gy-4">
    {{-- BOTTOM ACTIONS --}}
    <div class="col-md-6 col-xl-4">
      <div class="card glass-card h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
             <div class="avatar bg-label-danger rounded p-1"><i class="ti tabler-stethoscope fs-4"></i></div>
             <h6 class="mb-0 text-white fw-bold">Izin & Sakit</h6>
          </div>
          <p class="text-white-50 flex-grow-1 small">Butuh izin berhalangan hadir? Ajukan surat keterangan atau izin langsung melalui portal ini.</p>
          <a href="{{ route('admin.izin-sakit.index') }}" class="btn btn-label-danger mt-3 fw-bold">
            <i class="ti tabler-file-plus me-1"></i> Ajukan Pengajuan Baru
          </a>
        </div>
      </div>
    </div>
    
    <div class="col-md-6 col-xl-8">
      <div class="card glass-card h-100 border-start border-4 border-info">
        <div class="card-body p-4">
          <h6 class="text-white fw-bold d-flex align-items-center gap-2 mb-3">
            <i class="ti tabler-info-circle text-info"></i> Panduan Penggunaan Geofencing
          </h6>
          <div class="row gy-3">
            <div class="col-md-6">
              <div class="d-flex align-items-start gap-3">
                <div class="text-info fs-4 position-relative top-1"><i class="ti tabler-gps"></i></div>
                <div>
                  <div class="text-white small fw-bold mb-1">Aktifkan GPS Perangkat</div>
                  <p class="text-white-50 small mb-0 opacity-75">Pastikan fitur Lokasi/GPS menyala sebelum menekan tombol absen.</p>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-start gap-3">
                <div class="text-info fs-4 position-relative top-1"><i class="ti tabler-browser-check"></i></div>
                <div>
                  <div class="text-white small fw-bold mb-1">Izinkan Akses Browser</div>
                  <p class="text-white-50 small mb-0 opacity-75">Tekan "Allow/Izinkan" saat browser meminta informasi lokasi Anda.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnMasuk = document.getElementById('btnAbsenMasuk');
    const btnPulang = document.getElementById('btnAbsenPulang');
    const msgBox = document.getElementById('absenMessage');

    const bunyiAktif = {{ $aktifkanBunyi ? 'true' : 'false' }};
    const freqs = {
        hadir: {{ $freqHadir }},
        terlambat: {{ $freqTerlambat }},
        streak: {{ $freqStreak }},
        early: {{ $freqEarly }},
        normal: {{ $freqNormal }},
        late: {{ $freqLate }},
        checkout: {{ $freqCheckout }}
    };

    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    
    const playSound = (type) => {
        if (!bunyiAktif) return;
        
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        const now = audioCtx.currentTime;
        
        let freq = freqs[type] || freqs.normal;
        if (type === 'streak_5' || type === 'streak_10' || type === 'streak_30') {
            freq = freqs.streak;
        }
        
        const soundConfigs = {
            'hadir': { freq: freq, type: 'sine', duration: 0.15, pattern: 'single' },
            'terlambat': { freq: freq, type: 'sine', duration: 0.2, pattern: 'descend' },
            'streak_5': { freq: freq, type: 'triangle', duration: 0.3, pattern: 'ascend' },
            'streak_10': { freq: freq, type: 'triangle', duration: 0.4, pattern: 'fanfare' },
            'streak_30': { freq: freq, type: 'triangle', duration: 0.5, pattern: 'fanfare' },
            'early': { freq: freqs.early, type: 'sine', duration: 0.2, pattern: 'energetic' },
            'normal': { freq: freqs.normal, type: 'sine', duration: 0.1, pattern: 'single' },
            'late': { freq: freqs.late, type: 'sine', duration: 0.25, pattern: 'descend' },
            'checkout': { freq: freqs.checkout, type: 'sine', duration: 0.3, pattern: 'warm' }
        };
        
        const config = soundConfigs[type] || soundConfigs['normal'];
        
        oscillator.type = config.type;
        
        if (config.pattern === 'descend') {
            oscillator.frequency.setValueAtTime(config.freq, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 0.5, now + config.duration);
        } else if (config.pattern === 'ascend') {
            oscillator.frequency.setValueAtTime(config.freq * 0.75, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq, now + config.duration);
        } else if (config.pattern === 'fanfare') {
            oscillator.frequency.setValueAtTime(config.freq * 0.5, now);
            oscillator.frequency.setValueAtTime(config.freq, now + 0.1);
            oscillator.frequency.setValueAtTime(config.freq * 1.25, now + 0.2);
            oscillator.frequency.setValueAtTime(config.freq * 1.5, now + config.duration - 0.1);
        } else if (config.pattern === 'pop') {
            oscillator.frequency.setValueAtTime(config.freq * 1.5, now);
            oscillator.frequency.exponentialRampToValueAtTime(config.freq, now + config.duration);
        } else if (config.pattern === 'energetic') {
            oscillator.frequency.setValueAtTime(config.freq * 0.8, now);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 1.2, now + config.duration);
        } else if (config.pattern === 'warm') {
            oscillator.frequency.setValueAtTime(config.freq * 0.75, now);
            oscillator.frequency.setValueAtTime(config.freq, now + config.duration * 0.5);
            oscillator.frequency.linearRampToValueAtTime(config.freq * 0.5, now + config.duration);
        } else {
            oscillator.frequency.setValueAtTime(config.freq, now);
        }
        
        gainNode.gain.setValueAtTime(0.3, now);
        gainNode.gain.exponentialRampToValueAtTime(0.01, now + config.duration);
        
        oscillator.start(now);
        oscillator.stop(now + config.duration);
    };

    const handleAbsen = (btn) => {
        if (!btn) return;
        
        btn.addEventListener('click', function () {
            btn.disabled = true;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm mb-1"></span><span class="small">Scanning...</span>';
            
            msgBox.innerHTML = 'Mendapatkan lokasi...';
            msgBox.className = 'mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold text-info block';
            msgBox.classList.remove('d-none');

            if (!navigator.geolocation) {
                showMsg('Browser tidak mendukung Geolocation.', 'text-danger');
                resetBtn(btn, originalHtml);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    showMsg('Lokasi ditemukan. Mengirim data...', 'text-info');

                    fetch('{{ route('siswa.absensi-mandiri.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            lat: lat, 
                            lng: lng, 
                            accuracy: position.coords.accuracy
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            showMsg('<i class="ti tabler-check"></i> ' + data.message, 'text-success');
                            
                            const status = data.status || 'hadir';
                            const milestone = data.milestone_type;
                            const timeCtx = data.time_context;
                            
                            playSound(status);
                            
                            if (milestone) {
                                setTimeout(() => playSound(milestone), 200);
                            } else if (timeCtx) {
                                setTimeout(() => playSound(timeCtx), 200);
                            }
                            
                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            showMsg('<i class="ti tabler-alert-circle"></i> ' + data.message, 'text-danger');
                            resetBtn(btn, originalHtml);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showMsg('Terjadi kesalahan jaringan.', 'text-danger');
                        resetBtn(btn, originalHtml);
                    });
                },
                function(error) {
                    let errStr = 'Gagal mendapatkan lokasi.';
                    if(error.code === error.PERMISSION_DENIED) errStr = 'Akses lokasi ditolak.';
                    showMsg(errStr, 'text-danger');
                    resetBtn(btn, originalHtml);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        });
    };

    handleAbsen(btnMasuk);
    handleAbsen(btnPulang);

    function showMsg(text, className) {
        msgBox.innerHTML = text;
        msgBox.className = 'mt-4 p-2 rounded bg-black bg-opacity-10 small fw-bold ' + className;
        msgBox.classList.remove('d-none');
    }

    function resetBtn(btn, html) {
        btn.disabled = false;
        btn.innerHTML = html;
    }
});
</script>
@endpush

