@extends('layouts/layoutMaster')

@section('title', 'Pengaturan WA Gateway')

@section('content')

<div class="set-hero mb-5">
  <div class="set-hero__bg"></div>
  <div class="set-hero__glass"></div>
  <div class="set-hero__grid"></div>
  <div class="set-hero__inner">
    <div class="set-hero__identity">
      <div class="set-hero__icon-wrap">
        <i class="ti tabler-brand-whatsapp"></i>
        <div class="set-hero__icon-glow" style="background:rgba(37,211,102,.35);"></div>
      </div>
      <div>
        <div class="set-hero__badge">
          <span class="pulse-dot" style="background:#25d366;"></span>
          WhatsApp Gateway
        </div>
        <h4 class="set-hero__title text-gradient-gold">Pengaturan WA Gateway</h4>
        <p class="set-hero__sub">Konfigurasi koneksi WhatsApp Gateway untuk pengiriman notifikasi absensi ke orang tua siswa.</p>
      </div>
    </div>
    <div class="set-hero__breadcrumb glass-card">
      <span class="text-muted small"><i class="ti tabler-home me-1"></i>Dashboard</span>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <a href="{{ route('admin.pengaturan.index') }}" class="text-muted small">Pengaturan</a>
      <i class="ti tabler-chevron-right text-muted mx-1" style="font-size:0.7rem;"></i>
      <span class="small text-white fw-semibold">WA Gateway</span>
    </div>
  </div>
</div>

@if (session('success'))
  <div class="set-toast mb-4" id="successToast">
    <div class="set-toast__icon"><i class="ti tabler-circle-check"></i></div>
    <div class="set-toast__msg">{{ session('success') }}</div>
    <button type="button" class="set-toast__close" onclick="document.getElementById('successToast').style.display='none'"><i class="ti tabler-x"></i></button>
  </div>
@endif

@if($errors->any())
  <div class="alert alert-danger mb-4">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
  </div>
@endif

{{-- Status Badge --}}
<div class="d-flex gap-2 mb-4 align-items-center flex-wrap">
  <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="ti tabler-arrow-left me-1"></i>Kembali ke Pengaturan
  </a>
  <a href="{{ route('admin.wa-gateway.keywords.index') }}" class="btn btn-sm btn-primary">
    <i class="ti tabler-key me-1"></i>Kelola Keyword Autoreply
  </a>
  @php $waOn = ($settings['wa_gateway_enabled'] ?? 'Ya') === 'Ya'; @endphp
  <span class="badge {{ $waOn ? 'bg-success' : 'bg-secondary' }} fs-6 px-3 py-2">
    <i class="ti {{ $waOn ? 'tabler-wifi' : 'tabler-wifi-off' }} me-1"></i>
    WA Gateway {{ $waOn ? 'AKTIF' : 'NONAKTIF' }}
  </span>
</div>

