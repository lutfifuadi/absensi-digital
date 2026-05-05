@extends('layouts/layoutMaster')

@section('title', 'Pengaturan Sistem')

@section('content')

{{-- ═══════════════════════════════════════════════════
     HERO HEADER — konsisten dengan dashboard
═══════════════════════════════════════════════════ --}}
<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-settings-2"></i>
        <div class="set-hero__icon-glow"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot"></span>
          Panel Administrasi
        </div>
        <h4 class="set-hero__title text-gradient-gold">Pengaturan Sistem</h4>
        <p class="set-hero__sub">Kelola preferensi lembaga, keamanan, branding, dan integrasi notifikasi secara komprehensif.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">Pengaturan</span>
    </div>
  </div>
</div>

{{-- ── SUCCESS ALERT ── --}}
@if (session('success'))
  <div class="set-toast mb-4">
    <div class="set-toast__icon"><i class="ti tabler-circle-check"></i></div>
    <div class="set-toast__msg">{{ session('success') }}</div>
    <button type="button" class="set-toast__close" data-bs-dismiss="alert">
      <i class="ti tabler-x"></i>
    </button>
  </div>
@endif

@if (session('sync_success'))
  <div class="set-toast mb-4" style="background: rgba(0, 207, 232, 0.12); border-color: rgba(0, 207, 232, 0.25);">
    <div class="set-toast__icon" style="color: #00cfe8;"><i class="ti tabler-refresh"></i></div>
    <div class="set-toast__msg" style="color: #b4f5ff;">{{ session('sync_success') }}</div>
    <button type="button" class="set-toast__close" data-bs-dismiss="alert">
      <i class="ti tabler-x"></i>
    </button>
  </div>
@endif

@if (session('sync_error'))
  <div class="set-toast mb-4" style="background: rgba(234, 84, 85, 0.12); border-color: rgba(234, 84, 85, 0.25);">
    <div class="set-toast__icon" style="color: #ea5455;"><i class="ti tabler-alert-triangle"></i></div>
    <div class="set-toast__msg" style="color: #fecaca;">{{ session('sync_error') }}</div>
    <button type="button" class="set-toast__close" data-bs-dismiss="alert">
      <i class="ti tabler-x"></i>
    </button>
  </div>
@endif

@if(auth()->user()->isSuperAdmin())
<div class="mb-4 d-flex justify-content-end">
  <a href="{{ route('admin.pengaturan.api-source.index') }}" class="btn btn-outline-info">
    <i class="ti tabler-api me-1"></i>
    Buka Pengaturan API Sumber Data
  </a>
</div>
@endif

