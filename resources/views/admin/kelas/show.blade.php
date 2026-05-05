@extends('layouts/layoutMaster')

@section('title', 'Detail Kelas — ' . $kelas->nama)

@section('vendor-style')
  @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
  @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-style')
  <style>
    .siswa-row-hover {
      transition: background 0.15s ease;
    }

    .siswa-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 5px 12px;
      border-radius: 6px;
      font-size: 0.8rem;
      font-weight: 500;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: opacity 0.15s ease, transform 0.15s ease;
      color: #fff !important;
    }

    .action-btn:hover {
      opacity: 0.85;
      transform: translateY(-1px);
    }

    .info-card {
      background: rgba(255, 255, 255, 0.04);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 1.25rem 1.5rem;
    }

    .info-card__item {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .info-card__label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      opacity: 0.5;
    }

    .info-card__value {
      font-size: 0.9rem;
      font-weight: 500;
    }

    #modalTambahSiswa .modal-content,
    #modalHapusSiswa .modal-content {
      border: 1px solid rgba(255, 255, 255, 0.1);
      background: #1e1e2d;
      border-radius: 12px;
      overflow: hidden;
    }

    #modalTambahSiswa .modal-header {
      background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1.25rem 1.5rem;
    }

    #modalTambahSiswa .modal-body {
      padding: 1.5rem;
    }

    #modalTambahSiswa .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      padding: 1rem 1.5rem;
      background: rgba(255, 255, 255, 0.02);
    }

    #modalHapusSiswa .modal-header {
      background: linear-gradient(135deg, #2d1a1a 0%, #3d0f0f 100%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    #modalHapusSiswa .modal-footer {
      border-top: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.02);
    }

    .modal-icon-header {
      width: 44px;
      height: 44px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .siswa-checkbox-row {
      transition: background 0.1s ease;
    }

    .siswa-checkbox-row:has(input:checked) {
      background: rgba(40, 199, 111, 0.06) !important;
    }

    .siswa-checkbox-row:hover {
      background: rgba(255, 255, 255, 0.03) !important;
    }

    /* PAGINATION — sama persis dengan halaman siswa */
    .das-page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 5px;
      border: 1px solid rgba(255,255,255,0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }
    .das-page-btn:hover {
      background: rgba(255,255,255,0.08);
      color: #fff;
      border-color: rgba(255,255,255,0.12);
    }
    .das-page-active {
      background: #7367f0 !important;
      color: #fff !important;
      border-color: #7367f0 !important;
    }
    .das-page-dots {
      border-color: transparent;
      background: transparent;
      color: #555;
      pointer-events: none;
    }
    .page-item.disabled .das-page-btn {
      opacity: 0.35;
      pointer-events: none;
    }

    /* SEARCH & PER-PAGE */
    #searchSiswa::placeholder { color: rgba(255,255,255,0.4); }
    #searchSiswa:focus {
      outline: none;
      box-shadow: none;
      background: rgba(255,255,255,0.08) !important;
      border-color: rgba(115,103,240,0.5) !important;
    }
    #perPageSiswa option { background: #1a1a2e; color: #ccc; }
    #perPageSiswa:focus { outline: none; box-shadow: none; }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-users text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            <a href="{{ route('admin.master-data') }}" class="text-white text-decoration-none">Master Data</a>
            /
            <a href="{{ route('admin.kelas.index') }}" class="text-white text-decoration-none">Kelas</a>
            / {{ $kelas->nama }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">Detail Kelas — {{ $kelas->nama }}</h4>
          <p class="das-hero__subtitle">Kelola siswa dalam kelas ini.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <a href="{{ route('admin.kelas.index') }}" class="btn das-btn --secondary">
          <i class="ti tabler-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <button type="button" class="btn das-btn --warning" data-bs-toggle="modal"
          data-bs-target="#modalPindahMassal">
          <i class="ti tabler-arrows-left-right me-1"></i> Pindah Kelas
        </button>
        <button type="button" class="btn das-btn --primary" data-bs-toggle="modal"
          data-bs-target="#modalTambahSiswa">
          <i class="ti tabler-user-plus me-1"></i> Tambah Siswa
        </button>
      </div>
    </div>
  </div>

  {{-- FLASH MESSAGES --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-circle-check fs-5"></i>
      <span>{{ session('success') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-sm"
      role="alert" style="border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span>{{ session('error') }}</span>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: INFO CARD
  ═══════════════════════════════════════════════════════ --}}
  <div class="info-card mb-4">
    <div class="row g-4">
      <div class="col-6 col-md-2">
        <div class="info-card__item">
          <span class="info-card__label">Nama Kelas</span>
          <span class="info-card__value text-white">{{ $kelas->nama }}</span>
        </div>
      </div>
      <div class="col-6 col-md-1">
        <div class="info-card__item">
          <span class="info-card__label">Tingkat</span>
          @php
            $tingkatColor = match ($kelas->tingkat) {
                'X' => 'primary',
                'XI' => 'warning',
                'XII' => 'danger',
                default => 'secondary',
            };
          @endphp
          <span class="badge bg-label-{{ $tingkatColor }}">{{ $kelas->tingkat }}</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="info-card__item">
          <span class="info-card__label">Jurusan</span>
          <span class="info-card__value text-white-75">{{ $kelas->jurusan ?? '—' }}</span>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="info-card__item">
          <span class="info-card__label">Wali Kelas</span>
          @if ($kelas->waliKelas)
            <span class="info-card__value text-white-75">{{ $kelas->waliKelas->nama_lengkap }}</span>
          @else
            <span class="info-card__value text-white-50">— Belum ditentukan</span>
          @endif
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="info-card__item">
          <span class="info-card__label">Tahun Akademik</span>
          @if ($kelas->tahunAkademik)
            <span class="info-card__value">
              <span class="badge bg-label-warning">{{ $kelas->tahunAkademik->nama }}</span>
              <span class="text-white-50 small ms-1">{{ ucfirst($kelas->tahunAkademik->semester) }}</span>
            </span>
          @else
            <span class="info-card__value text-white-50">—</span>
          @endif
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="info-card__item">
          <span class="info-card__label">Jumlah Siswa</span>
          <span class="info-card__value">
            <span class="das-chip --info">{{ $totalSiswaCount }} Siswa</span>
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: TABEL SISWA
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-panel">
    <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
      style="border-color:rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
        <i class="ti tabler-users text-info"></i> Daftar Siswa
      </h6>
      <div class="d-flex align-items-center gap-3 flex-wrap">
        {{-- Search --}}
        <div class="position-relative" style="min-width:220px;max-width:320px;">
          <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
            style="font-size:0.85rem;pointer-events:none;"></i>
          <input type="text" id="searchSiswa" class="form-control border-0 text-white"
            placeholder="Cari nama, NISN, atau NIS..."
            style="background:rgba(255,255,255,0.05);height:38px;padding-left:2.2rem;font-size:0.85rem;">
        </div>
        {{-- Per page --}}
        <select id="perPageSiswa" class="form-select border-0 text-white w-auto"
          style="background:rgba(255,255,255,0.05);height:38px;font-size:0.85rem;cursor:pointer;">
          <option value="10">10</option>
          <option value="12">12</option>
          <option value="30">30</option>
          <option value="50">50</option>
          <option value="all">Semua</option>
        </select>
        <span class="das-chip --success" id="siswaCountChip">{{ $totalSiswaCount }} Terdaftar</span>
      </div>
    </div>
    <div class="das-panel__body p-0">
      <div id="siswaTableContainer">
        @include('admin.kelas.siswa-table')
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL TAMBAH SISWA KE KELAS
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:620px;">
      <div class="modal-content shadow-lg">

        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(40,199,111,0.2);border:1px solid rgba(40,199,111,0.35);">
              <i class="ti tabler-user-plus text-success fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Tambah Siswa ke Kelas</h5>
              <small class="text-white-50">Pilih siswa yang akan ditambahkan ke kelas
                <strong class="text-info">{{ $kelas->nama }}</strong>.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('admin.kelas.add-siswa', $kelas) }}" method="POST">
          @csrf

          <div class="modal-body">

            @if ($siswaAvailable->isEmpty())
              <div class="d-flex flex-column align-items-center gap-2 py-4 opacity-50">
                <i class="ti tabler-users-off" style="font-size:2.5rem;"></i>
                <span class="small text-center">Tidak ada siswa yang tersedia untuk ditambahkan.<br>Semua siswa
                  mungkin sudah berada di kelas ini atau di kelas lain.</span>
              </div>
            @else
              <p class="small text-white-50 mb-3">
                <i class="ti tabler-info-circle me-1"></i>
                Centang siswa yang ingin ditambahkan, lalu klik "Tambah ke Kelas".
              </p>

              {{-- Select All helper --}}
              <div class="d-flex align-items-center gap-2 mb-2 px-1">
                <input type="checkbox" id="selectAllSiswa" class="form-check-input mt-0">
                <label for="selectAllSiswa" class="small text-white-50 mb-0 user-select-none" style="cursor:pointer;">
                  Pilih Semua ({{ $siswaAvailable->count() }} siswa)
                </label>
              </div>

              <div class="table-responsive" style="max-height:340px;overflow-y:auto;">
                <table class="table table-hover align-middle mb-0" style="color:inherit;font-size:0.875rem;">
                  <thead
                    style="background:rgba(255,255,255,0.04);font-size:0.72rem;text-transform:uppercase;letter-spacing:0.6px;opacity:0.7;position:sticky;top:0;z-index:1;">
                    <tr>
                      <th class="ps-3 py-2" style="width:40px;"></th>
                      <th class="py-2">NISN</th>
                      <th class="py-2">Nama Lengkap</th>
                      <th class="py-2 text-center">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($siswaAvailable as $sa)
                      @php
                        $saStatusColor = match ($sa->status) {
                            'aktif' => 'success',
                            'alumni' => 'warning',
                            default => 'secondary',
                        };
                        $saAvatarColor = ['primary', 'success', 'info', 'warning', 'danger'][($loop->index) % 5];
                      @endphp
                      <tr class="siswa-checkbox-row">
                        <td class="ps-3 py-2">
                          <input type="checkbox" name="siswa_ids[]" value="{{ $sa->id }}"
                            class="form-check-input siswa-checkbox mt-0">
                        </td>
                        <td class="py-2">
                          <span class="small text-white-75 font-monospace">{{ $sa->nisn ?? $sa->nis ?? '—' }}</span>
                        </td>
                        <td class="py-2">
                          <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs">
                              <span class="avatar-initial rounded-circle bg-label-{{ $saAvatarColor }}"
                                style="font-size:0.6rem;">
                                {{ strtoupper(substr($sa->nama_lengkap, 0, 1)) }}
                              </span>
                            </div>
                            <span>{{ $sa->nama_lengkap }}</span>
                          </div>
                        </td>
                        <td class="py-2 text-center">
                          <span class="badge bg-label-{{ $saStatusColor }}">{{ ucfirst($sa->status ?? 'aktif') }}</span>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>

          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            @if (!$siswaAvailable->isEmpty())
              <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm" id="btnTambahSiswa">
                <i class="ti tabler-user-plus me-1"></i>
                <span id="tambahSiswaCount">Tambah ke Kelas</span>
              </button>
            @endif
          </div>
        </form>

      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL KONFIRMASI HAPUS SISWA DARI KELAS
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalHapusSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div
              style="width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(234,84,85,0.2);border:1px solid rgba(234,84,85,0.35);">
              <i class="ti tabler-alert-triangle text-danger fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Hapus dari Kelas</h5>
              <small class="text-white-50">Siswa tidak akan dihapus permanen dari sistem.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center py-4">
          <p class="mb-1 text-white-50">Hapus siswa berikut dari kelas <strong class="text-info">{{ $kelas->nama }}</strong>?</p>
          <p class="fw-bold text-warning fs-6 mb-2" id="hapusSiswaName">—</p>
          <p class="small text-white-50 mb-0">
            <i class="ti tabler-info-circle me-1"></i>
            Data siswa tetap ada, hanya dilepas dari kelas ini.
          </p>
        </div>
        <div class="modal-footer gap-2 justify-content-center">
          <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">
            <i class="ti tabler-x me-1"></i> Batal
          </button>
          <form id="formHapusSiswa" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger fw-semibold px-4 shadow-sm">
              <i class="ti tabler-user-minus me-1"></i> Ya, Hapus dari Kelas
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL PINDAH KELAS MASSAL
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalPindahMassal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(255,159,67,0.2);border:1px solid rgba(255,159,67,0.35);">
              <i class="ti tabler-arrows-left-right text-warning fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Pindah Kelas Massal</h5>
              <small class="text-white-50">Pindahkan semua siswa ke kelas lain.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('admin.kelas.pindah-massal', $kelas) }}" method="POST">
          @csrf
          <div class="modal-body">
            <div class="alert alert-warning border-0 d-flex gap-3 mb-4" style="background:rgba(255,159,67,0.08);border-radius:10px;">
              <i class="ti tabler-alert-triangle fs-4 text-warning"></i>
              <div class="small">
                <strong>Perhatian:</strong> Tindakan ini akan memindahkan <span class="badge bg-warning">{{ $totalSiswaCount }} siswa</span> dari kelas <strong>{{ $kelas->nama }}</strong> ke kelas tujuan yang Anda pilih.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label text-white-50 small text-uppercase fw-bold">Pilih Kelas Tujuan</label>
              <select name="kelas_tujuan_id" class="form-select select2" data-placeholder="Pilih kelas tujuan..." required>
                <option value=""></option>
                @foreach ($kelasOptions as $ko)
                  <option value="{{ $ko->id }}">{{ $ko->nama }} ({{ $ko->jurusan }})</option>
                @endforeach
              </select>
            </div>

            <p class="small text-white-50 mb-0 mt-3">
              <i class="ti tabler-info-circle me-1"></i>
              Pastikan kelas tujuan sudah benar sebelum memproses.
            </p>
          </div>
          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm" {{ $totalSiswaCount == 0 ? 'disabled' : '' }}>
              <i class="ti tabler-arrows-left-right me-1"></i> Pindahkan Sekarang
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════════════
       MODAL PINDAH KELAS INDIVIDUAL
  ══════════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="modalPindahSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
      <div class="modal-content shadow-lg">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-3">
            <div class="modal-icon-header"
              style="background:rgba(255,159,67,0.2);border:1px solid rgba(255,159,67,0.35);">
              <i class="ti tabler-arrows-left-right text-warning fs-5"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0 text-white fw-bold">Pindah Kelas</h5>
              <small class="text-white-50">Pindahkan siswa ke kelas lain.</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
        </div>

        <form id="formPindahSiswa" method="POST">
          @csrf
          <div class="modal-body">
            <div class="mb-4">
              <label class="form-label text-white-50 small text-uppercase fw-bold">Siswa</label>
              <h6 id="pindahSiswaName" class="text-white fw-bold">...</h6>
            </div>

            <div class="mb-3">
              <label class="form-label text-white-50 small text-uppercase fw-bold">Pilih Kelas Tujuan</label>
              <select name="kelas_id" class="form-select select2" data-placeholder="Pilih kelas tujuan..." required>
                <option value=""></option>
                @foreach ($kelasOptions as $ko)
                  <option value="{{ $ko->id }}">{{ $ko->nama }} ({{ $ko->jurusan }})</option>
                @endforeach
              </select>
            </div>

            <p class="small text-white-50 mb-0 mt-3">
              <i class="ti tabler-info-circle me-1"></i>
              Pemindahan dilakukan dalam tahun akademik yang sama.
            </p>
          </div>
          <div class="modal-footer gap-2">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
              <i class="ti tabler-x me-1"></i> Batal
            </button>
            <button type="submit" class="btn btn-warning fw-semibold px-4 shadow-sm">
              <i class="ti tabler-arrows-left-right me-1"></i> Pindahkan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
  <script>
    const kelasId = {{ $kelas->id }};
    const removeSiswaBaseUrl = "{{ url('admin/kelas/' . $kelas->id . '/siswa') }}";

    // ── Hapus siswa dari kelas ──────────────────────────────
    function openHapusSiswa(siswaId, siswaName) {
      document.getElementById('hapusSiswaName').textContent = siswaName;
      document.getElementById('formHapusSiswa').action = removeSiswaBaseUrl + '/' + siswaId;
      new bootstrap.Modal(document.getElementById('modalHapusSiswa')).show();
    }

    // ── Pindah siswa perorangan ─────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
      const modalPindahSiswa = document.getElementById('modalPindahSiswa');
      if (modalPindahSiswa) {
        modalPindahSiswa.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          const id = button.getAttribute('data-id');
          const name = button.getAttribute('data-name');
          
          document.getElementById('pindahSiswaName').textContent = name;
          document.getElementById('formPindahSiswa').action = "{{ url('admin/siswa') }}/" + id + "/pindah-kelas";
        });
        
        // Initialize Select2 in modal
        $(modalPindahSiswa).on('shown.bs.modal', function () {
          $(this).find('.select2').select2({
            dropdownParent: $(this)
          });
        });
      }
    });

    document.addEventListener('DOMContentLoaded', function () {

      // ── Live search + pagination ──────────────────────────
      const container     = document.getElementById('siswaTableContainer');
      const searchInput   = document.getElementById('searchSiswa');
      const perPageSelect = document.getElementById('perPageSiswa');
      const showUrl       = '{{ route('admin.kelas.show', $kelas) }}';
      let searchTimeout;

      function fetchSiswa(page) {
        page = page || 1;
        const search  = encodeURIComponent(searchInput.value || '');
        const perPage = perPageSelect.value || 10;
        const url     = showUrl + '?page=' + page + '&search=' + search + '&per_page=' + perPage;

        container.style.opacity      = '0.45';
        container.style.pointerEvents = 'none';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(function (res) { return res.text(); })
          .then(function (html) {
            container.innerHTML         = html;
            container.style.opacity      = '1';
            container.style.pointerEvents = 'auto';
          })
          .catch(function () {
            container.style.opacity      = '1';
            container.style.pointerEvents = 'auto';
          });
      }

      // Debounce search
      searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () { fetchSiswa(1); }, 400);
      });

      // Per-page change
      perPageSelect.addEventListener('change', function () {
        fetchSiswa(1);
      });

      // Pagination link click delegation
      container.addEventListener('click', function (e) {
        const link = e.target.closest('a.das-page-btn');
        if (link) {
          e.preventDefault();
          const page = link.dataset.page
            || (link.href ? new URL(link.href).searchParams.get('page') : null)
            || 1;
          fetchSiswa(page);
        }
      });

      // ── Tambah siswa checkbox ─────────────────────────────
      const selectAll  = document.getElementById('selectAllSiswa');
      const checkboxes = document.querySelectorAll('.siswa-checkbox');
      const countLabel = document.getElementById('tambahSiswaCount');

      function updateCount() {
        const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (countLabel) {
          countLabel.textContent = checked > 0
            ? 'Tambah ' + checked + ' Siswa ke Kelas'
            : 'Tambah ke Kelas';
        }
      }

      if (selectAll) {
        selectAll.addEventListener('change', function () {
          checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
          updateCount();
        });
      }

      checkboxes.forEach(function (cb) {
        cb.addEventListener('change', function () {
          const allChecked = document.querySelectorAll('.siswa-checkbox:checked').length === checkboxes.length;
          if (selectAll) selectAll.checked = allChecked;
          updateCount();
        });
      });

    });
  </script>
@endsection