<form action="{{ route('admin.wa-gateway.update') }}" method="POST" id="formWaGateway">
  @csrf
  <div>

      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-toggle-right"></i></div>
            <div>
              <div class="set-panel__title">Status WA Gateway</div>
              <div class="set-panel__sub">Aktifkan atau nonaktifkan pengiriman notifikasi WhatsApp.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
            <div class="set-field set-field--full">
              <div class="form-check form-switch form-check-lg">
                <input class="form-check-input" type="checkbox" id="wa_gateway_enabled_check"
                       style="width:3rem;height:1.5rem;"
                       onchange="document.getElementById('wa_gateway_enabled').value = this.checked ? 'Ya' : 'Tidak'"
                       {{ ($settings['wa_gateway_enabled'] ?? 'Ya') === 'Ya' ? 'checked' : '' }}>
                <label class="form-check-label fs-6 fw-semibold ms-2" for="wa_gateway_enabled_check">
                  Aktifkan Pengiriman Notifikasi WhatsApp
                </label>
              </div>
              <input type="hidden" name="wa_gateway_enabled" id="wa_gateway_enabled"
                     value="{{ ($settings['wa_gateway_enabled'] ?? 'Ya') }}">
              <div class="set-field-hint --info mt-3">
                <i class="ti tabler-info-circle"></i>
                Jika dinonaktifkan, tidak ada notifikasi WA yang akan dikirim ke orang tua.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-server-2"></i></div>
            <div>
              <div class="set-panel__title">Koneksi Server</div>
              <div class="set-panel__sub">Konfigurasi endpoint dan API Key gateway WA.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
            <div class="set-field set-field--full">
              <label class="set-label">URL Server WA Gateway <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                <input type="url" class="set-input @error('link_server_wa') is-invalid @enderror"
                       name="link_server_wa"
                       value="{{ old('link_server_wa', $settings['link_server_wa'] ?? 'https://wa.lutfifuadi.my.id/send-message') }}"
                       placeholder="https://wa.lutfifuadi.my.id/send-message">
              </div>
              <div class="set-field-hint">Endpoint lengkap termasuk <code>/send-message</code>.</div>
              @error('link_server_wa')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="set-field set-field--full">
              <label class="set-label">API Key <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                <input type="password" class="set-input @error('wa_api_key') is-invalid @enderror"
                       name="wa_api_key" id="wa_api_key"
                       value="{{ old('wa_api_key', $settings['wa_api_key'] ?? '') }}"
                       placeholder="Masukkan API Key gateway">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKeyVisibility()"
                        style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-eye" id="apiKeyEyeIcon"></i>
                </button>
              </div>
              <div class="set-field-hint">API Key untuk autentikasi ke server WA Gateway.</div>
              @error('wa_api_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>
      </div>

      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-phone"></i></div>
            <div>
              <div class="set-panel__title">Nomor WhatsApp</div>
              <div class="set-panel__sub">Nomor pengirim dan admin untuk notifikasi WA.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
            <div class="set-field">
              <label class="set-label">Nomor Pengirim (Sender/Device) <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_nomor_notifikasi') is-invalid @enderror"
                       name="wa_nomor_notifikasi" maxlength="20"
                       value="{{ old('wa_nomor_notifikasi', $settings['wa_nomor_notifikasi'] ?? '') }}"
                       placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
              </div>
              <div class="set-field-hint">Nomor WA perangkat yang terhubung ke gateway. Bisa gunakan format 08 atau 62.</div>
              @error('wa_nomor_notifikasi')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="set-field">
              <label class="set-label">Nomor Admin</label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_nomor_admin') is-invalid @enderror"
                       name="wa_nomor_admin" maxlength="20"
                       value="{{ old('wa_nomor_admin', $settings['wa_nomor_admin'] ?? '') }}"
                       placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
              </div>
              <div class="set-field-hint">Nomor admin untuk notifikasi sistem.</div>
              @error('wa_nomor_admin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <input type="hidden" name="nomor_server_wa_api_key" value="{{ $settings['nomor_server_wa_api_key'] ?? '' }}">
          </div>
        </div>
      </div>

      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-clock-send"></i></div>
            <div>
              <div class="set-panel__title">Pengaturan Pengiriman</div>
              <div class="set-panel__sub">Atur jeda dan mode pengiriman notifikasi.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
            <div class="set-field">
              <label class="set-label">Jeda Kirim Pesan (detik)</label>
              <div class="set-input-group">
                <input type="number" class="set-input" name="jeda_waktu_kirim_pesan_detik" min="1" max="300"
                       value="{{ old('jeda_waktu_kirim_pesan_detik', $settings['jeda_waktu_kirim_pesan_detik'] ?? 5) }}">
                <span class="set-input-suffix">detik</span>
              </div>
              <div class="set-field-hint">Delay antar pesan untuk anti-flood.</div>
            </div>
            <div class="set-field">
              <label class="set-label">Jeda Notifikasi (detik)</label>
              <div class="set-input-group">
                <input type="number" class="set-input" name="jeda_waktu_kirim_notifikasi_detik" min="1" max="60"
                       value="{{ old('jeda_waktu_kirim_notifikasi_detik', $settings['jeda_waktu_kirim_notifikasi_detik'] ?? 1) }}">
                <span class="set-input-suffix">detik</span>
              </div>
              <div class="set-field-hint">Delay setelah scan QR.</div>
            </div>
            <div class="set-field set-field--full">
              <label class="set-label">Mode Pengiriman</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-bolt"></i></span>
                <select class="set-input" name="pengiriman_notifikasi_scan_qr">
                  @foreach(['Kirim otomatis' => 'Kirim Otomatis (via queue)', 'Kirim manual' => 'Kirim Manual (perlu konfirmasi)'] as $val => $label)
                    <option value="{{ $val }}" {{ ($settings['pengiriman_notifikasi_scan_qr'] ?? 'Kirim otomatis') === $val ? 'selected' : '' }}>
                      {{ $label }}
                    </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════
           WA PENGADUAN
           ═══════════════════════════════════════ --}}
      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --warning"><i class="ti tabler-message-report"></i></div>
            <div>
              <div class="set-panel__title">WA Pengaduan (Notifikasi Pengaduan)</div>
              <div class="set-panel__sub">Konfigurasi WhatsApp untuk notifikasi pengaduan data siswa &amp; orang tua.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">

            {{-- Aktifkan --}}
            <div class="set-field set-field--full">
              <div class="form-check form-switch form-check-lg">
                <input class="form-check-input" type="checkbox" id="wa_pengaduan_enabled_check"
                       style="width:3rem;height:1.5rem;"
                       onchange="document.getElementById('wa_pengaduan_enabled').value = this.checked ? 'Ya' : 'Tidak'"
                       {{ ($settings['wa_pengaduan_enabled'] ?? 'Tidak') === 'Ya' ? 'checked' : '' }}>
                <label class="form-check-label fs-6 fw-semibold ms-2" for="wa_pengaduan_enabled_check">
                  Aktifkan WA Pengaduan
                </label>
              </div>
              <input type="hidden" name="wa_pengaduan_enabled" id="wa_pengaduan_enabled"
                     value="{{ $settings['wa_pengaduan_enabled'] ?? 'Tidak' }}">
              <div class="set-field-hint --info mt-3">
                <i class="ti tabler-info-circle"></i>
                Jika diaktifkan, notifikasi pengaduan baru akan dikirim ke grup admin WA.
              </div>
            </div>

            {{-- API Key --}}
            <div class="set-field set-field--full">
              <label class="set-label">API Key <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                <input type="password" class="set-input @error('wa_pengaduan_api_key') is-invalid @enderror"
                       name="wa_pengaduan_api_key" id="wa_pengaduan_api_key"
                       value="{{ old('wa_pengaduan_api_key', $settings['wa_pengaduan_api_key'] ?? '') }}"
                       placeholder="Masukkan API Key WA Pengaduan">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePengaduanKeyVisibility()"
                        style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-eye" id="pengaduanKeyEyeIcon"></i>
                </button>
              </div>
              <div class="set-field-hint">API Key khusus untuk WA Pengaduan.</div>
              @error('wa_pengaduan_api_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Endpoint URL --}}
            <div class="set-field set-field--full">
              <label class="set-label">Endpoint URL <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                <input type="url" class="set-input @error('wa_pengaduan_endpoint') is-invalid @enderror"
                       name="wa_pengaduan_endpoint"
                       value="{{ old('wa_pengaduan_endpoint', $settings['wa_pengaduan_endpoint'] ?? 'https://wa.lutfifuadi.my.id') }}"
                       placeholder="https://wa.lutfifuadi.my.id">
              </div>
              <div class="set-field-hint">Base URL server WA Pengaduan (tanpa <code>/send-message</code>).</div>
              @error('wa_pengaduan_endpoint')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Nomor Pengirim --}}
            <div class="set-field">
              <label class="set-label">Nomor Pengirim (Sender) <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_pengaduan_sender') is-invalid @enderror"
                       name="wa_pengaduan_sender" maxlength="20"
                       value="{{ old('wa_pengaduan_sender', $settings['wa_pengaduan_sender'] ?? '') }}"
                       placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
              </div>
              <div class="set-field-hint">Nomor WA perangkat yang terhubung ke gateway pengaduan.</div>
              @error('wa_pengaduan_sender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- ID Grup Admin WA --}}
            <div class="set-field">
              <label class="set-label">ID Grup Admin WA</label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_pengaduan_group_id') is-invalid @enderror"
                       name="wa_pengaduan_group_id" maxlength="50"
                       value="{{ old('wa_pengaduan_group_id', $settings['wa_pengaduan_group_id'] ?? '') }}"
                       placeholder="Contoh: 62812xxxxxx-123456789">
              </div>
              <div class="set-field-hint --info mt-1">
                <i class="ti tabler-info-circle"></i>
                Nomor grup WA untuk notifikasi pengaduan baru. Biarkan kosong jika tidak menggunakan grup.
              </div>
              @error('wa_pengaduan_group_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════
           WA VALIDATOR
           ═══════════════════════════════════════ --}}
      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --info"><i class="ti tabler-shield-check"></i></div>
            <div>
              <div class="set-panel__title">WA Validator (Validasi Nomor)</div>
              <div class="set-panel__sub">Konfigurasi WhatsApp untuk validasi nomor siswa &amp; orang tua.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">

            {{-- Aktifkan --}}
            <div class="set-field set-field--full">
              <div class="form-check form-switch form-check-lg">
                <input class="form-check-input" type="checkbox" id="wa_validator_enabled_check"
                       style="width:3rem;height:1.5rem;"
                       onchange="document.getElementById('wa_validator_enabled').value = this.checked ? 'Ya' : 'Tidak'"
                       {{ ($settings['wa_validator_enabled'] ?? 'Tidak') === 'Ya' ? 'checked' : '' }}>
                <label class="form-check-label fs-6 fw-semibold ms-2" for="wa_validator_enabled_check">
                  Aktifkan WA Validator
                </label>
              </div>
              <input type="hidden" name="wa_validator_enabled" id="wa_validator_enabled"
                     value="{{ $settings['wa_validator_enabled'] ?? 'Tidak' }}">
              <div class="set-field-hint --info mt-3">
                <i class="ti tabler-info-circle"></i>
                Jika diaktifkan, sistem akan memvalidasi nomor WA sebelum mengirim notifikasi pengaduan.
              </div>
            </div>

            {{-- API Key --}}
            <div class="set-field set-field--full">
              <label class="set-label">API Key <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                <input type="password" class="set-input @error('wa_validator_api_key') is-invalid @enderror"
                       name="wa_validator_api_key" id="wa_validator_api_key"
                       value="{{ old('wa_validator_api_key', $settings['wa_validator_api_key'] ?? '') }}"
                       placeholder="Masukkan API Key WA Validator">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleValidatorKeyVisibility()"
                       style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-eye" id="validatorKeyEyeIcon"></i>
                </button>
              </div>
              <div class="set-field-hint">API Key khusus untuk WA Validator.</div>
              @error('wa_validator_api_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Endpoint URL --}}
            <div class="set-field set-field--full">
              <label class="set-label">Endpoint URL <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                <input type="url" class="set-input @error('wa_validator_endpoint') is-invalid @enderror"
                       name="wa_validator_endpoint"
                       value="{{ old('wa_validator_endpoint', $settings['wa_validator_endpoint'] ?? 'https://wa.lutfifuadi.my.id') }}"
                       placeholder="https://wa.lutfifuadi.my.id">
              </div>
              <div class="set-field-hint">Base URL server WA Validator (tanpa <code>/check-number</code>).</div>
              @error('wa_validator_endpoint')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Nomor Pengirim --}}
            <div class="set-field set-field--full">
              <label class="set-label">Nomor Pengirim (Sender) <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_validator_sender') is-invalid @enderror"
                       name="wa_validator_sender" maxlength="20"
                       value="{{ old('wa_validator_sender', $settings['wa_validator_sender'] ?? '') }}"
                       placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
              </div>
              <div class="set-field-hint">Nomor WA perangkat yang terhubung ke gateway validator.</div>
              @error('wa_validator_sender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

          </div>
        </div>
      </div>

      {{-- ═══════════════════════════════════════
           WA AUTOREPLY
           ═══════════════════════════════════════ --}}
      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --success"><i class="ti tabler-message-chatbot"></i></div>
            <div>
              <div class="set-panel__title">Autoreply WA (Inbound Response)</div>
              <div class="set-panel__sub">Konfigurasi WhatsApp untuk autoreply berbasis kata kunci pesan masuk.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">

            {{-- Aktifkan --}}
            <div class="set-field set-field--full">
              <div class="form-check form-switch form-check-lg">
                <input class="form-check-input" type="checkbox" id="wa_autoreply_enabled_check"
                       style="width:3rem;height:1.5rem;"
                       onchange="document.getElementById('wa_autoreply_enabled').value = this.checked ? 'Ya' : 'Tidak'"
                       {{ ($settings['wa_autoreply_enabled'] ?? 'Tidak') === 'Ya' ? 'checked' : '' }}>
                <label class="form-check-label fs-6 fw-semibold ms-2" for="wa_autoreply_enabled_check">
                  Aktifkan Autoreply WA
                </label>
              </div>
              <input type="hidden" name="wa_autoreply_enabled" id="wa_autoreply_enabled"
                     value="{{ $settings['wa_autoreply_enabled'] ?? 'Tidak' }}">
              <div class="set-field-hint --info mt-3">
                <i class="ti tabler-info-circle"></i>
                Jika diaktifkan, sistem akan memproses pesan masuk dan menjawab otomatis sesuai kata kunci.
              </div>
            </div>

            {{-- Nomor Pengirim --}}
            <div class="set-field">
              <label class="set-label">Nomor Pengirim (Autoreply) <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <input type="text" class="set-input @error('wa_autoreply_sender') is-invalid @enderror"
                       name="wa_autoreply_sender" maxlength="20"
                       value="{{ old('wa_autoreply_sender', $settings['wa_autoreply_sender'] ?? '') }}"
                       placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
              </div>
              <div class="set-field-hint">Nomor WA perangkat yang terhubung untuk autoreply.</div>
              @error('wa_autoreply_sender')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- API Key --}}
            <div class="set-field">
              <label class="set-label">API Key Autoreply <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-key"></i></span>
                <input type="password" class="set-input @error('wa_autoreply_api_key') is-invalid @enderror"
                       name="wa_autoreply_api_key" id="wa_autoreply_api_key"
                       value="{{ old('wa_autoreply_api_key', $settings['wa_autoreply_api_key'] ?? '') }}"
                       placeholder="Masukkan API Key WA Autoreply">
                <button class="btn btn-outline-secondary" type="button" onclick="toggleAutoreplyKeyVisibility()"
                       style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-eye" id="autoreplyKeyEyeIcon"></i>
                </button>
              </div>
              <div class="set-field-hint">API Key untuk autentikasi ke server WA Autoreply.</div>
              @error('wa_autoreply_api_key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Endpoint URL --}}
            <div class="set-field set-field--full">
              <label class="set-label">Endpoint URL <span class="text-danger">*</span></label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-link"></i></span>
                <input type="url" class="set-input @error('wa_autoreply_endpoint') is-invalid @enderror"
                       name="wa_autoreply_endpoint"
                       value="{{ old('wa_autoreply_endpoint', $settings['wa_autoreply_endpoint'] ?? 'https://wa.lutfifuadi.my.id') }}"
                       placeholder="https://wa.lutfifuadi.my.id">
              </div>
              <div class="set-field-hint">Base URL server WA Autoreply (e.g. <code>https://wa.lutfifuadi.my.id</code>).</div>
              @error('wa_autoreply_endpoint')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Webhook URL (Readonly) --}}
            <div class="set-field set-field--full">
              <label class="set-label">Webhook URL (Readonly)</label>
              <div class="set-input-group">
                <span class="set-input-prefix"><i class="ti tabler-webhook"></i></span>
                <input type="text" class="set-input" readonly
                       value="{{ url('/api/v1/webhook/whatsapp-autoreply?token=' . ($settings['wa_autoreply_webhook_token'] ?? '')) }}">
                <button class="btn btn-outline-info" type="button" onclick="copyWebhookUrl(this)"
                       style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-copy me-1"></i>Copy
                </button>
              </div>
              <div class="set-field-hint">Copy URL ini dan paste di dashboard provider WA Anda (e.g. Fonnte).</div>
            </div>

            <input type="hidden" name="wa_autoreply_webhook_token" value="{{ $settings['wa_autoreply_webhook_token'] ?? '' }}">

          </div>
        </div>
      </div>

      <div class="set-panel mb-4">
        <div class="set-panel__head">
          <div class="set-panel__title-wrap">
            <div class="set-panel__icon --primary"><i class="ti tabler-flask"></i></div>
            <div>
              <div class="set-panel__title">Test Koneksi</div>
              <div class="set-panel__sub">Cek apakah nomor terdaftar di WhatsApp sebelum mengirim.</div>
            </div>
          </div>
        </div>
        <div class="set-panel__body">
          <div class="set-form-grid">
              <div class="set-field set-field--full">
                <label class="set-label">Nomor untuk dicek</label>
                <div class="set-input-group">
                  <input type="text" class="set-input" id="testNumber" maxlength="20" placeholder="08xxxxxxxxx atau 628xxxxxxxxx">
                <button class="btn btn-outline-success" type="button" onclick="testWaConnection()" id="btnTestWa"
                        style="border-radius:0 8px 8px 0 !important;">
                  <i class="ti tabler-check me-1"></i>Cek Nomor
                </button>
              </div>
              <div id="testResult" class="d-none mt-3">
                <div class="alert mb-0" id="testResultAlert">
                  <i class="ti me-1" id="testResultIcon"></i>
                  <span id="testResultMsg"></span>
                </div>
              </div>
              <div class="set-field-hint --info mt-2">
                <i class="ti tabler-info-circle"></i>
                Sistem otomatis memvalidasi nomor sebelum mengirim notifikasi.
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('admin.pengaturan.index') }}" class="btn btn-outline-secondary">
          <i class="ti tabler-arrow-left me-1"></i>Batal
        </a>
        <button type="submit" class="set-save-btn">
          <i class="ti tabler-device-floppy me-1"></i>Simpan Pengaturan WA Gateway
        </button>
      </div>

  </div>