{{-- ── MAIN LAYOUT ── --}}
<form action="{{ route('admin.pengaturan.update') }}" method="POST" enctype="multipart/form-data" id="formPengaturan">
  @csrf

  {{-- ─────────────────────────────
       HORIZONTAL TAB BAR
  ───────────────────────────── --}}
  <div class="set-tabbar-wrap">
    <div class="set-tabbar" id="set-nav-tabs">
      @php
        $navItems = [
          ['id' => 'lembaga',    'icon' => 'tabler-building-arch',  'label' => 'Identitas Lembaga'],
          ['id' => 'waktu',      'icon' => 'tabler-clock',           'label' => 'Waktu & Absensi'],
          ['id' => 'keamanan',   'icon' => 'tabler-shield-lock',     'label' => 'Keamanan & Lokasi'],
          ['id' => 'branding',   'icon' => 'tabler-photo',           'label' => 'Logo & Branding'],
          ['id' => 'notifikasi', 'icon' => 'tabler-bell-ringing',    'label' => 'Integrasi & Notifikasi'],
        ];

        if (auth()->user()->isSuperAdmin()) {
          $navItems[] = ['id' => 'update', 'icon' => 'tabler-cloud-download', 'label' => 'Pembaruan GitHub'];
        }
      @endphp
      @foreach ($navItems as $i => $nav)
        <button type="button"
          class="set-tab-btn {{ $i === 0 ? 'active' : '' }}"
          data-tab="{{ $nav['id'] }}">
          <i class="ti {{ $nav['icon'] }} set-tab-btn__icon"></i>
          <span class="set-tab-btn__label">{{ $nav['label'] }}</span>
        </button>
      @endforeach
    </div>
    <div class="set-tabbar__indicator"></div>
  </div>

  <div class="set-content">

    {{-- ─────────────────────────────
         CONTENT AREA
    ───────────────────────────── --}}
      {{-- ══ TAB 1: IDENTITAS LEMBAGA ══ --}}
      <div class="set-tab active" id="tab-lembaga">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --primary"><i class="ti tabler-building-arch"></i></div>
              <div>
                <div class="set-panel__title">Identitas Lembaga</div>
                <div class="set-panel__sub">Informasi dasar mengenai lembaga pendidikan Anda.</div>
              </div>
              <div class="ms-auto">
                <button type="button" class="set-btn set-btn--primary btn-sm" id="syncPusatBtn" onclick="triggerSyncPusat()">
                  <i class="ti tabler-refresh"></i>
                  <span>Sinkron dari Pusat</span>
                </button>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            <div class="set-form-grid">

              <div class="set-field">
                <label class="set-label">Nama Lembaga / Sekolah</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-school"></i></span>
                  <input type="text" class="set-input" name="nama_lembaga"
                    value="{{ old('nama_lembaga', $settings['nama_lembaga'] ?? '') }}"
                    placeholder="SMAN 1 Kota Bandung">
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Nama Kepala Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-user-tie"></i></span>
                  <input type="text" class="set-input" name="nama_kepala_lembaga"
                    value="{{ old('nama_kepala_lembaga', $settings['nama_kepala_lembaga'] ?? '') }}"
                    placeholder="Bapak/Ibu Kepala Sekolah">
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">NIP Kepala Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-barcode"></i></span>
                  <input type="text" class="set-input" name="nip_kepala_lembaga"
                    value="{{ old('nip_kepala_lembaga', $settings['nip_kepala_lembaga'] ?? '') }}"
                    placeholder="1980XXXXXX XXXXXX X XXX">
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Status Akreditasi</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-certificate"></i></span>
                  <select class="set-input" name="status_akreditasi">
                    @foreach(['Akreditasi A','Akreditasi B','Akreditasi C','Belum Terakreditasi'] as $akr)
                      <option value="{{ $akr }}" {{ ($settings['status_akreditasi'] ?? '') == $akr ? 'selected' : '' }}>{{ $akr }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Jumlah Tahun Belajar</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-calendar"></i></span>
                  <input type="number" class="set-input" name="jumlah_tahun_sekolah"
                    value="{{ old('jumlah_tahun_sekolah', $settings['jumlah_tahun_sekolah'] ?? '3') }}"
                    min="1" max="6">
                  <span class="set-input-suffix">Tahun</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Kecamatan</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-map-pin"></i></span>
                  <input type="text" class="set-input" name="kecamatan"
                    value="{{ old('kecamatan', $settings['kecamatan'] ?? '') }}"
                    placeholder="Kecamatan Coblong">
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Nomor Telepon Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-phone"></i></span>
                  <input type="text" class="set-input" name="no_telp_lembaga"
                    value="{{ old('no_telp_lembaga', $settings['no_telp_lembaga'] ?? '') }}"
                    placeholder="022-xxxxxxxx">
                </div>
              </div>

              <div class="set-field set-field--full">
                <label class="set-label">Website Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-world"></i></span>
                  <input type="text" class="set-input" name="website_lembaga"
                    value="{{ old('website_lembaga', $settings['website_lembaga'] ?? '') }}"
                    placeholder="https://sekolah.sch.id">
                </div>
              </div>

              {{-- Field tambahan: Alamat, Kontak, Email --}}
              <div class="set-field set-field--full">
                <label class="set-label">Alamat Lengkap Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-map-pin-2"></i></span>
                  <input type="text" class="set-input" name="alamat_lembaga"
                    value="{{ old('alamat_lembaga', $settings['alamat_lembaga'] ?? '') }}"
                    placeholder="Jl. Contoh No. 1, Kota, Provinsi">
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Nomor Kontak Resmi</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-device-mobile"></i></span>
                  <input type="text" class="set-input" name="kontak_lembaga"
                    value="{{ old('kontak_lembaga', $settings['kontak_lembaga'] ?? '') }}"
                    placeholder="0812xxxxxxxx">
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Nomor WA yang bisa dihubungi</div>
              </div>

              <div class="set-field">
                <label class="set-label">Email Resmi Lembaga</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-mail"></i></span>
                  <input type="email" class="set-input" name="email_lembaga"
                    value="{{ old('email_lembaga', $settings['email_lembaga'] ?? '') }}"
                    placeholder="info@sekolah.sch.id">
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- ══ TAB 2: WAKTU & ABSENSI ══ --}}
      <div class="set-tab" id="tab-waktu">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --warning"><i class="ti tabler-clock"></i></div>
              <div>
                <div class="set-panel__title">Waktu & Absensi</div>
                <div class="set-panel__sub">Atur batasan waktu masuk, pulang, dan toleransi jam absensi.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">

            {{-- Row 1: Masuk Window --}}
            <div class="set-time-cards mb-3">
              <div class="set-time-card set-time-card--masuk">
                <div class="set-time-card__icon"><i class="ti tabler-sun"></i></div>
                <div class="set-time-card__label">Jam Masuk</div>
                <input type="time" name="jam_masuk" class="set-time-input"
                  value="{{ old('jam_masuk', $settings['jam_masuk'] ?? '07:00') }}">
                <div class="set-time-card__hint">Waktu KBM dimulai</div>
              </div>
              <div class="set-time-card__divider">
                <div class="set-time-card__divider-line"></div>
                <div class="set-time-card__divider-icon"><i class="ti tabler-arrow-right"></i></div>
                <div class="set-time-card__divider-line"></div>
              </div>
              <div class="set-time-card set-time-card--masuk" style="border-style: dashed; opacity: 0.85;">
                <div class="set-time-card__icon"><i class="ti tabler-hourglass-high"></i></div>
                <div class="set-time-card__label">Batas Jam Terlambat</div>
                <input type="time" name="jam_batas_masuk" class="set-time-input"
                  value="{{ old('jam_batas_masuk', $settings['jam_batas_masuk'] ?? '08:00') }}">
                <div class="set-time-card__hint">Mulai dianggap Alpha/Tutup</div>
              </div>
            </div>

            {{-- Row 2: Pulang Window --}}
            <div class="set-time-cards">
              <div class="set-time-card set-time-card--pulang">
                <div class="set-time-card__icon"><i class="ti tabler-moon"></i></div>
                <div class="set-time-card__label">Jam Pulang</div>
                <input type="time" name="jam_pulang" class="set-time-input"
                  value="{{ old('jam_pulang', $settings['jam_pulang'] ?? '15:00') }}">
                <div class="set-time-card__hint">Waktu KBM berakhir</div>
              </div>
              <div class="set-time-card__divider">
                <div class="set-time-card__divider-line"></div>
                <div class="set-time-card__divider-icon"><i class="ti tabler-arrow-right"></i></div>
                <div class="set-time-card__divider-line"></div>
              </div>
              <div class="set-time-card set-time-card--pulang" style="border-style: dashed; opacity: 0.85;">
                <div class="set-time-card__icon"><i class="ti tabler-door-exit"></i></div>
                <div class="set-time-card__label">Batas Absensi Pulang</div>
                <input type="time" name="jam_akhir_pulang" class="set-time-input"
                  value="{{ old('jam_akhir_pulang', $settings['jam_akhir_pulang'] ?? '17:00') }}">
                <div class="set-time-card__hint">Sesi scan pulang ditutup</div>
              </div>
            </div>

            <div class="set-form-grid mt-4">
              <div class="set-field">
                <label class="set-label">Mulai Boleh Scan Pulang</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-clock-play"></i></span>
                  <input type="time" class="set-input" name="jam_mulai_pulang"
                    value="{{ old('jam_mulai_pulang', $settings['jam_mulai_pulang'] ?? '14:00') }}">
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Earliest time to scan out</div>
              </div>

              <div class="set-field">
                <label class="set-label">Toleransi Keterlambatan (Menit)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-hourglass-low"></i></span>
                  <input type="number" class="set-input" name="toleransi_terlambat"
                    value="{{ old('toleransi_terlambat', $settings['toleransi_terlambat'] ?? '15') }}">
                  <span class="set-input-suffix">Menit</span>
                </div>
                <div class="set-field-hint --warning"><i class="ti tabler-info-circle"></i> Setelah Jam Masuk</div>
              </div>

              <div class="set-field">
                <label class="set-label">Minimal Hadir untuk Rekap</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-percentage"></i></span>
                  <input type="number" class="set-input" name="minimal_hadir_persen"
                    value="{{ old('minimal_hadir_persen', $settings['minimal_hadir_persen'] ?? '90') }}"
                    min="0" max="100">
                  <span class="set-input-suffix">%</span>
                </div>
              </div>

              <div class="set-field set-field--full">
                <label class="set-label">Zona Waktu Lokal</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-globe"></i></span>
                  <select class="set-input" name="zona_waktu">
                    @foreach(['Asia/Jakarta (WIB)','Asia/Makassar (WITA)','Asia/Jayapura (WIT)'] as $tz)
                      <option value="{{ $tz }}" {{ ($settings['zona_waktu'] ?? '') == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ══ TAB 3: KEAMANAN & LOKASI ══ --}}
      <div class="set-tab" id="tab-keamanan">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --danger"><i class="ti tabler-shield-lock"></i></div>
              <div>
                <div class="set-panel__title">Keamanan & Lokasi</div>
                <div class="set-panel__sub">Aktifkan limitasi perangkat, geofencing, dan validasi anti Fake-GPS.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">

            {{-- Toggle Section --}}
            <div class="set-section-label">Fitur Cerdas</div>
            <div class="set-toggles">
              @php
                $toggles = [
                  ['name'=>'tampilkan_beranda',              'label'=>'Tampilkan Landing Page', 'sub'=>'Tampilkan halaman beranda saat akses awal', 'color'=>'primary', 'icon' => 'tabler-home-heart'],
                  ['name'=>'lock_device_pc',                 'label'=>'Kunci Perangkat PC',    'sub'=>'Lock akses scanner hanya untuk device terdaftar', 'color'=>'warning', 'icon' => 'tabler-device-desktop'],
                  ['name'=>'izinkan_lokasi_absensi_mandiri', 'label'=>'Validasi Lokasi Siswa', 'sub'=>'Kunci absensi dalam radius sekolah', 'color'=>'primary', 'icon' => 'tabler-map-pin'],
                  ['name'=>'deteksi_fake_gps',               'label'=>'Anti Fake GPS',         'sub'=>'Deteksi & cegah mock-location',    'color'=>'danger', 'icon' => 'tabler-shield-x'],
                  ['name'=>'izinkan_lokasi_scan_qr',         'label'=>'Scanner Publik Lokasi',  'sub'=>'Minta GPS di laman QR publik',     'color'=>'success', 'icon' => 'tabler-qrcode'],
                  ['name'=>'izinkan_rfid',                   'label'=>'Perangkat RFID',         'sub'=>'Enable sensor tap card external',  'color'=>'info', 'icon' => 'tabler-wifi'],
                ];
              @endphp
              @foreach ($toggles as $tg)
                <div class="set-toggle-item set-toggle-item--{{ $tg['color'] }}">
                  <div class="set-toggle-item__icon">
                    <i class="ti {{ $tg['icon'] }}"></i>
                  </div>
                  <div class="set-toggle-item__info">
                    <div class="set-toggle-item__label">{{ $tg['label'] }}</div>
                    <div class="set-toggle-item__sub">{{ $tg['sub'] }}</div>
                  </div>
                  <label class="set-switch set-switch--{{ $tg['color'] }}">
                    <input type="hidden" name="{{ $tg['name'] }}" value="Tidak">
                    <input type="checkbox" class="set-switch__input" name="{{ $tg['name'] }}" value="Ya"
                      {{ ($settings[$tg['name']] ?? '') == 'Ya' ? 'checked' : '' }}>
                    <span class="set-switch__slider"></span>
                  </label>
                </div>
              @endforeach
            </div>

            {{-- Geofencing --}}
            <div class="set-section-label mt-4">Koordinat & Geofencing Sekolah</div>
            <div class="set-form-grid">
              <div class="set-field">
                <label class="set-label">Latitude</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-map-2"></i></span>
                  <input type="text" class="set-input font-monospace" name="latitude"
                    value="{{ old('latitude', $settings['latitude'] ?? '-6.922405') }}">
                </div>
              </div>
              <div class="set-field">
                <label class="set-label">Longitude</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-map-2"></i></span>
                  <input type="text" class="set-input font-monospace" name="longitude"
                    value="{{ old('longitude', $settings['longitude'] ?? '107.5717651') }}">
                </div>
              </div>
              <div class="set-field">
                <label class="set-label">Radius Absensi (Meter)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-target"></i></span>
                  <input type="number" class="set-input" name="radius_jarak_absen"
                    value="{{ old('radius_jarak_absen', $settings['radius_jarak_absen'] ?? '900') }}">
                  <span class="set-input-suffix">m</span>
                </div>
              </div>
              <div class="set-field">
                <label class="set-label">Toleransi Akurasi GPS</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-radar"></i></span>
                  <input type="number" class="set-input" name="minimal_akurasi_gps"
                    value="{{ old('minimal_akurasi_gps', $settings['minimal_akurasi_gps'] ?? '100') }}">
                  <span class="set-input-suffix">m</span>
                </div>
              </div>
            </div>

            {{-- Scanner Password --}}
            <div class="set-section-label mt-4">Password Scanner Publik</div>
            <div class="set-info-banner set-info-banner--info mb-3">
              <i class="ti tabler-info-circle"></i>
              <div>
                <strong>Pengamanan Layar Guru Piket</strong><br>
                <span>Gunakan password di bawah untuk memproteksi laman <code>/scan-qr</code>.</span>
              </div>
            </div>
            <div class="set-field">
              <label class="set-label">Password Login Scanner Publik</label>
              <div class="set-input-group set-password-toggle">
                <span class="set-input-prefix"><i class="ti tabler-lock"></i></span>
                <input type="password" class="set-input" name="password_unlock_scan_qr"
                  placeholder="Isi untuk mengubah, kosongkan jika tetap..." autocomplete="new-password">
                <button type="button" class="set-input-eye">
                  <i class="ti tabler-eye-off"></i>
                </button>
              </div>
              @if (!empty($settings['scan_qr_password_set']))
                <div class="set-field-hint --success"><i class="ti tabler-check"></i> Proteksi Scanner: AKTIF</div>
              @else
                <div class="set-field-hint --warning"><i class="ti tabler-alert-triangle"></i> Proteksi Scanner: BELUM AKTIF</div>
              @endif
            </div>

          </div>
        </div>
      </div>

      {{-- ══ TAB 4: BRANDING ══ --}}
      <div class="set-tab" id="tab-branding">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --success"><i class="ti tabler-photo"></i></div>
              <div>
                <div class="set-panel__title">Logo & Branding</div>
                <div class="set-panel__sub">Atur tampilan visual logo untuk dokumen dan dashboard.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            <div class="set-branding-wrap">
              <div class="set-logo-preview" id="logoPreviewWrap">
                @php
                  $logoSrc = null;
                  if (!empty($settings['logo_url'])) {
                    $logoSrc = $settings['logo_url'];
                  } elseif (!empty($settings['logo_sekolah'])) {
                    $logoSrc = Storage::url($settings['logo_sekolah']);
                  }
                @endphp
                @if ($logoSrc)
                  <img src="{{ $logoSrc }}" id="logoPreviewImg" alt="Logo Sekolah" class="set-logo-preview__img">
                @else
                  <div class="set-logo-preview__empty" id="logoPreviewEmpty">
                    <i class="ti tabler-photo-off"></i>
                    <span>Belum ada logo</span>
                  </div>
                  <img src="" id="logoPreviewImg" alt="" class="set-logo-preview__img d-none">
                @endif
              </div>
              <div class="set-logo-uploader">
                <div class="set-upload-zone" id="uploadZone">
                  <div class="set-upload-zone__icon"><i class="ti tabler-cloud-upload"></i></div>
                  <p class="set-upload-zone__title">Klik atau seret file ke sini</p>
                  <p class="set-upload-zone__sub">JPG, PNG, GIF · Maks 1MB</p>
                  <label class="set-btn set-btn--primary" for="upload_logo">
                    <i class="ti tabler-file-upload"></i> Pilih File
                  </label>
                  <input type="file" id="upload_logo" name="logo_sekolah" class="d-none" accept="image/png,image/jpeg,image/jpg,image/gif">
                </div>
                <div class="set-upload-hints">
                  <div class="set-upload-hint-item"><i class="ti tabler-check text-success"></i> Resolusi disarankan min. 200×200px</div>
                  <div class="set-upload-hint-item"><i class="ti tabler-check text-success"></i> Background transparan (PNG) lebih baik</div>
                  <div class="set-upload-hint-item"><i class="ti tabler-check text-success"></i> Logo muncul di cetak laporan & PDF</div>
                </div>
                
                <div class="set-url-upload mt-4">
                  <div class="set-url-upload__divider">
                    <span class="set-url-upload__divider-text">atau</span>
                  </div>
                  <div class="set-field">
                    <label class="set-label">Upload dari URL/S3</label>
                    <div class="set-input-group">
                      <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                      <input type="url" class="set-input" name="logo_url" id="logoUrlInput"
                        value="{{ old('logo_url') }}"
                        placeholder="https://example.com/logo.png">
                      </div>
                    </div>
                    <div class="set-field-hint --info">
                      <i class="ti tabler-info-circle"></i> Contoh: URL dari S3 seperti https://ppdb-mansaba.s3.ap-southeast-1.amazonaws.com/logo_madrasah.png
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ══ TAB 5: NOTIFIKASI ══ --}}
      <div class="set-tab" id="tab-notifikasi">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --info"><i class="ti tabler-bell-ringing"></i></div>
              <div>
                <div class="set-panel__title">Integrasi & Notifikasi</div>
                <div class="set-panel__sub">Hubungkan layanan pihak ketiga untuk push-notifications real-time.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">

            <div class="set-form-grid mb-4">
              <div class="set-field">
                <label class="set-label">Platform Notifikasi Orang Tua</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-send"></i></span>
                  <select class="set-input" name="jenis_notifikasi_ortu">
                    @foreach(['WhatsApp (WA)' => 'WhatsApp Gateway', 'Telegram' => 'Telegram Bot API', 'Matikan' => 'Disabled (Matikan)'] as $val => $label)
                      <option value="{{ $val }}" {{ ($settings['jenis_notifikasi_ortu'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="set-field">
                <label class="set-label">Mode Feedback Scanner QR</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-volume"></i></span>
                  <select class="set-input" name="mode_notifikasi_scan_qr">
                    <option value="Mode Audio" {{ ($settings['mode_notifikasi_scan_qr'] ?? '') == 'Mode Audio' ? 'selected' : '' }}>Mode Audio (Suara Berhasil)</option>
                    <option value="Mode Tulisan" {{ ($settings['mode_notifikasi_scan_qr'] ?? '') == 'Mode Tulisan' ? 'selected' : '' }}>Mode Visual Saja (Toast)</option>
                  </select>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Varian Notifikasi Suara</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-music"></i></span>
                  <select class="set-input" name="varian_notifikasi_suara">
                    @foreach([
                      'default'   => 'Default (Bel + Terima Kasih)',
                      'beep'      => 'Beep Standar',
                      'chime'     => 'Chime (Melodi Tinggi)',
                      'soft'      => 'Soft Bell (Lembut)',
                      'digital'   => 'Digital Beep',
                    ] as $val => $label)
                      <option value="{{ $val }}" {{ ($settings['varian_notifikasi_suara'] ?? 'default') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Suara yang dimainkan saat scan QR berhasil</div>
              </div>

              <div class="set-field">
                <label class="set-label">Aktifkan Bunyi Notifikasi Absensi Mandiri</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-speakerphone"></i></span>
                  <select class="set-input" name="aktifkan_bunyi_notif_absensi">
                    <option value="Ya" {{ ($settings['aktifkan_bunyi_notif_absensi'] ?? 'Ya') == 'Ya' ? 'selected' : '' }}>Aktif</option>
                    <option value="Tidak" {{ ($settings['aktifkan_bunyi_notif_absensi'] ?? 'Ya') == 'Tidak' ? 'selected' : '' }}>Nonaktif</option>
                  </select>
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Bunyi saat absensi mandiri berhasil di dashboard siswa/guru</div>
              </div>

              <div class="set-field set-field--full">
                <div class="alert alert-info mb-0" style="border-radius:12px;background:rgba(37,211,102,.08);border:1px solid rgba(37,211,102,.2);">
                  <i class="ti tabler-brand-whatsapp me-2 text-success"></i>
                  <strong>Pengaturan WA Gateway:</strong>
                  Untuk konfigurasi detail WA Gateway (API Key, nomor admin, test koneksi), gunakan halaman khusus:
                  <a href="{{ route('admin.wa-gateway.index') }}" class="btn btn-sm btn-success ms-2">
                    <i class="ti tabler-settings me-1"></i>Buka WA Gateway
                  </a>
                </div>
              </div>
            </div>

            {{-- Frekuensi Bunyi Variasi Absensi --}}
            <div class="set-section-label mt-4">Frekuensi Bunyi per Konteks Absensi (Hz)</div>
            <div class="set-form-grid mb-4">
              <div class="set-field">
                <label class="set-label">Bunyi Hadir Tepat Waktu</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-music-check"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_hadir"
                    value="{{ old('freq_bunyi_hadir', $settings['freq_bunyi_hadir'] ?? '880') }}"
                    placeholder="880" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Hadir Terlambat</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-music-minus"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_terlambat"
                    value="{{ old('freq_bunyi_terlambat', $settings['freq_bunyi_terlambat'] ?? '440') }}"
                    placeholder="440" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Milestone Streak</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-trophy"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_streak"
                    value="{{ old('freq_bunyi_streak', $settings['freq_bunyi_streak'] ?? '523') }}"
                    placeholder="523" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Awal Pagi (Early)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-sun-rise"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_early"
                    value="{{ old('freq_bunyi_early', $settings['freq_bunyi_early'] ?? '698') }}"
                    placeholder="698" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Waktu Normal</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-clock-check"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_normal"
                    value="{{ old('freq_bunyi_normal', $settings['freq_bunyi_normal'] ?? '523') }}"
                    placeholder="523" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Terlambat (Late)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-clock-x"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_late"
                    value="{{ old('freq_bunyi_late', $settings['freq_bunyi_late'] ?? '349') }}"
                    placeholder="349" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>

              <div class="set-field">
                <label class="set-label">Bunyi Pulang (Checkout)</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-logout"></i></span>
                  <input type="number" class="set-input" name="freq_bunyi_checkout"
                    value="{{ old('freq_bunyi_checkout', $settings['freq_bunyi_checkout'] ?? '392') }}"
                    placeholder="392" min="100" max="2000">
                  <span class="set-input-suffix">Hz</span>
                </div>
              </div>
            </div>


            <div class="alert alert-info mb-4" role="alert" style="border-radius: 12px; background: rgba(0, 207, 232, 0.08); border:1px solid rgba(0, 207, 232, 0.15); color:#fff;">
              <div class="d-flex align-items-center gap-3">
                <i class="ti tabler-info-circle fs-3"></i>
                <div>
                  <strong>Pengaturan API Sumber Data</strong><br>
                  Untuk konfigurasi API aplikasi eksternal, gunakan halaman khusus <a href="{{ route('admin.pengaturan.api-source.index') }}" class="text-info text-decoration-underline">Pengaturan API Sumber Data</a>.
                </div>
              </div>
            </div>

            {{-- PMBM Incoming API Key --}}
            <div class="set-integration-card set-integration-card--pmbm mb-4">
              <div class="set-integration-card__head">
                <div class="set-integration-card__brand">
                  <i class="ti tabler-key"></i>
                  <span>Integrasi PMBM</span>
                </div>
                <div class="set-chip --warning">Webhook PMBM</div>
              </div>
              <div class="set-integration-card__body">
                <div class="set-field set-field--full">
                  <label class="set-label">X-API-KEY PMBM</label>
                  <div class="set-input-group">
                    <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                    <input type="text" class="set-input font-monospace" name="pmbm_incoming_api_key"
                      value="{{ old('pmbm_incoming_api_key', $settings['pmbm_incoming_api_key'] ?? '') }}"
                      placeholder="Masukkan X-API-KEY untuk webhook PMBM">
                  </div>
                  <div class="form-text text-muted mt-2">Kunci ini harus sama dengan <code>external_api_key</code> di aplikasi PMBM agar webhook ke <code>/api/v1/pmbm/presensi</code> dapat diterima. Jika dikosongkan, aplikasi akan menggunakan <code>PMBM_INCOMING_API_KEY</code> dari <code>.env</code> apabila tersedia.</div>
                </div>
              </div>
            </div>

            {{-- WhatsApp Config --}}
            <div class="set-integration-card set-integration-card--wa mb-4">
              <div class="set-integration-card__head">
                <div class="set-integration-card__brand">
                  <i class="ti tabler-brand-whatsapp"></i>
                  <span>WhatsApp Gateway API</span>
                </div>
                <div class="set-chip --success">WA API</div>
              </div>
              <div class="set-integration-card__body">
                <div class="set-form-grid">
                  <div class="set-field set-field--full">
                    <label class="set-label">Endpoint Server WA API</label>
                    <div class="set-input-group">
                      <span class="set-input-prefix"><i class="ti tabler-api"></i></span>
                      <input type="text" class="set-input font-monospace" name="link_server_wa"
                        value="{{ old('link_server_wa', $settings['link_server_wa'] ?? '') }}"
                        placeholder="https://api.domain.com/send">
                    </div>
                  </div>
                  <div class="set-field">
                    <label class="set-label">Nomor / API Key</label>
                    <div class="set-input-group">
                      <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                      <input type="text" class="set-input" name="nomor_server_wa_api_key"
                        value="{{ old('nomor_server_wa_api_key', $settings['nomor_server_wa_api_key'] ?? '') }}">
                    </div>
                  </div>
                  <div class="set-field">
                    <label class="set-label">Jeda Antrean Kirim</label>
                    <div class="set-input-group">
                      <span class="set-input-prefix"><i class="ti tabler-clock-pause"></i></span>
                      <input type="number" class="set-input" name="jeda_waktu_kirim_pesan_detik"
                        value="{{ old('jeda_waktu_kirim_pesan_detik', $settings['jeda_waktu_kirim_pesan_detik'] ?? '5') }}">
                      <span class="set-input-suffix">Detik</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Telegram Config --}}
            <div class="set-integration-card set-integration-card--tg">
              <div class="set-integration-card__head">
                <div class="set-integration-card__brand">
                  <i class="ti tabler-brand-telegram"></i>
                  <span>Telegram Bot API</span>
                </div>
                <div class="set-chip --info">Telegram</div>
              </div>
              <div class="set-integration-card__body">
                <div class="set-field">
                  <label class="set-label">Token Bot Telegram</label>
                  <div class="set-input-group">
                    <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                    <input type="text" class="set-input font-monospace" name="token_bot_telegram"
                      value="{{ old('token_bot_telegram', $settings['token_bot_telegram'] ?? '') }}"
                      placeholder="123456:ABC-DEFxxxxxxxxxxxxxxxxxxx">
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- ══ TAB 6: PEMBARUAN GITHUB ══ --}}
      @if(auth()->user()->isSuperAdmin())
      <div class="set-tab" id="tab-update">
        <div class="set-panel">
          <div class="set-panel__head">
            <div class="set-panel__title-wrap">
              <div class="set-panel__icon --primary"><i class="ti tabler-cloud-download"></i></div>
              <div>
                <div class="set-panel__title">Pembaruan GitHub</div>
                <div class="set-panel__sub">Konfigurasi repositori GitHub untuk sinkronisasi update sistem.</div>
              </div>
            </div>
          </div>
          <div class="set-panel__body">
            {{-- Status Pembaruan Card --}}
            <div class="mb-4" style="background: rgba(115, 103, 240, 0.05); border: 1px solid rgba(115, 103, 240, 0.15); border-radius: 12px; padding: 1.5rem;">
              <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="update-status-icon" style="width: 50px; height: 50px; background: rgba(115, 103, 240, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #7367f0;">
                    <i class="ti tabler-versions fs-2"></i>
                  </div>
                  <div>
                    <h6 class="mb-1" style="color: #fff;">Status Sistem</h6>
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-label-primary">Versi {{ $currentVersion }}</span>
                      @if($updateInfo && isset($updateInfo['latest_version']))
                        <span class="badge bg-label-warning">
                          <i class="ti tabler-arrow-up-circle me-1"></i>
                          v{{ $updateInfo['latest_version'] }} Tersedia
                        </span>
                      @else
                        <span class="badge bg-label-success">
                          <i class="ti tabler-check me-1"></i>
                          Terbaru
                        </span>
                      @endif
                    </div>
                  </div>
                </div>
                <div>
                  <a href="{{ route('admin.update.index') }}" class="set-btn set-btn--primary">
                    <i class="ti tabler-external-link"></i>
                    <span>Buka Halaman Pembaruan</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="set-section-label">Konfigurasi Repositori</div>

            <div class="alert alert-warning mb-4" role="alert" style="background: rgba(255, 159, 67, 0.08); border: 1px solid rgba(255, 159, 67, 0.2); border-radius: 12px; color: #ff9f43;">
              <div class="d-flex align-items-center gap-2">
                <i class="ti tabler-alert-triangle fs-4"></i>
                <strong>Perhatian:</strong>
              </div>
              <p class="mt-2 mb-0 small">Pastikan Personal Access Token (PAT) memiliki izin <code>repo</code> untuk mengakses repositori privat. Token ini akan digunakan sistem untuk mengecek dan mengunduh paket pembaruan.</p>
            </div>
            <div class="set-form-grid">
              <div class="set-field">
                <label class="set-label">GitHub Username / Owner</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-user"></i></span>
                  <input type="text" class="set-input" name="github_repo_owner"
                    value="{{ old('github_repo_owner', $settings['github_repo_owner'] ?? '') }}"
                    placeholder="Contoh: lutfifuadi">
                </div>
              </div>
              <div class="set-field">
                <label class="set-label">GitHub Repository Name</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-brand-github"></i></span>
                  <input type="text" class="set-input" name="github_repo_name"
                    value="{{ old('github_repo_name', $settings['github_repo_name'] ?? '') }}"
                    placeholder="Contoh: absensi-klien">
                </div>
              </div>
              <div class="set-field set-field--full">
                <label class="set-label">GitHub Personal Access Token (PAT)</label>
                <div class="set-input-group set-password-toggle">
                  <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                  <input type="password" class="set-input" name="github_access_token"
                    value="{{ old('github_access_token', $settings['github_access_token'] ?? '') }}"
                    placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                  <button type="button" class="set-input-eye">
                    <i class="ti tabler-eye-off"></i>
                  </button>
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Token ini digunakan untuk mengunduh update dari repositori privat secara aman.</div>
              </div>
              <div class="set-field set-field--full">
                <label class="set-label">Versi Aplikasi Saat Ini</label>
                <div class="set-input-group">
                  <span class="set-input-prefix"><i class="ti tabler-hash"></i></span>
                  <input type="text" class="set-input font-monospace" name="app_version"
                    value="{{ old('app_version', $settings['app_version'] ?? '1.3.0') }}"
                    placeholder="Contoh: 1.3.0">
                </div>
                <div class="set-field-hint --info"><i class="ti tabler-info-circle"></i> Versi ini akan digunakan untuk membandingkan dengan rilisan terbaru di GitHub.</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- Bottom Save Button --}}
      <div class="set-footer-save">
        <button type="submit" class="set-save-btn" id="footerSaveBtn">
          <i class="ti tabler-device-floppy"></i>
          <span>Simpan Semua Konfigurasi</span>
        </button>
      </div>

  </div>
