@extends('layouts/layoutMaster')

@section('title', 'Dashboard Utama — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — identitas sekolah + jam live
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner">
      {{-- Identitas --}}
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          @if (isset($pengaturanArr['logo_sekolah']))
            <img src="{{ (filter_var($pengaturanArr['logo_sekolah'], FILTER_VALIDATE_URL) || str_starts_with($pengaturanArr['logo_sekolah'], 'http://') || str_starts_with($pengaturanArr['logo_sekolah'], 'https://')) ? $pengaturanArr['logo_sekolah'] : asset('uploads/logo/' . $pengaturanArr['logo_sekolah']) }}" alt="Logo {{ $pengaturanArr['nama_sekolah'] ?? 'sekolah' }}" class="das-hero__logo">
          @else
            <div class="das-hero__logo-placeholder">
              <i class="ti tabler-school" aria-hidden="true"></i>
            </div>
          @endif
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Sistem Administrasi Sekolah
          </div>
          <h1 class="das-hero__school">{{ $pengaturanArr['nama_sekolah'] ?? $pengaturanArr['nama_lembaga'] ?? 'Sistem Absensi' }}</h1>
          <p class="das-hero__welcome">Selamat datang kembali, <strong>{{ $user->name }}</strong> <span aria-hidden="true">👋</span></p>
        </div>
      </div>

      {{-- Chronos Luxury Sport Watch — Reference Design (Hublot-inspired) --}}
      <div class="d-flex flex-column align-items-center text-center">
        <div class="chronos-watch-container">

          <!-- TOP STRAP LUG -->
          <div class="chronos-strap-lug top-lug"></div>

          <!-- MAIN WATCH BODY -->
          <div class="chronos-body">

            <!-- SIDE CROWN & PUSHERS (Right side) -->
            <div class="chronos-side-buttons">
              <div class="side-btn top-btn"><div class="btn-grip"></div></div>
              <div class="side-btn mid-crown"><div class="btn-grip"></div></div>
              <div class="side-btn bot-btn"><div class="btn-grip"></div></div>
            </div>

            <!-- OCTAGONAL BEZEL WITH CORNER SCREWS -->
            <div class="chronos-bezel-outer">
              <div class="outer-screw sc-1"></div>
              <div class="outer-screw sc-2"></div>
              <div class="outer-screw sc-3"></div>
              <div class="outer-screw sc-4"></div>
              <div class="outer-screw sc-5"></div>
              <div class="outer-screw sc-6"></div>
              <div class="outer-screw sc-7"></div>
              <div class="outer-screw sc-8"></div>

              <!-- CIRCULAR BEZEL RING WITH TICK MARKS -->
              <div class="chronos-bezel-ring">
                <div id="chronosBezelTicks"></div>

                <!-- CARBON FIBER DIAL FACE -->
                <div class="chronos-dial-face" id="chronosDial">
                  <div class="dial-inner-ring"></div>
                  <div id="chronosLumiBars"></div>

                  <div class="chronos-logo-area">
                    <div class="brand-emblem">◈</div>
                    <div class="brand-name">INDONESIA</div>
                    <div class="brand-auto">AUTOMATIC | 100M</div>
                  </div>

                  <!-- LEFT SUB-DIAL (9 o'clock — Orange) -->
                  <div class="chronos-sub sub-left">
                    <div id="subLeftTicks"></div>
                    <div class="sub-hand" id="chronosSubLeftHand"></div>
                    <div class="sub-center-dot"></div>
                  </div>

                  <!-- RIGHT SUB-DIAL (3 o'clock — Blue) -->
                  <div class="chronos-sub sub-right">
                    <div id="subRightTicks"></div>
                    <div class="sub-hand" id="chronosSubRightHand"></div>
                    <div class="sub-center-dot"></div>
                  </div>

                  <!-- BOTTOM LCD DISPLAY -->
                  <div class="chronos-lcd">
                    <div class="lcd-inner">
                      <span class="lcd-day" id="chronosLcdDay">RAB 25</span>
                      <span class="lcd-sep">|</span>
                      <span class="lcd-time" id="chronosLcdTime">00:00:00</span>
                    </div>
                  </div>

                  <div class="hand hour-hand" id="chronosHourHand"></div>
                  <div class="hand minute-hand" id="chronosMinuteHand"></div>
                  <div class="hand second-hand" id="chronosSecondHand"></div>
                  <div class="chronos-cap"></div>
                  <div class="glass-glare"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- BOTTOM STRAP LUG -->
          <div class="chronos-strap-lug bot-lug"></div>
        </div>
      </div>
    </div>
  </div>{{-- /das-hero --}}

  {{-- WIDGET PENGUMUMAN TARGET --}}
  <x-pengumuman-widget />


  {{-- ═══════════════════════════════════════════════════════
       SECTION 1B: STATS ROW — 4 Card Statistik Dinamis & Interaktif
       ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card 1: Tingkat Kehadiran --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.laporan.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-success h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti tabler-percentage fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $tingkatKehadiran }}%</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Tingkat Kehadiran</p>
            <p class="mb-0">
              <span class="text-success fw-medium me-2">{{ $hadirCount + $terlambatCount }} Siswa</span>
              <small class="text-body-secondary">hadir & terlambat</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 2: Siswa Terlambat --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.absensi-siswa.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-warning h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-warning">
                  <i class="ti tabler-clock-exclamation fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $terlambatCount }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Siswa Terlambat</p>
            <p class="mb-0">
              <span class="text-warning fw-medium me-2">Evaluasi Kehadiran</span>
              <small class="text-body-secondary">butuh tindakan</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 3: Izin & Sakit --}}
    <div class="col-lg-3 col-sm-6">
      <a href="{{ route('admin.izin-sakit.index') }}" class="text-decoration-none stats-card-link">
        <div class="card card-grad-info h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-clipboard-check fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $izinCount + $sakitCount }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Izin & Sakit</p>
            <p class="mb-0">
              <span class="text-info fw-medium me-2">Keterangan Resmi</span>
              <small class="text-body-secondary">sakit/izin</small>
            </p>
          </div>
        </div>
      </a>
    </div>

    {{-- Card 4: Belum Presensi --}}
    <div class="col-lg-3 col-sm-6">
      <a href="#" class="text-decoration-none stats-card-link" data-bs-toggle="modal" data-bs-target="#modalBelumAbsen">
        <div class="card card-grad-danger h-100">
          <div class="card-body">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar me-4">
                <span class="avatar-initial rounded bg-label-danger">
                  <i class="ti tabler-user-question fs-4"></i>
                </span>
              </div>
              <h4 class="mb-0 fw-semibold">{{ $belumAbsen }}</h4>
            </div>
            <p class="mb-1 text-body-secondary text-nowrap">Belum Presensi</p>
            <p class="mb-0">
              <span class="text-danger fw-medium me-2">Tindakan Segera</span>
              <small class="text-body-secondary">butuh konfirmasi</small>
            </p>
          </div>
        </div>
      </a>
    </div>
  </div>{{-- /row g-6 mb-6 (Stats Row) --}}

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1B3: WIDGET KONEKTIVITAS SERVICE WHATSAPP
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-6">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08) !important; border-radius: 12px; backdrop-filter: blur(10px);">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded bg-label-success">
                  <i class="ti tabler-brand-whatsapp fs-4"></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                  Konektivitas Service WhatsApp
                  <span class="badge bg-label-info font-normal" style="font-size:0.7rem;">Monitoring Server</span>
                </h5>
                <small class="text-body-secondary">Pengecekan real-time status WA Gateway Notif, Validator WA, Notif Pengaduan, dan Autoreply WA</small>
              </div>
            </div>
            <button type="button" id="btnRefreshWaStatus" class="btn btn-sm btn-label-info d-inline-flex align-items-center gap-1 fw-semibold">
              <i class="ti tabler-refresh" id="iconRefreshWa"></i> Check Status
            </button>
          </div>

          <div class="row g-3" id="waServicesStatusContainer">
            {{-- Card 1: WA Gateway Notif --}}
            <div class="col-xl-3 col-lg-6 col-md-6">
              <div class="p-3 rounded border" id="card-wa_gateway_notif" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-bell-ringing text-info fs-5"></i>
                    <span class="fw-semibold text-white small">WA Gateway Notif</span>
                  </div>
                  <span class="badge bg-label-secondary status-badge" id="badge-wa_gateway_notif">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Memeriksa...
                  </span>
                </div>
                <div class="text-body-secondary small text-truncate" id="msg-wa_gateway_notif" style="font-size:0.75rem;">
                  Mengontak server...
                </div>
              </div>
            </div>

            {{-- Card 2: Validator WA --}}
            <div class="col-xl-3 col-lg-6 col-md-6">
              <div class="p-3 rounded border" id="card-validator_wa" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-circle-check text-primary fs-5"></i>
                    <span class="fw-semibold text-white small">Validator WA</span>
                  </div>
                  <span class="badge bg-label-secondary status-badge" id="badge-validator_wa">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Memeriksa...
                  </span>
                </div>
                <div class="text-body-secondary small text-truncate" id="msg-validator_wa" style="font-size:0.75rem;">
                  Mengontak server...
                </div>
              </div>
            </div>

            {{-- Card 3: Notif Pengaduan --}}
            <div class="col-xl-3 col-lg-6 col-md-6">
              <div class="p-3 rounded border" id="card-notif_pengaduan" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-message-dots text-warning fs-5"></i>
                    <span class="fw-semibold text-white small">Notif Pengaduan WA</span>
                  </div>
                  <span class="badge bg-label-secondary status-badge" id="badge-notif_pengaduan">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Memeriksa...
                  </span>
                </div>
                <div class="text-body-secondary small text-truncate" id="msg-notif_pengaduan" style="font-size:0.75rem;">
                  Mengontak server...
                </div>
              </div>
            </div>

            {{-- Card 4: Autoreply WA --}}
            <div class="col-xl-3 col-lg-6 col-md-6">
              <div class="p-3 rounded border" id="card-autoreply_wa" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-robot text-success fs-5"></i>
                    <span class="fw-semibold text-white small">Autoreply WA</span>
                  </div>
                  <span class="badge bg-label-secondary status-badge" id="badge-autoreply_wa">
                    <span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Memeriksa...
                  </span>
                </div>
                <div class="text-body-secondary small text-truncate" id="msg-autoreply_wa" style="font-size:0.75rem;">
                  Mengontak server...
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  {{-- ═══════════════════════════════════════════════════════
       SECTION 1B2: WIDGET BELUM ABSEN — Mini Chart + Daftar Siswa
  ═══════════════════════════════════════════════════════ --}}
  @if($belumAbsen > 0 || $isWeekend)
  <div class="row g-4 mb-6">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(234,84,85,0.06) 0%, rgba(234,84,85,0.02) 100%); border: 1px solid rgba(234,84,85,0.15) !important; border-radius: 12px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded" style="background: rgba(234,84,85,0.12); color: #ea5455;">
                  <i class="ti tabler-user-off fs-4"></i>
                </span>
              </div>
              <div>
                @if($isWeekend)
                  <h5 class="mb-0 fw-bold" style="color: #ea5455;"><i class="ti tabler-calendar-off me-1"></i> Hari Libur</h5>
                  <small class="text-body-secondary">Hari ini {{ \Carbon\Carbon::today()->locale('id')->isoFormat('dddd') }} — tidak ada absensi siswa</small>
                @else
                  <h5 class="mb-0 fw-bold" style="color: #ea5455;">{{ $belumAbsen }} Siswa Belum Absen</h5>
                  <small class="text-body-secondary">Hari ini — dari {{ $totalSiswaWajibAbsen }} siswa wajib absen</small>
                @endif
              </div>
            </div>
            @if(!$isWeekend)
            <a href="{{ route('admin.dashboard.belum-absen') }}" class="btn btn-sm btn-label-danger d-inline-flex align-items-center gap-1 fw-semibold">
              <i class="ti tabler-arrow-right"></i> Lihat Semua
            </a>
            @endif
          </div>

          <div class="row g-4">
            @if($isWeekend)
            <div class="col-12 text-center py-4">
              <i class="ti tabler-calendar-off fs-1 d-block mb-2" style="color: rgba(234,84,85,0.3);"></i>
              <span class="text-body-secondary">Tidak ada data absensi di hari libur</span>
            </div>
            @else
            {{-- Kiri: Mini Bar Chart per Kelas --}}
            <div class="col-lg-5 col-md-6">
              <h6 class="text-body-secondary small fw-semibold mb-3"><i class="ti tabler-chart-bar me-1"></i> Top 10 Kelas Belum Absen</h6>
              @if(count($belumAbsenPerKelas) > 0)
                @foreach($belumAbsenPerKelas as $item)
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="text-body-secondary small" style="min-width: 70px;">{{ $item['nama'] }}</span>
                  <div class="flex-grow-1" style="height: 20px; background: rgba(234,84,85,0.08); border-radius: 4px; overflow: hidden;">
                    <div style="height: 100%; width: {{ $totalSiswaWajibAbsen > 0 ? round(($item['belum_absen'] / max(1, max(array_column($belumAbsenPerKelas, 'belum_absen'))) * 100)) : 0 }}%; background: linear-gradient(90deg, #ea5455, #f06464); border-radius: 4px; transition: width 0.6s ease;"></div>
                  </div>
                  <span class="badge bg-label-danger fw-bold" style="font-size: 0.72rem; min-width: 28px; text-align: center;">{{ $item['belum_absen'] }}</span>
                </div>
                @endforeach
              @else
                <div class="text-center text-body-secondary py-4">
                  <i class="ti tabler-mood-happy fs-3 d-block mb-1" style="color: rgba(40,199,111,0.4);"></i>
                  <small>Semua kelas sudah lengkap absennya</small>
                </div>
              @endif
            </div>

            {{-- Kanan: Daftar Nama Siswa --}}
            <div class="col-lg-7 col-md-6">
              <h6 class="text-body-secondary small fw-semibold mb-3"><i class="ti tabler-users me-1"></i> Siswa Belum Absen</h6>
              @if(count($listBelumAbsen) > 0)
                <div class="table-responsive" style="border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; background: rgba(255,255,255,0.02);">
                  <table class="table table-hover align-middle mb-0" style="font-size: 0.82rem;">
                    <thead>
                      <tr style="background: rgba(255,255,255,0.03);">
                        <th class="text-white fw-semibold py-2 ps-3 text-nowrap">Nama</th>
                        <th class="text-white fw-semibold py-2 text-nowrap">Kelas</th>
                        <th class="text-white fw-semibold py-2 pe-3 text-nowrap">Wali Kelas</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($listBelumAbsen as $siswa)
                      <tr style="border-color: rgba(255,255,255,0.04);">
                        <td class="ps-3 text-nowrap">
                          <div class="d-flex align-items-center gap-2">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(115,103,240,0.15); color: #a5a2f7; font-weight: 700; font-size: 0.7rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              {{ substr($siswa['nama'], 0, 1) }}
                            </div>
                            <span class="text-white fw-medium">{{ $siswa['nama'] }}</span>
                          </div>
                        </td>
                        <td class="text-nowrap"><span class="badge bg-label-info fw-semibold" style="font-size: 0.68rem;">{{ $siswa['kelas'] }}</span></td>
                        <td class="pe-3 text-nowrap">
                          <span class="text-body-secondary small">{{ $siswa['wali_kelas'] }}</span>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                @if($belumAbsen > 5)
                <div class="text-center mt-2">
                  <a href="{{ route('admin.dashboard.belum-absen') }}" class="text-danger small fw-semibold text-decoration-none">
                    + {{ $belumAbsen - 5 }} siswa lainnya <i class="ti tabler-arrow-right"></i>
                  </a>
                </div>
                @endif
              @else
                <div class="text-center text-body-secondary py-4">
                  <i class="ti tabler-mood-happy fs-3 d-block mb-1" style="color: rgba(40,199,111,0.4);"></i>
                  <small>Semua siswa sudah absen hari ini</small>
                </div>
              @endif
            </div>
            @endif
          </div>

        </div>
      </div>
    </div>
  </div>
  @endif


  {{-- ═══════════════════════════════════════════════════════
       SECTION 1C: INFO AKADEMIK + QUICK MENU
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card Informasi Akademik --}}
    <div class="col-lg-8">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="row align-items-center">
            {{-- Kiri: Detail Kelas --}}
            <div class="col-12 col-md-6 mb-4 mb-md-0">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="avatar">
                  <span class="avatar-initial rounded bg-label-primary">
                    <i class="ti tabler-school fs-4"></i>
                  </span>
                </div>
                <div>
                  <h6 class="mb-0">Informasi Akademik</h6>
                  <small class="text-body-secondary">Tahun Ajaran {{ $tahunAkademikAktif->nama ?? 'Aktif' }} {{ $tahunAkademikAktif->semester ?? '' }}</small>
                </div>
              </div>
              <div class="d-flex flex-wrap gap-0">
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-primary fw-bold">{{ $totalKelas }}</h3>
                  <small class="text-body-secondary">Total Kelas</small>
                </div>
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-success fw-bold">{{ $totalSiswa }}</h3>
                  <small class="text-body-secondary">Total Siswa</small>
                </div>
                <div class="text-center px-3 border-end border-secondary border-opacity-25 pe-4">
                  <h3 class="mb-0 text-warning fw-bold">{{ $totalSiswaWajibAbsen ?? $totalSiswa }}</h3>
                  <small class="text-body-secondary">Wajib Absen</small>
                </div>
                <div class="text-center px-3">
                  <h3 class="mb-0 text-info fw-bold">{{ $totalGuru }}</h3>
                  <small class="text-body-secondary">Total Guru</small>
                </div>
              </div>
            </div>

            {{-- Kanan: Tahun Ajaran Active Badge --}}
            <div class="col-12 col-md-6 text-md-end">
              <div class="d-inline-flex align-items-center gap-2 p-3 rounded-3 bg-label-primary bg-opacity-10 shadow-sm">
                <i class="ti tabler-calendar-stats fs-2 text-primary"></i>
                <div class="text-start">
                  <small class="text-body-secondary d-block">Semester Aktif</small>
                  <span class="fw-bold fs-5">{{ $tahunAkademikAktif->semester ?? 'Ganjil' }} {{ $tahunAkademikAktif->nama ?? date('Y') }}</span>
                  <br>
                  <small class="text-body-secondary">
                    {{ $tahunAkademikAktif ? \Carbon\Carbon::parse($tahunAkademikAktif->tanggal_mulai)->translatedFormat('d M Y') : '-' }}
                    —
                    {{ $tahunAkademikAktif ? \Carbon\Carbon::parse($tahunAkademikAktif->tanggal_selesai)->translatedFormat('d M Y') : '-' }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Card Quick Menu --}}
    <div class="col-lg-4">
      <div class="card card-grad-gold h-100">
        <div class="card-body d-flex flex-column justify-content-center">
          <div class="das-quick-v2 d-grid gap-2">
            @php
              $quickLinks = [
                ['icon' => 'tabler-database', 'label' => 'Master', 'route' => route('admin.master-data'), 'color' => 'primary'],
                ['icon' => 'tabler-school', 'label' => 'Absensi', 'route' => route('admin.absensi-siswa.index'), 'color' => 'success'],
                ['icon' => 'tabler-report-analytics', 'label' => 'Laporan', 'route' => route('admin.laporan.index'), 'color' => 'warning'],
                ['icon' => 'tabler-clipboard-check', 'label' => 'Izin', 'route' => route('admin.izin-sakit.index'), 'color' => 'danger'],
                ['icon' => 'tabler-users', 'label' => 'Users', 'route' => route('admin.users.index'), 'color' => 'dark'],
                ['icon' => 'tabler-settings', 'label' => 'Settings', 'route' => route('admin.pengaturan.index'), 'color' => 'info'],
                ['icon' => 'tabler-cloud-download', 'label' => 'Update', 'route' => route('admin.update.index'), 'color' => 'primary'],
                ['icon' => 'tabler-scan', 'label' => 'Scan', 'route' => route('public.kegiatan.index'), 'color' => 'danger'],
              ];
            @endphp
            @foreach ($quickLinks as $link)
              <a href="{{ $link['route'] }}"
                 class="d-flex flex-column align-items-center gap-1 p-3 rounded-2 bg-label-{{ $link['color'] }} text-decoration-none"
                 style="min-width:70px">
                <i class="ti {{ $link['icon'] }} fs-4"></i>
                <small class="fw-medium text-body" style="font-size:0.7rem">{{ $link['label'] }}</small>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>


  {{-- ================================================================
     SECTION 2: ANALYTICS GRID -- Vuexy-native Bootstrap row
     ================================================================ --}}
  <div class="row g-6">

    {{-- -- Row 1, Col 1: School Overview (static card) -- --}}
    <div class="col-xl-6 col-12">
      <div class="card swiper-card-advance-bg h-100">
        <div class="card-body">
          <div class="row">
            <div class="col-12">
              <h5 class="text-white mb-0">{{ $pengaturanArr['nama_sekolah'] ?? 'Sekolah' }}</h5>
              <small class="text-white opacity-75">Sistem Presensi Digital</small>
            </div>
            <div class="col-lg-7 col-md-9 col-12 pt-md-9">
              <h6 class="text-white mt-0 mt-md-3 mb-4">Statistik Hari Ini</h6>
              <div class="row">
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $hadirCount + $terlambatCount }}</p>
                      <p class="mb-0 text-white">Hadir</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $sakitCount + $izinCount }}</p>
                      <p class="mb-0 text-white">Izin/Sakit</p>
                    </li>
                  </ul>
                </div>
                <div class="col-6">
                  <ul class="list-unstyled mb-0">
                    <li class="d-flex mb-4 align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $alphaCount }}</p>
                      <p class="mb-0 text-white">Alpha</p>
                    </li>
                    <li class="d-flex align-items-center">
                      <p class="mb-0 fw-medium me-2 website-analytics-text-bg fs-4 fw-bold">{{ $belumAbsen }}</p>
                      <p class="mb-0 text-white">Belum Absen</p>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-lg-5 col-md-3 col-12 my-4 my-md-0 text-center">
              <div class="d-flex align-items-center justify-content-center h-100">
                <div class="text-center opacity-30">
                  <i class="ti tabler-school" style="font-size: 8rem; color: #D4A94A;"></i>
                </div>
              </div>
            </div>
          </div>
          {{-- Bottom mini-stats: Guru & Staff --}}
          <div class="row mt-4 pt-2 border-top border-white border-opacity-10">
            <div class="col-6">
              <p class="text-white mb-0 small opacity-75">
                <i class="ti tabler-chalkboard-teacher me-1"></i> Guru: {{ $absensiGuruHariIni }}/{{ $totalGuru }}
              </p>
            </div>
            <div class="col-6">
              <p class="text-white mb-0 small opacity-75">
                <i class="ti tabler-user-check me-1"></i> Staff: {{ $absensiStaffHariIni }}/{{ $totalStaff }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 1, Col 3: Overview Kehadiran -- --}}
    @php
      $totalWajib = $totalSiswa ?? ($hadirCount + $terlambatCount + $sakitCount + $izinCount + $alphaCount + $belumAbsen);
      $hadirTotal = $hadirCount + $terlambatCount;
      $hadirPct = $totalWajib > 0 ? round(($hadirTotal / $totalWajib) * 100, 1) : 0;
      $tidakPct = $totalWajib > 0 ? round((($totalWajib - $hadirTotal) / $totalWajib) * 100, 1) : 0;
    @endphp
    <div class="col-xl-6 col-sm-6">
      <div class="card card-grad-primary h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between">
            <p class="mb-0 text-body">Overview Kehadiran</p>
            <p class="card-text fw-medium text-success">{{ $hadirPct }}%</p>
          </div>
          <h4 class="card-title mb-1">{{ $hadirTotal }} <small class="text-body fw-normal">dari {{ $totalWajib }}</small></h4>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-4">
              <div class="d-flex gap-2 align-items-center mb-2">
                <span class="badge bg-label-success p-1 rounded"><i class="ti tabler-circle-check icon-sm"></i></span>
                <p class="mb-0">Hadir</p>
              </div>
              <h5 class="mb-0 pt-1 text-success">{{ $hadirPct }}%</h5>
              <small class="text-body-secondary">{{ $hadirTotal }}</small>
            </div>
            <div class="col-4">
              <div class="divider divider-vertical">
                <div class="divider-text">
                  <span class="badge-divider-bg bg-label-secondary">VS</span>
                </div>
              </div>
            </div>
            <div class="col-4 text-end">
              <div class="d-flex gap-2 justify-content-end align-items-center mb-2">
                <p class="mb-0">Tidak</p>
                <span class="badge bg-label-danger p-1 rounded"><i class="ti tabler-ban icon-sm"></i></span>
              </div>
              <h5 class="mb-0 pt-1 text-danger">{{ $tidakPct }}%</h5>
              <small class="text-body-secondary">{{ $totalWajib - $hadirTotal }}</small>
            </div>
          </div>
          <div class="d-flex align-items-center mt-6">
            <div class="progress w-100" style="height: 8px; border-radius: 4px;">
              <div class="progress-bar bg-success" style="width: {{ $hadirPct }}%" role="progressbar" aria-valuenow="{{ $hadirPct }}" aria-valuemin="0" aria-valuemax="100"></div>
              <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $tidakPct }}%" aria-valuenow="{{ $tidakPct }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 2, Col 1: Tren Kehadiran (7 Hari) — selaras dengan siswa dashboard -- --}}
    <div class="col-md-6">
      <div class="siswa-chart-card h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot das-panel__icon-dot--success"></span>
            Tren Kehadiran — 7 Hari Terakhir
          </div>
          <a href="{{ route('admin.laporan.index') }}" class="das-btn das-btn--ghost" style="font-size:0.72rem;">
            <i class="ti tabler-external-link me-1"></i> Lihat Semua
          </a>
        </div>
        <div class="das-chart-mount">
          <div id="chartKehadiranMingguan" style="min-height:200px;width:100%;"></div>
        </div>
        <div class="px-4 pb-4 pt-2 border-top" style="border-color: rgba(231,236,245,0.06) !important;">
          <div class="row text-center g-3">
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(47, 191, 113, 0.05); border: 1px solid rgba(47, 191, 113, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Rerata Hadir</small>
                <h5 class="mb-0 text-success fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $rataRataHadir }}</h5>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(58, 183, 224, 0.05); border: 1px solid rgba(58, 183, 224, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Hari Terbaik</small>
                <h5 class="mb-0 text-info fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $hariTerbaik }}</h5>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2" style="background: rgba(239, 90, 90, 0.05); border: 1px solid rgba(239, 90, 90, 0.1);">
                <small class="text-body-secondary d-block mb-1 text-uppercase text-nowrap" style="font-size: 0.65rem; letter-spacing: 0.5px;">Ketidakhadiran</small>
                <h5 class="mb-0 text-danger fw-bold text-nowrap" style="font-size: 0.95rem;">{{ $tingkatKetidakhadiran }}</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 2, Col 2: Attendance Tracker -- --}}
    <div class="col-md-6">
      <div class="card card-grad-success h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1">Attendance Tracker</h5>
            <p class="card-subtitle">Hari Ini</p>
          </div>
          <span class="badge bg-label-primary">{{ now()->translatedFormat('d F Y') }}</span>
        </div>
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-md-6 col-12">
              <div class="mb-4">
                <span class="text-body-secondary small text-uppercase letter-spacing-1 d-block mb-1">Total Hadir Hari Ini</span>
                <h2 class="mb-0 fw-black text-success" style="font-size: 2.2rem; font-family: 'Plus Jakarta Sans', sans-serif;">{{ $hadirCount + $terlambatCount }}</h2>
              </div>
              @php
                $trackerItems = [
                  ['label' => 'Hadir', 'val' => $hadirCount, 'color' => 'success', 'icon' => 'tabler-circle-check'],
                  ['label' => 'Terlambat', 'val' => $terlambatCount, 'color' => 'warning', 'icon' => 'tabler-clock-exclamation'],
                  ['label' => 'Sakit', 'val' => $sakitCount, 'color' => 'info', 'icon' => 'tabler-heart'],
                  ['label' => 'Izin', 'val' => $izinCount, 'color' => 'warning', 'icon' => 'tabler-clipboard-check'],
                  ['label' => 'Alpha', 'val' => $alphaCount, 'color' => 'danger', 'icon' => 'tabler-ban'],
                  ['label' => 'Belum Absen', 'val' => $belumAbsen, 'color' => 'dark', 'icon' => 'tabler-user-question'],
                ];
              @endphp
              <div class="row g-2">
                @foreach ($trackerItems as $item)
                  <div class="col-6">
                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); transition: all 0.2s ease;">
                      <div class="badge rounded bg-label-{{ $item['color'] }} p-1 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; flex-shrink: 0;">
                        <i class="ti {{ $item['icon'] }}" style="font-size: 0.95rem;"></i>
                      </div>
                      <div class="overflow-hidden">
                        <small class="text-body-secondary d-block text-truncate" style="font-size: 0.68rem; font-weight: 500;">{{ $item['label'] }}</small>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.88rem; line-height: 1.2;">{{ $item['val'] }}</h6>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
            <div class="col-md-6 col-12 d-flex justify-content-center align-items-center mt-3 mt-md-0">
              @if ($totalAbsensiHariIni > 0 || $totalSiswa > 0)
                <div id="chartDonutStatus" class="w-100" style="max-width: 250px;"></div>
              @else
                <div class="d-flex flex-column align-items-center justify-content-center py-5 text-body-secondary">
                  <i class="ti tabler-chart-pie fs-1 mb-2" aria-hidden="true"></i>
                  <span>Belum ada data</span>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- -- Row 4, Col 1: Metode Absensi -- --}}
    <div class="col-xxl-4 col-md-6 col-12">
      <div class="card card-grad-warning h-100">
        <div class="card-header d-flex justify-content-between">
          <div class="card-title mb-0">
            <h5 class="mb-1">Metode Absensi</h5>
            <p class="card-subtitle">Cara masuk hari ini</p>
          </div>
        </div>
        <div class="card-body">
          <ul class="list-unstyled mb-0">
            @forelse ($metodeAbsensi ?? [] as $metode)
              @php
                $iconMap = [
                  'qr' => 'tabler-qrcode',
                  'manual' => 'tabler-keyboard',
                  'face' => 'tabler-scan',
                  'fingerprint' => 'tabler-fingerprint',
                  'kartu' => 'tabler-credit-card',
                ];
                $metodeIcon = $iconMap[$metode['key']] ?? 'tabler-device-analytics';
              @endphp
              <li class="das-metode-item mb-3 p-2 rounded-2 d-flex align-items-center">
                <div class="badge bg-label-secondary text-body p-2 me-4 rounded">
                  <i class="ti {{ $metodeIcon }} icon-md"></i>
                </div>
                <div class="d-flex justify-content-between w-100 flex-wrap gap-2">
                  <div class="me-2">
                    <h6 class="mb-0">{{ $metode['label'] }}</h6>
                  </div>
                  <div class="d-flex align-items-center">
                    <span class="badge bg-label-secondary rounded-pill fw-bold">{{ $metode['total'] }}</span>
                  </div>
                </div>
              </li>
            @empty
              <li class="d-flex align-items-center justify-content-center py-4 text-body-secondary">
                <span>Belum ada data</span>
              </li>
            @endforelse
          </ul>
        </div>
      </div>
    </div>

    {{-- -- Row 4, Col 2: Log Absensi Real-time -- --}}
    <div class="col-xxl-8">
      <div class="card card-grad-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="card-title mb-0">
            <h5 class="mb-1">Log Absensi Real-time</h5>
            <p class="card-subtitle">5 Absensi Terakhir</p>
          </div>
          <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2" onclick="refreshDashboardData()" title="Refresh">
            <i class="ti tabler-refresh icon-md"></i>
          </button>
        </div>
        <div class="table-responsive mb-4">
          <table class="table datatable-project table-sm table-hover das-log-table">
            <thead style="background: rgba(255,255,255,0.04); border-top: 1px solid rgba(255,255,255,0.08);">
              <tr>
                <th class="fw-medium">Waktu</th>
                <th class="fw-medium">Nama Siswa</th>
                <th class="fw-medium">Kelas</th>
                <th class="fw-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($recentLogs ?? [] as $log)
                <tr>
                  <td class="text-body-secondary font-monospace">
                    <small>{{ $log->jam_masuk ?? ($log->created_at ? $log->created_at->format('H:i:s') : '-') }}</small>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="https://ui-avatars.com/api/?name={{ urlencode($log->siswa->nama_lengkap ?? 'Unknown') }}&background=2FBF71&color=fff&size=28"
                           class="rounded-circle me-2" width="28" height="28" alt="" loading="lazy">
                      <span>{{ $log->siswa->nama_lengkap ?? '-' }}</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge bg-label-info rounded-pill">{{ $log->siswa->kelas->nama ?? $log->kelas->nama ?? '-' }}</span>
                  </td>
                  <td>
                    @php
                      $statusMap = [
                        'hadir' => ['badge' => 'bg-label-success', 'icon' => 'tabler-circle-check'],
                        'terlambat' => ['badge' => 'bg-label-warning', 'icon' => 'tabler-clock-exclamation'],
                        'sakit' => ['badge' => 'bg-label-info', 'icon' => 'tabler-heart'],
                        'izin' => ['badge' => 'bg-label-warning', 'icon' => 'tabler-clipboard-check'],
                        'alpha' => ['badge' => 'bg-label-danger', 'icon' => 'tabler-ban'],
                      ];
                      $status = $statusMap[$log->status] ?? ['badge' => 'bg-label-secondary', 'icon' => 'tabler-question-mark'];
                    @endphp
                    <span class="badge {{ $status['badge'] }} rounded-pill">
                      <i class="ti {{ $status['icon'] }} me-1"></i>
                      {{ ucfirst($log->status ?? '-') }}
                    </span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-body-secondary py-4">Belum ada log absensi</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Widget Siswa dengan Poin Tertinggi --}}
    <div class="col-12 mt-4">
      <div class="card card-grad-danger">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="card-title mb-0">
            <h5 class="mb-1 text-danger"><i class="ti tabler-shield-alert me-2"></i>Siswa dengan Poin Pelanggaran Tertinggi</h5>
            <p class="card-subtitle text-body-secondary">Top 5 Pelanggaran Tahun Akademik Aktif</p>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 text-white-50">
            <thead style="background: rgba(255,255,255,0.04); font-size:0.75rem; text-transform:uppercase;">
              <tr>
                <th class="ps-4">Siswa</th>
                <th class="text-center">Kelas</th>
                <th class="text-center">Total Poin</th>
                <th class="text-center">Badge SP</th>
                <th class="pe-4 text-end">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($top5Pelanggaran ?? [] as $tp)
                <tr>
                  <td class="ps-4 text-white">
                    <div class="d-flex align-items-center">
                      <span class="avatar-initial rounded-circle bg-label-{{ $tp->jenis_kelamin === 'L' ? 'info' : 'danger' }} me-2" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; font-size:0.75rem;">
                        {{ strtoupper(substr($tp->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($tp->nama_lengkap, ' ') ?: $tp->nama_lengkap, 1, 1)) }}
                      </span>
                      <span>{{ $tp->nama_lengkap }}</span>
                    </div>
                  </td>
                  <td class="text-center">
                    <span class="badge bg-label-info">{{ $tp->kelas->nama ?? '-' }}</span>
                  </td>
                  <td class="text-center fw-bold text-danger">
                    {{ (int) $tp->pelanggaran_siswa_sum_poin_saat_itu }}
                  </td>
                  <td class="text-center">
                    @php
                      $spTerbaru = $tp->pelanggaranSp->first();
                      $levelSp = $spTerbaru ? $spTerbaru->level_sp : null;
                      $spColor = match ($levelSp) {
                          'SP1' => 'warning',
                          'SP2' => 'danger',
                          'SP3' => 'dark',
                          default => 'secondary',
                      };
                    @endphp
                    @if ($levelSp)
                      <span class="badge bg-label-{{ $spColor }}">{{ $levelSp }}</span>
                    @else
                      <span class="text-white-50">-</span>
                    @endif
                  </td>
                  <td class="pe-4 text-end">
                    <a href="{{ route('admin.pelanggaran-siswa.profil-siswa', $tp) }}" class="btn btn-sm btn-icon btn-label-info">
                      <i class="ti tabler-eye"></i>
                    </a>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center py-4">Tidak ada data pelanggaran siswa.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>{{-- /row g-6 --}}


  {{-- ═══════════════════════════════════════════════════════
       MODAL: BELUM ABSEN
  ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalBelumAbsen" tabindex="-1" aria-hidden="true" aria-labelledby="modalBelumAbsenLabel">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head">
          <h5 class="das-modal__title" id="modalBelumAbsenLabel"><i class="ti tabler-user-question me-2" aria-hidden="true"></i>Siswa Belum Absen</h5>
          <button type="button" class="das-modal__close" data-bs-dismiss="modal" aria-label="Tutup"><i class="ti tabler-x" aria-hidden="true"></i></button>
        </div>
        <div class="das-modal__body">
          <div class="das-modal__stat">
            <div class="das-modal__stat-val">{{ $belumAbsen }}</div>
            <div class="das-modal__stat-label">Total Siswa Belum Absen Hari Ini</div>
            <div class="das-modal__stat-warn"><i class="ti tabler-alert-circle" aria-hidden="true"></i> Segera lakukan follow up.</div>
          </div>
          
          {{-- Search Input --}}
          <div class="mb-4">
            <div class="input-group input-group-merge" style="border: 1px solid rgba(231, 236, 245, 0.08); border-radius: 5px; overflow: hidden; background: rgba(255, 255, 255, 0.02);">
              <span class="input-group-text border-0 bg-transparent" id="siswa-search-icon" style="padding-left: 1rem;"><i class="ti tabler-search text-body-secondary fs-4"></i></span>
              <input type="text" id="search-siswa-belum-absen" class="form-control border-0 bg-transparent text-white focus:ring-0" placeholder="Cari nama siswa atau kelas..." aria-label="Cari nama siswa atau kelas..." aria-describedby="siswa-search-icon" style="box-shadow: none; font-size: 0.88rem; padding: 0.6rem 0.5rem;">
            </div>
          </div>

          {{-- Table Data --}}
          <div class="table-responsive das-table-wrap mb-4" style="border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 5px; background: var(--das-surface-2);">
            <table class="table table-hover das-table mb-0">
              <thead>
                <tr style="background: rgba(255, 255, 255, 0.03);">
                  <th class="py-3 text-white text-center text-nowrap" style="width: 50px;">#</th>
                  <th class="py-3 text-white text-nowrap">Nama Siswa</th>
                  <th class="py-3 text-white text-nowrap">Kelas</th>
                  <th class="py-3 text-white text-nowrap">No HP Orang Tua</th>
                  <th class="py-3 text-white text-center text-nowrap" style="width: 130px;">Aksi</th>
                </tr>
              </thead>
              <tbody id="tbody-siswa-belum-absen">
                <!-- Data loaded via AJAX -->
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          <div id="pagination-siswa-belum-absen" class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2">
            <!-- Pagination loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 6: RINGKASAN DATA PENGADUAN MASUK
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-6 mt-5">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(255,159,67,0.06) 0%, rgba(115,103,240,0.03) 100%); border: 1px solid rgba(255,159,67,0.18) !important; border-radius: 12px; backdrop-filter: blur(10px);">
        <div class="card-body p-4">
          {{-- Header --}}
          <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar">
                <span class="avatar-initial rounded" style="background: rgba(255,159,67,0.15); color: #ff9f43;">
                  <i class="ti tabler-message-report fs-3"></i>
                </span>
              </div>
              <div>
                <h5 class="mb-0 fw-bold text-white d-flex align-items-center gap-2">
                  Ringkasan Data Pengaduan Masuk
                  @if(($pengaduanPendingCount ?? 0) > 0)
                    <span class="badge bg-label-warning font-normal" style="font-size:0.75rem;">
                      <i class="ti tabler-alert-circle me-1"></i> {{ $pengaduanPendingCount }} Pengaduan Baru
                    </span>
                  @else
                    <span class="badge bg-label-success font-normal" style="font-size:0.75rem;">
                      <i class="ti tabler-check me-1"></i> Semua Pengaduan Teratasi
                    </span>
                  @endif
                </h5>
                <small class="text-body-secondary">Pusat pemantauan & tindak lanjut laporan pengaduan data civitas sekolah</small>
              </div>
            </div>
            <a href="{{ route('admin.pengaduan.index') }}" class="btn btn-sm btn-label-warning d-inline-flex align-items-center gap-1 font-semibold">
              <i class="ti tabler-arrow-right"></i> Kelola Seluruh Pengaduan
            </a>
          </div>

          <div class="row g-4">
            {{-- Stat KPI mini-cards --}}
            <div class="col-lg-4 col-md-5">
              <div class="d-flex flex-column gap-3">
                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: rgba(255,159,67,0.08); border: 1px solid rgba(255,159,67,0.15);">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-label-warning p-2 rounded"><i class="ti tabler-clock-play fs-5"></i></span>
                    <div>
                      <h6 class="mb-0 text-white fw-bold">Pengaduan Baru</h6>
                      <small class="text-body-secondary">Menunggu verifikasi</small>
                    </div>
                  </div>
                  <span class="h5 mb-0 fw-bold text-warning">{{ $pengaduanPendingCount ?? 0 }}</span>
                </div>

                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: rgba(0,207,221,0.08); border: 1px solid rgba(0,207,221,0.15);">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-label-info p-2 rounded"><i class="ti tabler-loader fs-5"></i></span>
                    <div>
                      <h6 class="mb-0 text-white fw-bold">Sedang Diproses</h6>
                      <small class="text-body-secondary">Dalam penanganan admin</small>
                    </div>
                  </div>
                  <span class="h5 mb-0 fw-bold text-info">{{ $pengaduanDiprosesCount ?? 0 }}</span>
                </div>

                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: rgba(40,199,111,0.08); border: 1px solid rgba(40,199,111,0.15);">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-label-success p-2 rounded"><i class="ti tabler-circle-check fs-5"></i></span>
                    <div>
                      <h6 class="mb-0 text-white fw-bold">Selesai Ditangani</h6>
                      <small class="text-body-secondary">Telah dikonfirmasi</small>
                    </div>
                  </div>
                  <span class="h5 mb-0 fw-bold text-success">{{ $pengaduanSelesaiCount ?? 0 }}</span>
                </div>
              </div>
            </div>

            {{-- Table list 5 pengaduan terbaru --}}
            <div class="col-lg-8 col-md-7">
              <div class="table-responsive" style="border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; background: rgba(255,255,255,0.02);">
                <table class="table align-middle mb-0" style="font-size: 0.82rem;">
                  <thead>
                    <tr style="background: rgba(255,255,255,0.04); font-size: 0.75rem; text-transform: uppercase;">
                      <th class="text-white fw-semibold py-2 ps-3">Pelapor</th>
                      <th class="text-white fw-semibold py-2">Kategori</th>
                      <th class="text-white fw-semibold py-2">Deskripsi Laporan</th>
                      <th class="text-white fw-semibold py-2 text-center pe-3">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($latestPengaduanList ?? [] as $p)
                      <tr style="border-color: rgba(255,255,255,0.04);">
                        <td class="ps-3 text-nowrap">
                          <div class="d-flex align-items-center gap-2">
                            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(255,159,67,0.15); color: #ff9f43; font-weight: 700; font-size: 0.7rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                              {{ strtoupper(substr($p->nama_lengkap ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                              <span class="text-white fw-medium d-block text-truncate" style="max-width:120px;">{{ $p->nama_lengkap }}</span>
                              <small class="text-body-secondary" style="font-size:0.68rem;">{{ ucfirst($p->status_pelapor ?? 'Pelapor') }}</small>
                            </div>
                          </div>
                        </td>
                        <td class="text-nowrap">
                          <span class="badge bg-label-info fw-semibold" style="font-size: 0.68rem;">{{ $p->kategori ?? 'Umum' }}</span>
                        </td>
                        <td>
                          <span class="text-white-50 text-truncate d-block" style="max-width: 220px;" title="{{ $p->deskripsi }}">
                            {{ \Illuminate\Support\Str::limit($p->deskripsi, 45) }}
                          </span>
                        </td>
                        <td class="text-center pe-3 text-nowrap">
                          @php
                            $badgeColor = match($p->status) {
                              'baru' => 'warning',
                              'diproses' => 'info',
                              'selesai' => 'success',
                              'ditolak' => 'danger',
                              default => 'secondary'
                            };
                          @endphp
                          <span class="badge bg-label-{{ $badgeColor }} px-2 py-1" style="font-size:0.7rem;">
                            {{ ucfirst($p->status) }}
                          </span>
                        </td>
                      </tr>
                    @empty
                      <tr>
                        <td colspan="4" class="text-center py-4 text-body-secondary">
                          <i class="ti tabler-message-check fs-3 d-block mb-1 text-success opacity-50"></i>
                          <small>Belum ada pengaduan data masuk.</small>
                        </td>
                      </tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection


@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
@endsection


@section('page-script')
  <script>
    /* ── LIVE CLOCK ── */
    (function() {
      function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) {
          el.textContent = new Date().toLocaleTimeString('id-ID', {
            hour12: false
          });
        }
      }
      updateClock();
      setInterval(updateClock, 1000);
    })();
  </script>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {

      /* ── SKELETON LOADING ── */
      setTimeout(() => {
        document.querySelectorAll('[data-skeleton]').forEach(el => {
          el.classList.add('--loaded');
        });
      }, 350);

      /* ── COUNTER ANIMATION (requestAnimationFrame, respects reduced motion) ── */
      const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      document.querySelectorAll('.counter-value').forEach(counter => {
        const target = +counter.getAttribute('data-target');
        if (!target || target === 0) { counter.innerText = target || 0; return; }
        if (prefersReducedMotion) { counter.innerText = target; return; }
        animateCounter(counter, target, 1000);
      });

      function animateCounter(el, target, duration = 1000) {
        const start = performance.now();
        function step(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          el.innerText = Math.floor(progress * target);
          if (progress < 1) requestAnimationFrame(step);
          else el.innerText = target;
        }
        requestAnimationFrame(step);
      }

      /* ── CHART THEME (shared tokens) ── */
      const chartFont = 'Inter, sans-serif';

      /* ── APEX: DONUT ── */
      @php
        $series = [$hadirCount, $sakitCount, $izinCount, $alphaCount, $terlambatCount];
        $labels = ['Hadir', 'Sakit', 'Izin', 'Alpha', 'Terlambat'];
      @endphp
      const donutEl = document.querySelector('#chartDonutStatus');
      let chartDonut;
      if (donutEl) {
        chartDonut = new ApexCharts(donutEl, {
          chart: { type: 'donut', height: 240, background: 'transparent', fontFamily: chartFont },
          theme: { mode: 'dark' },
          series: @json($series),
          labels: @json($labels),
          colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A', '#8B96AB'],
          legend: { show: false },
          dataLabels: { enabled: false },
          stroke: { show: true, width: 3, colors: ['#121B2E'] },
          plotOptions: {
            pie: {
              donut: {
                size: '78%',
                labels: {
                  show: true,
                  total: {
                    show: true,
                    label: 'Total',
                    color: '#8B96AB',
                    formatter: () => '{{ $totalAbsensiHariIni }}'
                  },
                  value: { color: '#E7ECF5', fontWeight: 700 }
                }
              }
            }
          },
          tooltip: { theme: 'dark', y: { formatter: v => v + ' Siswa' } },
          responsive: [
            { breakpoint: 576, options: { chart: { height: 200 } } },
            { breakpoint: 400, options: { chart: { height: 180 } } }
          ]
        });
        chartDonut.render();
      }

      /* ── APEX: AREA WEEKLY ── */
      const weeklyEl = document.querySelector('#chartKehadiranMingguan');
      let chartWeekly;
      if (weeklyEl) {
        chartWeekly = new ApexCharts(weeklyEl, {
          series: [
            { name: 'Hadir', data: @json($chartHadir) },
            { name: 'Sakit', data: @json($chartSakit) },
            { name: 'Izin', data: @json($chartIzin) },
            { name: 'Alpha', data: @json($chartAlpha) }
          ],
          chart: {
            type: 'area',
            height: 200,
            background: 'transparent',
            fontFamily: chartFont,
            toolbar: { show: false },
            animations: { enabled: !prefersReducedMotion, easing: 'easeinout', speed: 800 }
          },
          theme: { mode: 'dark' },
          stroke: { curve: 'smooth', width: 2.5 },
          fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.04, stops: [0, 90, 100] }
          },
          dataLabels: { enabled: false },
          colors: ['#2FBF71', '#3AB7E0', '#F0A63B', '#EF5A5A'],
          xaxis: {
            categories: @json($chartDays),
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#8B96AB', fontSize: '11px' } }
          },
          yaxis: { labels: { style: { colors: '#8B96AB' } } },
          grid: { borderColor: 'rgba(231,236,245,0.06)', strokeDashArray: 4 },
          legend: { position: 'top', horizontalAlign: 'right', labels: { colors: '#8B96AB' }, markers: { radius: 4 } },
          tooltip: { theme: 'dark', y: { formatter: v => v + ' Siswa' } },
          responsive: [
            { breakpoint: 768, options: { chart: { height: 180 }, legend: { position: 'bottom', horizontalAlign: 'center' } } },
            { breakpoint: 480, options: { chart: { height: 160 } } }
          ]
        });
        chartWeekly.render();
      }

      /* ── REFRESH DASHBOARD ── */
      window.refreshDashboardData = async function() {
        const btn = document.querySelector('.das-icon-btn') || document.querySelector('[onclick="refreshDashboardData()"]');
        if (btn) btn.classList.add('--spinning');
        try {
          const resp = await fetch("{{ route('admin.dashboard.refresh-stats') }}");
          const data = await resp.json();

          document.querySelectorAll('.counter-value').forEach(el => {
            const target = parseInt(el.getAttribute('data-target'));
            animateCounter(el, target, 600);
          });

          if (chartDonut) {
            chartDonut.updateSeries([data.hadirCount, data.sakitCount, data.izinCount, data.alphaCount, data.terlambatCount]);
          }
          if (chartWeekly) {
            chartWeekly.updateSeries([
              { name: 'Hadir', data: data.chartHadir },
              { name: 'Sakit', data: data.chartSakit },
              { name: 'Izin', data: data.chartIzin },
              { name: 'Alpha', data: data.chartAlpha }
            ]);
          }

        } catch (e) {
          console.error('Refresh error:', e);
        } finally {
          if (btn) btn.classList.remove('--spinning');
        }
      };

      /* ── POLLING: auto-refresh every 60s ── */
      setInterval(() => {
        if (typeof refreshDashboardData === 'function') {
          refreshDashboardData();
        }
      }, 60000);

      /* ── AJAX MODAL SISWA BELUM ABSEN ── */
      const modalBelumAbsen = document.getElementById('modalBelumAbsen');
      const searchInput = document.getElementById('search-siswa-belum-absen');
      const tbody = document.getElementById('tbody-siswa-belum-absen');
      const paginationContainer = document.getElementById('pagination-siswa-belum-absen');
      
      let currentPage = 1;
      let searchQuery = '';
      let searchTimeout = null;

      async function fetchSiswaBelumAbsen(page = 1, search = '') {
        // Tampilkan loading skeleton/indicator
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center py-5">
              <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <span class="text-body-secondary">Memuat data siswa...</span>
            </td>
          </tr>
        `;
        
        try {
          const url = new URL("{{ route('admin.dashboard.siswa-belum-absen') }}");
          url.searchParams.append('page', page);
          url.searchParams.append('search', search);
          url.searchParams.append('per_page', 5); // 5 data per halaman agar pas di modal

          const response = await fetch(url.toString(), {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          });
          const res = await response.json();

          if (res.success) {
            renderTableData(res.data, res.meta);
            renderPagination(res.meta);
          } else {
            showError('Gagal memuat data.');
          }
        } catch (error) {
          console.error('Fetch error:', error);
          showError('Terjadi kesalahan jaringan.');
        }
      }

      function renderTableData(data, meta) {
        if (data.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="5" class="text-center text-body-secondary py-5">
                <i class="ti tabler-search-off fs-1 d-block mb-2"></i>
                Tidak ada siswa belum absen yang ditemukan.
              </td>
            </tr>
          `;
          return;
        }

        const startIdx = (meta.current_page - 1) * meta.per_page;
        tbody.innerHTML = data.map((siswa, index) => {
          const waButton = siswa.wa_url && siswa.wa_url !== '#'
            ? `<a href="${siswa.wa_url}" target="_blank" class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 px-3 py-1.5 text-white" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); border: none; border-radius: 4px; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.25); font-weight: 600; font-size: 0.75rem; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 15px rgba(37, 211, 102, 0.4)';" onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 10px rgba(37, 211, 102, 0.25)';" onmousedown="this.style.transform='scale(0.98)';">
                 <i class="ti tabler-brand-whatsapp fs-5"></i>
                 <span>WhatsApp</span>
               </a>`
            : `<button class="btn btn-sm d-inline-flex align-items-center justify-content-center gap-1.5 px-3 py-1.5 text-white disabled" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 4px; font-weight: 600; font-size: 0.75rem;" disabled>
                 <i class="ti tabler-brand-whatsapp fs-5 text-body-secondary"></i>
                 <span class="text-body-secondary">WhatsApp</span>
               </button>`;

          return `
            <tr class="align-middle">
              <td class="text-center font-monospace text-body-secondary" style="font-size: 0.8rem;">${startIdx + index + 1}</td>
              <td>
                <div class="d-flex align-items-center">
                  <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(siswa.nama_lengkap)}&background=EF5A5A&color=fff&size=30" class="rounded-circle me-2.5" width="30" height="30" alt="" loading="lazy">
                  <span class="fw-semibold text-white" style="font-size: 0.85rem;">${siswa.nama_lengkap}</span>
                </div>
              </td>
              <td>
                <span class="badge bg-label-primary px-2.5 py-1" style="font-size: 0.72rem; font-weight: 600; border-radius: 4px;">${siswa.kelas}</span>
              </td>
              <td class="font-monospace text-body-secondary" style="font-size: 0.82rem;">${siswa.no_hp_ortu || '-'}</td>
              <td class="text-center py-2.5">${waButton}</td>
            </tr>
          `;
        }).join('');
      }

      function renderPagination(meta) {
        if (meta.last_page <= 1) {
          paginationContainer.innerHTML = `<span class="text-body-secondary small">Menampilkan ${meta.total} data siswa</span>`;
          return;
        }

        const from = (meta.current_page - 1) * meta.per_page + 1;
        const to = Math.min(meta.current_page * meta.per_page, meta.total);

        let paginationHtml = `<span class="text-body-secondary small">Menampilkan ${from}-${to} dari ${meta.total} data</span>`;
        paginationHtml += `<div class="btn-group gap-1" role="group" aria-label="Navigasi modal">`;

        // Prev Button
        paginationHtml += `
          <button type="button" class="btn btn-sm das-btn--ghost px-2.5 py-1 ${meta.current_page === 1 ? 'disabled' : ''}" 
                  ${meta.current_page === 1 ? 'disabled' : ''} 
                  onclick="changeModalPage(${meta.current_page - 1})">
            <i class="ti tabler-chevron-left fs-5"></i>
          </button>
        `;

        // Page Numbers (max 5 visible)
        let startPage = Math.max(1, meta.current_page - 2);
        let endPage = Math.min(meta.last_page, startPage + 4);
        if (endPage - startPage < 4) {
          startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
          paginationHtml += `
            <button type="button" class="btn btn-sm px-3 py-1 ${meta.current_page === i ? 'btn-primary fw-bold' : 'das-btn--ghost'}" 
                    style="${meta.current_page === i ? 'border-radius: 4px;' : ''}"
                    onclick="changeModalPage(${i})">
              ${i}
            </button>
          `;
        }

        // Next Button
        paginationHtml += `
          <button type="button" class="btn btn-sm das-btn--ghost px-2.5 py-1 ${meta.current_page === meta.last_page ? 'disabled' : ''}" 
                  ${meta.current_page === meta.last_page ? 'disabled' : ''} 
                  onclick="changeModalPage(${meta.current_page + 1})">
            <i class="ti tabler-chevron-right fs-5"></i>
          </button>
        `;

        paginationHtml += `</div>`;
        paginationContainer.innerHTML = paginationHtml;
      }

      function showError(message) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="text-center text-danger py-4">
              <i class="ti tabler-alert-circle fs-2 d-block mb-2"></i>
              ${message}
            </td>
          </tr>
        `;
        paginationContainer.innerHTML = '';
      }

      window.changeModalPage = function(page) {
        currentPage = page;
        fetchSiswaBelumAbsen(currentPage, searchQuery);
      };

      // Event listener: show.bs.modal
      if (modalBelumAbsen) {
        modalBelumAbsen.addEventListener('show.bs.modal', function () {
          currentPage = 1;
          searchQuery = '';
          if (searchInput) searchInput.value = '';
          fetchSiswaBelumAbsen(currentPage, searchQuery);
        });
      }

      // Debounced search input
      if (searchInput) {
        searchInput.addEventListener('input', function (e) {
          clearTimeout(searchTimeout);
          searchQuery = e.target.value;
          currentPage = 1;
          searchTimeout = setTimeout(() => {
            fetchSiswaBelumAbsen(currentPage, searchQuery);
          }, 400); // 400ms debounce
        });
      }
    });

    // ═══════════════════════════════════════════════════════════════
    // WA SERVICES CONNECTIVITY CHECKER
    // ═══════════════════════════════════════════════════════════════
    function checkWaServicesStatus() {
      const btn = document.getElementById('btnRefreshWaStatus');
      const icon = document.getElementById('iconRefreshWa');
      if (icon) icon.classList.add('ti-spin');
      if (btn) btn.disabled = true;

      const keys = ['wa_gateway_notif', 'validator_wa', 'notif_pengaduan', 'autoreply_wa'];
      keys.forEach(key => {
        const badge = document.getElementById(`badge-${key}`);
        const msg = document.getElementById(`msg-${key}`);
        if (badge) {
          badge.className = 'badge bg-label-secondary';
          badge.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width: 10px; height: 10px;"></span> Memeriksa...';
        }
        if (msg) msg.textContent = 'Mengontak server...';
      });

      fetch('{{ route("admin.wa-gateway.check-services-status") }}', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
        .then(res => res.json())
        .then(data => {
          if (data.status && data.services) {
            Object.keys(data.services).forEach(key => {
              const item = data.services[key];
              const badge = document.getElementById(`badge-${key}`);
              const msg = document.getElementById(`msg-${key}`);
              const card = document.getElementById(`card-${key}`);

              if (badge && msg) {
                if (item.status === 'connected') {
                  badge.className = 'badge bg-label-success text-success';
                  badge.innerHTML = '<i class="ti tabler-check me-1"></i> Terhubung';
                  msg.textContent = item.message || 'Server online & siap';
                  if (card) card.style.borderColor = 'rgba(40,199,111,0.3)';
                } else if (item.status === 'disabled') {
                  badge.className = 'badge bg-label-secondary text-muted';
                  badge.innerHTML = '<i class="ti tabler-power me-1"></i> Nonaktif';
                  msg.textContent = item.message || 'Fitur tidak diaktifkan';
                  if (card) card.style.borderColor = 'rgba(255,255,255,0.08)';
                } else {
                  badge.className = 'badge bg-label-danger text-danger';
                  badge.innerHTML = '<i class="ti tabler-alert-triangle me-1"></i> Terputus';
                  msg.textContent = item.message || 'Server tidak merespon';
                  if (card) card.style.borderColor = 'rgba(234,84,85,0.3)';
                }
              }
            });
          }
        })
        .catch(err => {
          console.error('WA Services status check error:', err);
          keys.forEach(key => {
            const badge = document.getElementById(`badge-${key}`);
            const msg = document.getElementById(`msg-${key}`);
            if (badge) {
              badge.className = 'badge bg-label-danger text-danger';
              badge.innerHTML = '<i class="ti tabler-alert-triangle me-1"></i> Error';
            }
            if (msg) msg.textContent = 'Gagal memuat status';
          });
        })
        .finally(() => {
          if (icon) icon.classList.remove('ti-spin');
          if (btn) btn.disabled = false;
        });
    }

    // ─── CHRONOS LUXURY SPORT WATCH LOGIC ────────────────────────────────────────
    (function initChronosWatch() {
      // 1. LumiBrite Minute & Hour Tick Marks (60 ticks with 12 glowing major hour bars)
      const lumiEl = document.getElementById('chronosLumiBars');
      if (lumiEl && lumiEl.children.length === 0) {
        for (let i = 0; i < 60; i++) {
          const tick = document.createElement('div');
          tick.className = 'lumi-tick' + (i % 5 === 0 ? ' major' : '');
          tick.style.transform = `rotate(${i * 6}deg)`;
          lumiEl.appendChild(tick);
        }
      }

      // 3. Bezel outer ring tick marks (60 minor ticks)
      const bezelTicksEl = document.getElementById('chronosBezelTicks');
      if (bezelTicksEl && bezelTicksEl.children.length === 0) {
        for (let i = 0; i < 60; i++) {
          const tick = document.createElement('div');
          tick.className = 'bezel-tick' + (i % 5 === 0 ? ' major' : '');
          tick.style.transform = `rotate(${i * 6}deg)`;
          bezelTicksEl.appendChild(tick);
        }
      }

      // 4. Sub-dial tick marks (Left = Orange, Right = Blue)
      function buildSubTicks(containerId, count) {
        const el = document.getElementById(containerId);
        if (!el || el.children.length > 0) return;
        for (let i = 0; i < count; i++) {
          const tick = document.createElement('div');
          tick.className = 'sub-tick' + (i % (count / 4) === 0 ? ' major' : '');
          tick.style.transform = `rotate(${(i / count) * 360}deg)`;
          el.appendChild(tick);
        }
      }
      buildSubTicks('subLeftTicks', 20);
      buildSubTicks('subRightTicks', 20);

      // 5. Hand elements
      const hourHand   = document.getElementById('chronosHourHand');
      const minuteHand = document.getElementById('chronosMinuteHand');
      const secondHand = document.getElementById('chronosSecondHand');
      const subLeftHand  = document.getElementById('chronosSubLeftHand');
      const subRightHand = document.getElementById('chronosSubRightHand');
      const lcdDay  = document.getElementById('chronosLcdDay');
      const lcdTime = document.getElementById('chronosLcdTime');

      const prevDeg = { hour: null, minute: null, second: null };

      function setHandRotation(el, deg, key) {
        if (!el) return;
        const prev = prevDeg[key];
        if (prev !== null && deg < prev - 180) {
          el.style.transition = 'none';
          el.style.transform = `rotate(${deg}deg)`;
          void el.offsetWidth;
          el.style.transition = '';
        } else {
          el.style.transform = `rotate(${deg}deg)`;
        }
        prevDeg[key] = deg;
      }

      const DAYS  = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
      const MONTHS = ['JAN', 'PEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'];
      const appTimezone = "{{ config('app.timezone', 'Asia/Jakarta') }}";

      function getNowInTimezone() {
        try {
          const now = new Date();
          const tzStr = now.toLocaleString("en-US", { timeZone: appTimezone });
          return new Date(tzStr);
        } catch (e) {
          return new Date();
        }
      }

      function updateChronosWatch() {
        const now = getNowInTimezone();
        const h = now.getHours();
        const m = now.getMinutes();
        const s = now.getSeconds();

        // Main hands
        setHandRotation(hourHand,   (h % 12) * 30 + m * 0.5, 'hour');
        setHandRotation(minuteHand, m * 6 + s * 0.1, 'minute');
        setHandRotation(secondHand, s * 6, 'second');

        // Sub-dial hands
        if (subLeftHand)  subLeftHand.style.transform  = `rotate(${(m / 60) * 360}deg)`;
        if (subRightHand) subRightHand.style.transform = `rotate(${(h / 12) * 360}deg)`;

        // LCD display — day abbreviation + date | HH:MM:SS
        if (lcdDay) {
          const dayAbbr = DAYS[now.getDay()];
          const date    = String(now.getDate()).padStart(2, '0');
          lcdDay.textContent = `${dayAbbr} ${date}`;
        }
        if (lcdTime) {
          const hh = String(h).padStart(2,'0');
          const mm = String(m).padStart(2,'0');
          const ss = String(s).padStart(2,'0');
          lcdTime.textContent = `${hh}:${mm}:${ss}`;
        }
      }

      updateChronosWatch();
      setInterval(updateChronosWatch, 1000);
    })();

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', function () {
        checkWaServicesStatus();
        const btnRefresh = document.getElementById('btnRefreshWaStatus');
        if (btnRefresh) btnRefresh.addEventListener('click', checkWaServicesStatus);
      });
    } else {
      checkWaServicesStatus();
      const btnRefresh = document.getElementById('btnRefreshWaStatus');
      if (btnRefresh) btnRefresh.addEventListener('click', checkWaServicesStatus);
    }
  </script>
@endsection