</form>

@endsection

@section('page-style')
<style>
/* ═══════════════════════════════════════
   CSS VARIABLES
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
   CONTENT PANELS
═══════════════════════════════════════ */
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
  flex: 1; padding: 0.6rem 0.75rem;
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
  color: #475569;
}
.set-field-hint.--success { color: var(--das-success); }
.set-field-hint.--warning { color: var(--das-warning); }
.set-field-hint.--info    { color: var(--das-info); }

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

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
@media (max-width: 767px) {
  .set-form-grid { grid-template-columns: 1fr; }
  .set-field--full { grid-column: 1; }
  .set-hero__inner { flex-direction: column; align-items: flex-start; }
}
</style>
@endsection

@push('scripts')
<script>
function toggleApiKeyVisibility() {
  const input = document.getElementById('wa_api_key');
  const icon = document.getElementById('apiKeyEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'ti tabler-eye-off';
  } else {
    input.type = 'password';
    icon.className = 'ti tabler-eye';
  }
}

function togglePengaduanKeyVisibility() {
  const input = document.getElementById('wa_pengaduan_api_key');
  const icon = document.getElementById('pengaduanKeyEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'ti tabler-eye-off';
  } else {
    input.type = 'password';
    icon.className = 'ti tabler-eye';
  }
}