</form>

@endsection


@section('page-style')
<style>
/* ═══════════════════════════════════════
   CSS VARIABLES (konsisten dengan dashboard)
═══════════════════════════════════════ */
:root {
  --das-primary:      #7367f0;
  --das-primary-soft: rgba(115,103,240,0.12);
  --das-success:      #28c76f;
  --das-success-soft: rgba(40,199,111,0.12);
  --das-info:         #00cfe8;
  --das-info-soft:    rgba(0,207,232,0.12);
  --das-warning:      #ff9f43;
  --das-warning-soft: rgba(255,159,67,0.12);
  --das-danger:       #ea5455;
  --das-danger-soft:  rgba(234,84,85,0.12);
  --das-secondary:    #a8aaae;

  --das-surface:       rgba(15, 23, 42, 0.45);
  --das-surface-hover: rgba(30, 41, 59, 0.65);
  --das-border:        rgba(255,255,255,0.07);
  --das-border-hover:  rgba(255,255,255,0.14);
  --das-radius:        5px;
  --das-radius-sm:     5px;
}

/* ═══════════════════════════════════════
   UTILITIES
═══════════════════════════════════════ */
.glass-card {
  background: rgba(255,255,255,0.03) !important;
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  border: 1px solid var(--das-border) !important;
}
.text-gradient-gold {
  background: linear-gradient(to right, #fff, #ffd700);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.font-monospace { font-family: 'Courier New', monospace !important; }

/* ═══════════════════════════════════════
   HERO HEADER
═══════════════════════════════════════ */
.set-hero {
  position: relative;
  border-radius: var(--das-radius);
  overflow: hidden;
}
.set-hero__bg {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #1e1b4b 0%, #312d89 45%, #4338ca 100%);
  z-index: 0;
}
.set-hero__glass {
  position: absolute; inset: 0;
  background: radial-gradient(circle at top right, rgba(115,103,240,0.18), transparent 45%);
  z-index: 1;
}
.set-hero__grid {
  position: absolute; inset: 0; z-index: 1;
  background-image:
    linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
  background-size: 40px 40px;
}
.set-hero__inner {
  position: relative; z-index: 2;
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 2rem 2.5rem;
  gap: 1.5rem; flex-wrap: wrap;
}
.set-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
.set-hero__icon-wrap {
  position: relative;
  width: 64px; height: 64px; border-radius: 5px;
  background: rgba(115,103,240,0.2);
  border: 1.5px solid rgba(115,103,240,0.4);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.75rem; color: #a5a2f7; flex-shrink: 0;
  animation: heroIconSpin 20s linear infinite;
}
@keyframes heroIconSpin {
  0%,100% { box-shadow: 0 0 15px rgba(115,103,240,0.2); }
  50%      { box-shadow: 0 0 30px rgba(115,103,240,0.5); }
}
.set-hero__icon-glow {
  position: absolute; inset: -8px;
  background: var(--das-primary);
  filter: blur(18px); opacity: 0.2;
  border-radius: 50%; z-index: -1;
}
.set-hero__badge {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.62rem; font-weight: 700;
  letter-spacing: 1.2px; text-transform: uppercase;
  background: rgba(115,103,240,0.18);
  border: 1px solid rgba(115,103,240,0.3);
  color: #a5a2f7;
  padding: 3px 10px; border-radius: 20px; margin-bottom: 6px;
}
.pulse-dot {
  width: 6px; height: 6px; background: #a5a2f7; border-radius: 50%;
  animation: pulseGlow 1.5s infinite;
}
@keyframes pulseGlow {
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.8); opacity: 0.5; }
}
.set-hero__title {
  font-size: 1.5rem; font-weight: 800;
  margin: 0 0 4px;
}
.set-hero__sub {
  margin: 0; font-size: 0.8rem;
  color: rgba(255,255,255,0.5);
  max-width: 500px;
}
.set-hero__breadcrumb {
  border-radius: var(--das-radius-sm);
  padding: 0.6rem 1rem;
  display: flex; align-items: center;
  background: rgba(0,0,0,0.2) !important;
}