function toggleValidatorKeyVisibility() {
  const input = document.getElementById('wa_validator_api_key');
  const icon = document.getElementById('validatorKeyEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'ti tabler-eye-off';
  } else {
    input.type = 'password';
    icon.className = 'ti tabler-eye';
  }
}

function toggleAutoreplyKeyVisibility() {
  const input = document.getElementById('wa_autoreply_api_key');
  const icon = document.getElementById('autoreplyKeyEyeIcon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'ti tabler-eye-off';
  } else {
    input.type = 'password';
    icon.className = 'ti tabler-eye';
  }
}

function copyWebhookUrl(btn) {
  const input = btn.previousElementSibling;
  input.select();
  input.setSelectionRange(0, 99999);
  navigator.clipboard.writeText(input.value);

  const origHtml = btn.innerHTML;
  btn.innerHTML = '<i class="ti tabler-check me-1"></i>Copied!';
  setTimeout(() => {
    btn.innerHTML = origHtml;
  }, 2000);
}

async function testWaConnection() {
  const btn = document.getElementById('btnTestWa');
  const number = document.getElementById('testNumber').value.trim();
  const resultDiv = document.getElementById('testResult');
  const alertDiv = document.getElementById('testResultAlert');
  const iconEl = document.getElementById('testResultIcon');
  const msgEl = document.getElementById('testResultMsg');

  if (!number) {
    alert('Masukkan nomor terlebih dahulu!');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengecek...';
  resultDiv.classList.add('d-none');

  try {
    const res = await fetch('{{ route("admin.wa-gateway.test") }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ test_number: number })
    });

    const data = await res.json();

    resultDiv.classList.remove('d-none');
    if (data.status) {
      alertDiv.className = 'alert alert-success mb-0';
      iconEl.className = 'ti tabler-circle-check me-1';
    } else {
      alertDiv.className = 'alert alert-warning mb-0';
      iconEl.className = 'ti tabler-alert-triangle me-1';
    }
    msgEl.textContent = data.message;
  } catch (e) {
    resultDiv.classList.remove('d-none');
    alertDiv.className = 'alert alert-danger mb-0';
    iconEl.className = 'ti tabler-x me-1';
    msgEl.textContent = 'Terjadi error: ' + e.message;
  } finally {
    btn.disabled = false;
    btn.innerHTML = '<i class="ti tabler-check me-1"></i>Cek Nomor';
  }
}
</script>
@endpush