/* ═══════════════════════════════════════
   TOAST ALERT
═══════════════════════════════════════ */
.set-toast {
  display: flex; align-items: center; gap: 0.75rem;
  background: rgba(40,199,111,0.12);
  border: 1px solid rgba(40,199,111,0.25);
  border-radius: var(--das-radius-sm);
  padding: 0.85rem 1.1rem;
}
.set-toast__icon { color: var(--das-success); font-size: 1.2rem; flex-shrink: 0; }
.set-toast__msg  { flex: 1; font-size: 0.85rem; color: #d1fae5; }
.set-toast__close {
  background: transparent; border: none; color: #888; cursor: pointer;
  padding: 0; font-size: 0.9rem; transition: color 0.15s;
}
.set-toast__close:hover { color: white; }

/* ═══════════════════════════════════════
   HORIZONTAL TAB BAR
═══════════════════════════════════════ */
.set-tabbar-wrap {
  margin-bottom: 1.25rem;
  position: relative;
}
.set-tabbar {
  display: flex;
  gap: 0.25rem;
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  padding: 0.4rem;
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: none;
  -ms-overflow-style: none;
  backdrop-filter: blur(8px);
  position: relative;
}
.set-tabbar::-webkit-scrollbar { display: none; }

.set-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.1rem;
  border: 1px solid transparent;
  border-radius: calc(var(--das-radius) - 2px);
  background: transparent;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  color: #64748b;
  white-space: nowrap;
  flex-shrink: 0;
  transition: all 0.22s ease;
  position: relative;
}
.set-tab-btn:hover {
  color: #e2e8f0;
  background: var(--das-surface-hover);
}
.set-tab-btn.active {
  background: var(--das-primary-soft);
  border-color: rgba(115,103,240,0.35);
  color: #fff;
}
.set-tab-btn__icon {
  font-size: 1rem;
  flex-shrink: 0;
  transition: color 0.22s;
}
.set-tab-btn:not(.active) .set-tab-btn__icon { color: #475569; }
.set-tab-btn.active .set-tab-btn__icon { color: var(--das-primary); }
.set-tab-btn__label {
  font-size: 0.8rem;
}

/* Fade-in bottom border indicator */
.set-tabbar__indicator {
  height: 2px;
  background: transparent;
  border-radius: 0 0 4px 4px;
  transition: all 0.25s;
}

/* ═══════════════════════════════════════
   CONTENT PANELS
═══════════════════════════════════════ */
.set-content { min-width: 0; }

.set-tab { display: none; }
.set-tab.active {
  display: block;
  animation: tabFadeIn 0.35s ease-out;
}
@keyframes tabFadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

.set-panel {
  background: var(--das-surface);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius);
  overflow: hidden;
  backdrop-filter: blur(6px);
}
.set-panel__head {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--das-border);
  background: linear-gradient(90deg, rgba(115,103,240,0.06) 0%, transparent 60%);
}
.set-panel__title-wrap {
  display: flex; align-items: center; gap: 1rem;
}
.set-panel__icon {
  width: 44px; height: 44px; border-radius: var(--das-radius);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.25rem; flex-shrink: 0;
}
.set-panel__icon.--primary   { background: var(--das-primary-soft); color: var(--das-primary); }
.set-panel__icon.--warning   { background: var(--das-warning-soft); color: var(--das-warning); }
.set-panel__icon.--danger    { background: var(--das-danger-soft);  color: var(--das-danger);  }
.set-panel__icon.--success   { background: var(--das-success-soft); color: var(--das-success); }
.set-panel__icon.--info      { background: var(--das-info-soft);    color: var(--das-info);    }
.set-panel__title  { font-size: 1rem; font-weight: 700; color: #e2e8f0; margin: 0 0 2px; }
.set-panel__sub    { font-size: 0.72rem; color: #64748b; margin: 0; }
.set-panel__body   { padding: 1.5rem; }

/* ═══════════════════════════════════════
   FORM GRID & FIELDS
═══════════════════════════════════════ */
.set-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.15rem;
}
.set-field--full { grid-column: 1 / -1; }

.set-label {
  display: block;
  font-size: 0.62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: #64748b; margin-bottom: 0.45rem;
}
.set-input-group {
  display: flex; align-items: center;
  background: rgba(15,23,42,0.5);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  overflow: hidden;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.set-input-group:focus-within {
  border-color: var(--das-primary);
  box-shadow: 0 0 0 3px rgba(115,103,240,0.12);
}
.set-input-prefix {
  padding: 0 0.75rem; font-size: 1rem; color: #475569; flex-shrink: 0;
}
.set-input-suffix {
  padding: 0 0.75rem; font-size: 0.72rem; font-weight: 700;
  color: #475569; flex-shrink: 0; letter-spacing: 0.5px;
  border-left: 1px solid var(--das-border);
}
.set-input {
  flex: 1; padding: 0.6rem 0.5rem 0.6rem 0;
  background: transparent; border: none;
  color: #e2e8f0; font-size: 0.85rem;
  outline: none; min-width: 0;
}
.set-input::placeholder { color: #334155; }
select.set-input { padding-right: 0.5rem; cursor: pointer; }
.set-input-eye {
  padding: 0 0.75rem; background: transparent;
  border: none; color: #475569; cursor: pointer;
  font-size: 1rem; transition: color 0.15s;
  flex-shrink: 0;
}
.set-input-eye:hover { color: #e2e8f0; }

.set-field-hint {
  display: flex; align-items: center; gap: 4px;
  font-size: 0.7rem; font-weight: 600; margin-top: 6px;
}
.set-field-hint.--success { color: var(--das-success); }
.set-field-hint.--warning { color: var(--das-warning); }

/* ═══════════════════════════════════════
   TIME CARDS
═══════════════════════════════════════ */
.set-time-cards {
  display: flex; align-items: center; gap: 1rem;
}
.set-time-card {
  flex: 1; text-align: center;
  padding: 1.5rem 1rem;
  border-radius: var(--das-radius);
  border: 1px solid var(--das-border);
  background: rgba(15,23,42,0.4);
}
.set-time-card--masuk  { border-color: rgba(255,159,67,0.25); background: rgba(255,159,67,0.04); }
.set-time-card--pulang { border-color: rgba(234,84,85,0.25);  background: rgba(234,84,85,0.04);  }
.set-time-card__icon {
  font-size: 1.6rem; margin-bottom: 0.5rem;
}
.set-time-card--masuk  .set-time-card__icon { color: var(--das-warning); }
.set-time-card--pulang .set-time-card__icon { color: var(--das-danger);  }
.set-time-card__label {
  font-size: 0.68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.8px;
  color: #94a3b8; margin-bottom: 0.75rem;
}
.set-time-input {
  display: block; margin: 0 auto;
  font-size: 1.6rem; font-weight: 800;
  text-align: center; color: #fff;
  background: rgba(0,0,0,0.25);
  border: 1px solid var(--das-border);
  border-radius: var(--das-radius-sm);
  padding: 0.5rem 1rem;
  width: 100%; max-width: 160px;
  cursor: pointer;
}
.set-time-input:focus { outline: none; border-color: var(--das-primary); }
.set-time-card__hint { font-size: 0.62rem; color: #475569; margin-top: 0.5rem; }

.set-time-card__divider {
  display: flex; flex-direction: column;
  align-items: center; gap: 0.3rem; flex-shrink: 0;
}
.set-time-card__divider-line {
  width: 1px; height: 30px;
  background: linear-gradient(to bottom, transparent, var(--das-border), transparent);
}
.set-time-card__divider-icon {
  color: #475569; font-size: 1rem;
}

/* ═══════════════════════════════════════
   SECTION LABEL
═══════════════════════════════════════ */
.set-section-label {
  font-size: 0.6rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 1.2px;
  color: #475569; margin-bottom: 0.75rem;
  display: flex; align-items: center; gap: 0.5rem;
}
.set-section-label::after {
  content: ''; flex: 1; height: 1px;
  background: var(--das-border);
}

/* ═══════════════════════════════════════
   TOGGLES
═══════════════════════════════════════ */
.set-toggles {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}
.set-toggle-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.85rem 1rem;
  border-radius: var(--das-radius-sm);
  border: 1px solid var(--das-border);
  background: rgba(15,23,42,0.3);
  transition: all 0.2s;
}
.set-toggle-item:hover { background: rgba(15,23,42,0.5); }
.set-toggle-item__icon {
  width: 36px; height: 36px; border-radius: 5px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.05rem; flex-shrink: 0;
}
.set-toggle-item--primary  .set-toggle-item__icon { background: var(--das-primary-soft); color: var(--das-primary); }
.set-toggle-item--danger   .set-toggle-item__icon { background: var(--das-danger-soft);  color: var(--das-danger);  }
.set-toggle-item--success  .set-toggle-item__icon { background: var(--das-success-soft); color: var(--das-success); }
.set-toggle-item--info     .set-toggle-item__icon { background: var(--das-info-soft);    color: var(--das-info);    }
.set-toggle-item__info { flex: 1; min-width: 0; }
.set-toggle-item__label {
  font-size: 0.78rem; font-weight: 700; color: #e2e8f0; line-height: 1.2; margin-bottom: 2px;
}
.set-toggle-item__sub { font-size: 0.65rem; color: #64748b; }

/* Custom Switch */
.set-switch { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
.set-switch__input { opacity: 0; width: 0; height: 0; position: absolute; }
.set-switch__slider {
  position: absolute; inset: 0;
  cursor: pointer; border-radius: 22px;
  background: rgba(255,255,255,0.1);
  border: 1px solid var(--das-border);
  transition: all 0.25s;
}
.set-switch__slider::before {
  content: ''; position: absolute;
  width: 16px; height: 16px; border-radius: 50%;
  left: 2px; top: 50%; transform: translateY(-50%);
  background: #475569; transition: all 0.25s;
}
.set-switch__input:checked + .set-switch__slider { border-color: transparent; }
.set-switch--primary  .set-switch__input:checked + .set-switch__slider { background: var(--das-primary); }
.set-switch--danger   .set-switch__input:checked + .set-switch__slider { background: var(--das-danger);  }
.set-switch--success  .set-switch__input:checked + .set-switch__slider { background: var(--das-success); }
.set-switch--info     .set-switch__input:checked + .set-switch__slider { background: var(--das-info);    }
.set-switch--warning  .set-switch__input:checked + .set-switch__slider { background: var(--das-warning); }

.set-switch__input:checked + .set-switch__slider::before {
  transform: translate(18px, -50%); background: white;
}

/* ═══════════════════════════════════════
   INFO BANNER
═══════════════════════════════════════ */
.set-info-banner {
  display: flex; align-items: flex-start; gap: 0.85rem;
  padding: 0.85rem 1rem;
  border-radius: var(--das-radius-sm);
  font-size: 0.78rem; color: #94a3b8;
}
.set-info-banner i { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }
.set-info-banner--info {
  background: rgba(0,207,232,0.07);
  border: 1px solid rgba(0,207,232,0.2);
}
.set-info-banner--info i { color: var(--das-info); }
.set-info-banner strong { color: var(--das-info); }
.set-info-banner code {
  background: rgba(0,207,232,0.1);
  color: var(--das-info); padding: 0 4px; border-radius: 3px;
  font-size: 0.72rem;
}

/* ═══════════════════════════════════════
   BRANDING
═══════════════════════════════════════ */
.set-branding-wrap {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 2rem; align-items: start;
}
.set-logo-preview {
  display: flex; align-items: center; justify-content: center;
  min-height: 220px;
  background: rgba(15,23,42,0.5);
  border: 1px dashed var(--das-border);
  border-radius: var(--das-radius);
  padding: 1.5rem;
}
.set-logo-preview__img {
  max-width: 100%; max-height: 180px;
  object-fit: contain; border-radius: var(--das-radius-sm);
}
.set-logo-preview__empty {
  text-align: center; color: #334155;
}
.set-logo-preview__empty i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
.set-logo-preview__empty span { font-size: 0.72rem; }

.set-upload-zone {
  padding: 2rem 1.5rem;
  background: rgba(15,23,42,0.4);
  border: 1.5px dashed rgba(255,255,255,0.1);
  border-radius: var(--das-radius);
  text-align: center;
  transition: all 0.2s;
  cursor: pointer;
}
.set-upload-zone:hover { border-color: var(--das-primary); background: var(--das-primary-soft); }
.set-upload-zone__icon { font-size: 2rem; color: #475569; margin-bottom: 0.5rem; }
.set-upload-zone__title { font-size: 0.85rem; font-weight: 600; color: #94a3b8; margin: 0 0 4px; }
.set-upload-zone__sub   { font-size: 0.7rem; color: #475569; margin: 0 0 1rem; }

.set-upload-hints { margin-top: 1rem; }
.set-upload-hint-item {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.72rem; color: #64748b; margin-bottom: 4px;
}

.set-url-upload { margin-top: 1.5rem; }
.set-url-upload__divider {
  display: flex; align-items: center;
  margin-bottom: 1rem;
}
.set-url-upload__divider::before, .set-url-upload__divider::after {
  content: ''; flex: 1; height: 1px;
  background: var(--das-border);
}
.set-url-upload__divider-text {
  padding: 0 1rem;
  font-size: 0.72rem; color: #475569;
  text-transform: uppercase; letter-spacing: 1px;
}

/* ═══════════════════════════════════════
   INTEGRATION CARDS
═══════════════════════════════════════ */
.set-integration-card {
  border-radius: var(--das-radius);
  border: 1px solid var(--das-border);
  overflow: hidden;
}
.set-integration-card__head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0.85rem 1.1rem;
  border-bottom: 1px solid var(--das-border);
}
.set-integration-card--wa .set-integration-card__head { background: rgba(40,199,111,0.06); }
.set-integration-card--tg .set-integration-card__head { background: rgba(0,207,232,0.06);  }
.set-integration-card__brand {
  display: flex; align-items: center; gap: 0.6rem;
  font-size: 0.85rem; font-weight: 700; color: #e2e8f0;
}
.set-integration-card--wa .set-integration-card__brand i { color: var(--das-success); font-size: 1.2rem; }
.set-integration-card--tg .set-integration-card__brand i { color: var(--das-info);    font-size: 1.2rem; }
.set-integration-card__body { padding: 1.1rem; }

/* Chips */
.set-chip {
  display: inline-flex; align-items: center;
  font-size: 0.6rem; font-weight: 800;
  padding: 2px 8px; border-radius: 20px;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.set-chip.--success { background: var(--das-success-soft); color: var(--das-success); }
.set-chip.--info    { background: var(--das-info-soft);    color: var(--das-info);    }

/* ═══════════════════════════════════════
   SAVE BUTTON
═══════════════════════════════════════ */
.set-save-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
  background: var(--das-primary);
  border: none; border-radius: var(--das-radius-sm);
  color: white; font-size: 0.82rem; font-weight: 700;
  padding: 0.7rem 1.25rem; cursor: pointer;
  transition: all 0.2s ease;
  letter-spacing: 0.3px;
}
.set-save-btn:hover {
  background: #6259e8;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(115,103,240,0.3);
}
.set-save-btn:active { transform: translateY(0); }
.set-save-btn i { font-size: 1.05rem; }

.set-btn {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: 0.78rem; font-weight: 600;
  padding: 0.55rem 1.1rem; border-radius: var(--das-radius-sm);
  border: none; cursor: pointer; transition: all 0.2s;
  text-decoration: none;
}
.set-btn--primary {
  background: var(--das-primary); color: white;
}
.set-btn--primary:hover { background: #6259e8; color: white; }

.set-footer-save {
  margin-top: 1.25rem;
  display: flex; justify-content: flex-end;
}

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */

/* Large desktop: tampilkan label penuh */
@media (min-width: 1200px) {
  .set-tab-btn { padding: 0.65rem 1.25rem; font-size: 0.82rem; }
}

/* Tablet landscape (768–1199px): sedikit lebih kompak */
@media (max-width: 1199px) {
  .set-tab-btn { padding: 0.6rem 0.95rem; font-size: 0.78rem; }
}

/* Tablet portrait (576–991px): icon + label lebih kecil */
@media (max-width: 991px) {
  .set-tabbar { padding: 0.35rem; gap: 0.2rem; }
  .set-tab-btn { padding: 0.55rem 0.85rem; font-size: 0.73rem; }
  .set-tab-btn__icon { font-size: 0.9rem; }
  .set-toggles { grid-template-columns: 1fr; }
  .set-branding-wrap { grid-template-columns: 1fr; }
  .set-panel__body { padding: 1.15rem; }
}

/* Mobile (max 575px): icon saja, label tetap muncul tapi lebih kecil */
@media (max-width: 575px) {
  .set-tabbar {
    padding: 0.3rem;
    gap: 0.15rem;
    border-radius: var(--das-radius-sm);
  }
  .set-tab-btn {
    flex-direction: column;
    padding: 0.5rem 0.6rem;
    gap: 0.25rem;
    min-width: 64px;
    font-size: 0;
  }
  .set-tab-btn__label {
    font-size: 0.58rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    color: inherit;
    display: block;
  }
  .set-tab-btn__icon { font-size: 1.05rem; }
  .set-form-grid { grid-template-columns: 1fr; }
  .set-field--full { grid-column: 1; }
  .set-time-cards { flex-direction: column; }
  .set-time-card__divider { flex-direction: row; }
  .set-time-card__divider-line { width: 40px; height: 1px; }
  .set-hero__inner { flex-direction: column; align-items: flex-start; }
  .set-panel__body { padding: 1rem 0.85rem; }
  .set-hero__inner { padding: 1.25rem 1rem; }
}

/* Extra small (max 400px): sangat compact */
@media (max-width: 400px) {
  .set-tab-btn { min-width: 54px; padding: 0.45rem 0.5rem; }
  .set-tab-btn__icon { font-size: 0.95rem; }
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.spin {
  animation: spin 1s linear infinite;
  display: inline-block;
}
</style>
@endsection


@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── TAB NAVIGATION ── */
  const navItems = document.querySelectorAll('.set-tab-btn');
  const tabs     = document.querySelectorAll('.set-tab');

  navItems.forEach(item => {
    item.addEventListener('click', function () {
      const target = this.dataset.tab;

      navItems.forEach(n => n.classList.remove('active'));
      tabs.forEach(t => t.classList.remove('active'));

      this.classList.add('active');
      document.getElementById('tab-' + target)?.classList.add('active');

      // Scroll tab button into view on mobile
      this.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });

      // persist
      localStorage.setItem('set_active_tab_v2', target);
    });
  });

  // Restore active tab
  const savedTab = localStorage.getItem('set_active_tab_v2');
  if (savedTab) {
    const savedBtn = document.querySelector(`.set-tab-btn[data-tab="${savedTab}"]`);
    if (savedBtn) savedBtn.click();
  }

  /* ── PASSWORD TOGGLE ── */
  const eyeBtn = document.querySelector('.set-input-eye');
  const pwdInput = document.querySelector('.set-password-toggle .set-input');
  if (eyeBtn && pwdInput) {
    eyeBtn.addEventListener('click', function () {
      const type = pwdInput.type === 'password' ? 'text' : 'password';
      pwdInput.type = type;
      const icon = this.querySelector('i');
      icon.className = type === 'password' ? 'ti tabler-eye-off' : 'ti tabler-eye';
    });
  }

  /* ── LOGO PREVIEW ── */
  const logoInput   = document.getElementById('upload_logo');
  const logoImgEl   = document.getElementById('logoPreviewImg');
  const logoEmpty   = document.getElementById('logoPreviewEmpty');
  const uploadZone  = document.getElementById('uploadZone');

  if (logoInput) {
    logoInput.addEventListener('change', function () {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
          logoImgEl.src = e.target.result;
          logoImgEl.classList.remove('d-none');
          if (logoEmpty) logoEmpty.classList.add('d-none');
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }

  // Drag-drop upload zone
  if (uploadZone && logoInput) {
    ['dragenter','dragover'].forEach(ev => {
      uploadZone.addEventListener(ev, e => {
        e.preventDefault();
        uploadZone.style.borderColor = 'var(--das-primary)';
        uploadZone.style.background  = 'var(--das-primary-soft)';
      });
    });
    ['dragleave','drop'].forEach(ev => {
      uploadZone.addEventListener(ev, e => {
        uploadZone.style.borderColor = '';
        uploadZone.style.background  = '';
      });
    });
    uploadZone.addEventListener('drop', e => {
      e.preventDefault();
      if (e.dataTransfer.files.length) {
        logoInput.files = e.dataTransfer.files;
        logoInput.dispatchEvent(new Event('change'));
      }
    });
  }

  /* ── FORM SUBMIT LOADING STATE ── */
  const form = document.getElementById('formPengaturan');
  if (form) {
    form.addEventListener('submit', function () {
      document.querySelectorAll('.set-save-btn').forEach(btn => {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> <span>Memproses...</span>';
      });
    });
  }

  /* ── DISMISS ALERT ── */
  document.querySelectorAll('.set-toast__close').forEach(btn => {
    btn.addEventListener('click', function () {
      this.closest('.set-toast')?.remove();
    });
  });

});

function triggerSyncPusat() {
  if (confirm('Apakah Anda yakin ingin menyinkronkan data identitas lembaga dari server pusat?')) {
    const btn = document.getElementById('syncPusatBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ti tabler-loader-2 spin"></i> <span>Menyinkronkan...</span>';

    // Create a hidden form to submit the sync request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("admin.pengaturan.api-source.sync-now") }}';
    
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
  }
}
</script>
@endsection