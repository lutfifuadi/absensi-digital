@extends('layouts/layoutMaster')

@section('title', 'Gamifikasi & Prestise — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">

  <style>
    .rekap-row-hover {
      transition: background 0.18s ease;
    }
    .rekap-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }
    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      transition: all 0.2s ease;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: rgba(255, 255, 255, 0.04);
      color: var(--das-text-muted);
    }
    .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(108, 124, 240, 0.2);
      border-color: rgba(108, 124, 240, 0.4);
      color: #fff;
    }
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: var(--das-text) !important;
      border-radius: 6px !important;
    }
    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.07) !important;
      border-color: var(--das-gold) !important;
      box-shadow: 0 0 0 2px rgba(212, 169, 74, 0.2) !important;
    }
    .das-page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
      padding: 0 8px;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 6px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: transparent;
      color: var(--das-text-muted);
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
    }
    .das-page-btn:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.15);
    }
    .das-page-btn:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }
    .das-page-active {
      background: var(--das-primary) !important;
      color: #fff !important;
      border-color: var(--das-primary) !important;
      box-shadow: 0 4px 12px rgba(108, 124, 240, 0.3);
    }
    .das-page-dots {
      border-color: transparent;
      background: transparent;
      color: var(--das-text-dim);
      pointer-events: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
    }
    .text-gradient-gold {
      background: linear-gradient(135deg, #F3D999, #D4A94A 60%);
      -webkit-background-clip: text;
      background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .spin { animation: spin 1s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1: HERO HEADER — Identitas Gamifikasi + Live Clock
       ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-6">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
      {{-- Identitas --}}
      <div class="das-hero__identity me-md-3">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder" style="background: rgba(212, 169, 74, 0.15); border: 1px solid rgba(212, 169, 74, 0.35);">
            <i class="ti tabler-trophy text-warning fs-2" aria-hidden="true"></i>
          </div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Achievement System
          </div>
          <h1 class="das-hero__school text-gradient-gold">Gamifikasi & Prestise</h1>
          <p class="das-hero__welcome">
            Sistem badge & leaderboard kedisiplinan siswa.
            @if(isset($tahunAkademikAktif))
              <span class="badge bg-label-warning ms-1" style="font-size:0.7rem;">TA {{ $tahunAkademikAktif->nama }} {{ $tahunAkademikAktif->semester }}</span>
            @endif
          </p>
        </div>
      </div>

      {{-- Actions & Live Clock (Kanan) --}}
      <div class="das-hero__actions d-flex align-items-center gap-3 flex-wrap flex-shrink-0 ms-md-auto">
        <button class="btn das-btn --primary d-flex align-items-center gap-2 shadow-sm" onclick="calculateLeaderboard()">
          <i class="ti tabler-refresh fs-5 me-1"></i> Hitung Ulang Skor
        </button>

        <div class="das-hero__clock" role="status" aria-live="off" style="min-width: 200px; padding: 0.65rem 1rem;">
          <div class="das-hero__date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
          <div class="das-hero__time" style="font-size: 1.3rem;">
            <span id="live-clock">00:00:00</span>
            <span class="das-hero__live-badge" style="font-size: 0.55rem;"><span class="das-hero__pulse-dot" aria-hidden="true"></span>LIVE</span>
          </div>
          <div class="das-hero__tz" style="font-size: 0.55rem;">WAKTU INDONESIA BARAT (WIB)</div>
        </div>
      </div>
    </div>
  </div>{{-- /das-hero --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 2: TOP STAT CARDS — 3 Card Highlight Statistik
       ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Card 1: Badge Tersedia --}}
    <div class="col-lg-4 col-sm-6">
      <div class="card card-grad-warning h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-warning" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                <i class="ti tabler-award fs-3"></i>
              </span>
            </div>
            <div>
              <h3 class="mb-0 fw-semibold text-warning" id="totalBadges">-</h3>
              <p class="mb-0 text-body-secondary small text-uppercase letter-spacing-1">Badge Tersedia</p>
            </div>
          </div>
          <p class="mb-0 text-body-secondary small mt-2">
            <span class="text-warning fw-medium me-1"><i class="ti tabler-sparkles me-1"></i>Sistem Achievement</span>
            <small>otomatis dievaluasi</small>
          </p>
        </div>
      </div>
    </div>

    {{-- Card 2: Siswa Berprestasi --}}
    <div class="col-lg-4 col-sm-6">
      <div class="card card-grad-success h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-success" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                <i class="ti tabler-medal fs-3"></i>
              </span>
            </div>
            <div>
              <h3 class="mb-0 fw-semibold text-success" id="studentEarned">-</h3>
              <p class="mb-0 text-body-secondary small text-uppercase letter-spacing-1">Siswa Berprestasi</p>
            </div>
          </div>
          <p class="mb-0 text-body-secondary small mt-2">
            <span class="text-success fw-medium me-1"><i class="ti tabler-circle-check me-1"></i>Telah Meraih Badge</span>
            <small>kedisiplinan tinggi</small>
          </p>
        </div>
      </div>
    </div>

    {{-- Card 3: Kelas Berpartisipasi --}}
    <div class="col-lg-4 col-sm-12">
      <div class="card card-grad-primary h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <div class="avatar me-4">
              <span class="avatar-initial rounded bg-label-primary" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                <i class="ti tabler-school fs-3"></i>
              </span>
            </div>
            <div>
              <h3 class="mb-0 fw-semibold text-primary" id="totalKelas">-</h3>
              <p class="mb-0 text-body-secondary small text-uppercase letter-spacing-1">Kelas Berpartisipasi</p>
            </div>
          </div>
          <p class="mb-0 text-body-secondary small mt-2">
            <span class="text-primary fw-medium me-1"><i class="ti tabler-trophy me-1"></i>Kompetisi Antar Kelas</span>
            <small>Tahun Ajaran Aktif</small>
          </p>
        </div>
      </div>
    </div>
  </div>{{-- /Top Stat Row --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 3: LEADERBOARD & BADGE MASTERY
       ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    {{-- Left: Papan Peringkat (Kelas & Siswa) --}}
    <div class="col-xl-8">
      <div class="card card-grad-primary h-100">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="das-panel__icon-dot das-panel__icon-dot--primary"></span>
            <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-trophy me-2 text-warning"></i>Papan Peringkat</h5>
          </div>
          <div class="d-flex align-items-center gap-2">
            <div class="nav nav-pills bg-label-secondary p-1 rounded-2" id="leaderboardTab" role="tablist" style="font-size:.78rem;">
              <button class="nav-link active px-3 py-1 fw-bold rounded-2" id="kelas-tab" data-bs-toggle="tab" data-bs-target="#kelasTab" type="button" role="tab" onclick="switchLeaderboardTab('kelas')">
                <i class="ti tabler-school me-1"></i> Kelas
              </button>
              <button class="nav-link px-3 py-1 fw-bold rounded-2" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswaTab" type="button" role="tab" onclick="switchLeaderboardTab('siswa')">
                <i class="ti tabler-users me-1"></i> Siswa
              </button>
            </div>
            {{-- Switcher Periode Tab --}}
            <div class="nav nav-pills bg-label-info p-1 rounded-2" id="periodTab" role="tablist" style="font-size:.75rem;">
              <button class="nav-link px-2.5 py-1 fw-semibold rounded-2" id="period-minggu" type="button" onclick="switchPeriodTab('minggu')">Minggu</button>
              <button class="nav-link active px-2.5 py-1 fw-semibold rounded-2" id="period-bulan" type="button" onclick="switchPeriodTab('bulan')">Bulan</button>
              <button class="nav-link px-2.5 py-1 fw-semibold rounded-2" id="period-semester" type="button" onclick="switchPeriodTab('semester')">Semester</button>
              <button class="nav-link px-2.5 py-1 fw-semibold rounded-2" id="period-tahun" type="button" onclick="switchPeriodTab('tahun')">Tahun</button>
              <button class="nav-link px-2.5 py-1 fw-semibold rounded-2" id="period-semua" type="button" onclick="switchPeriodTab('semua')">Semua</button>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="tab-content p-0">
            {{-- TAB KELAS --}}
            <div class="tab-pane fade show active" id="kelasTab" role="tabpanel">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr style="background: rgba(255, 255, 255, 0.03);">
                      <th class="text-center" style="width: 70px;">RANK</th>
                      <th>KELAS</th>
                      <th class="text-center">ABSENSI</th>
                      <th class="text-center">KEHADIRAN (%)</th>
                      <th class="text-center">PERFORMA</th>
                    </tr>
                  </thead>
                  <tbody id="leaderboardBody">
                    <tr>
                      <td colspan="5" class="text-center py-5 text-muted opacity-75">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat peringkat kelas...
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            {{-- TAB SISWA --}}
            <div class="tab-pane fade" id="siswaTab" role="tabpanel">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr style="background: rgba(255, 255, 255, 0.03);">
                      <th class="text-center" style="width: 70px;">RANK</th>
                      <th>SISWA</th>
                      <th>KELAS</th>
                      <th class="text-center">HADIR</th>
                      <th class="text-center">SKOR</th>
                      <th class="text-center">BADGE</th>
                    </tr>
                  </thead>
                  <tbody id="studentLeaderboardBody">
                    <tr>
                      <td colspan="6" class="text-center py-5 text-muted opacity-75">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat peringkat siswa...
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Right: Badge Mastery Container --}}
    <div class="col-xl-4">
      <div class="card card-grad-gold h-100">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="das-panel__icon-dot das-panel__icon-dot--warning"></span>
            <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-award me-1 text-warning"></i>Badge Mastery</h5>
          </div>
          <div class="d-flex align-items-center gap-1.5">
            <a href="/api/v1/innovation/badges/export" class="btn btn-xs btn-outline-secondary text-white fw-semibold" title="Export Badge ke File JSON"><i class="ti tabler-download me-1"></i>Export</a>
            <button class="btn btn-xs btn-outline-info fw-semibold" onclick="openImportBadgeModal()" title="Import Badge dari File JSON"><i class="ti tabler-upload me-1"></i>Import</button>
            <button class="btn btn-sm btn-label-warning d-inline-flex align-items-center gap-1 fw-semibold ms-1" onclick="openBadgeModal()" title="Tambah Badge Baru">
              <i class="ti tabler-circle-plus fs-5"></i> Baru
            </button>
          </div>
        </div>
        <div class="card-body">
          <div id="badgesContainer" class="d-flex flex-column gap-3">
            <!-- Badges will be loaded here via JS -->
            <div class="text-center py-4 text-muted small opacity-75">
              <div class="spinner-border spinner-border-sm text-warning me-1"></div> Memuat daftar badge...
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>{{-- /Leaderboard & Badge Mastery Row --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 4: PEROLEHAN BADGE TERBARU
       ═══════════════════════════════════════════════════════ --}}
  <div class="row g-6 mb-6">
    <div class="col-12">
      <div class="card card-grad-warning">
        <div class="card-header d-flex align-items-center justify-content-between pb-3">
          <div class="d-flex align-items-center gap-2">
            <span class="das-panel__icon-dot das-panel__icon-dot--warning"></span>
            <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-history me-2 text-warning"></i>Perolehan Badge Terbaru</h5>
          </div>
          <span class="badge bg-label-warning fw-semibold">Aktivitas Real-time</span>
        </div>
        <div class="table-responsive">
          <table class="das-table">
            <thead>
              <tr style="background: rgba(255, 255, 255, 0.03);">
                <th class="ps-4">SISWA</th>
                <th>KELAS</th>
                <th>BADGE</th>
                <th class="pe-4 text-end">TANGGAL PEROLEH</th>
              </tr>
            </thead>
            <tbody id="studentBadgesBody">
              <tr>
                <td colspan="4" class="text-center py-4 text-muted opacity-50">Belum ada aktivitas perolehan badge terbaru.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>{{-- /Perolehan Badge Terbaru --}}


  {{-- ═══════════════════════════════════════════════════════
       SECTION 5: REKAPITULASI (PRD-004) — ALPINE.JS COMPONENT
       ═══════════════════════════════════════════════════════ --}}
  <div x-data="{
         kelasId: '',
         periode: 'semua',
         bulan: '{{ now()->format('Y-m') }}',
         activeSubTab: 'siswa',
         loading: false,
         loaded: false,
         error: null,
         exportOpen: false,
         summary: {},
         siswadata: [],
         kelasdata: [],
         badgedata: [],
         sortSiswaCol: 'rank',
         sortSiswaDir: 'asc',
         sortKelasCol: 'rank',
         sortKelasDir: 'asc',
         expandedBadge: null,
         siswaPage: 1,
         kelasPage: 1,
         perPage: 10,

         paginatedSiswa() {
           const totalPages = this.totalSiswaPages;
           if (this.siswaPage > totalPages && totalPages > 0) {
             this.siswaPage = totalPages;
           }
           const start = (this.siswaPage - 1) * this.perPage;
           return this.siswadata.slice(start, start + this.perPage);
         },

         paginatedKelas() {
           const totalPages = this.totalKelasPages;
           if (this.kelasPage > totalPages && totalPages > 0) {
             this.kelasPage = totalPages;
           }
           const start = (this.kelasPage - 1) * this.perPage;
           return this.kelasdata.slice(start, start + this.perPage);
         },

         get totalSiswaPages() {
           return Math.ceil(this.siswadata.length / this.perPage);
         },

         get totalKelasPages() {
           return Math.ceil(this.kelasdata.length / this.perPage);
         },

         getPages(current, total) {
           if (total <= 5) {
             let pages = [];
             for (let i = 1; i <= total; i++) {
               pages.push(i);
             }
             return pages;
           }
           let pages = [];
           if (current <= 3) {
             pages = [1, 2, 3, 4, '...', total];
           } else if (current >= total - 2) {
             pages = [1, '...', total - 3, total - 2, total - 1, total];
           } else {
             pages = [1, '...', current - 1, current, current + 1, '...', total];
           }
           return pages;
         },

         async fetchRekap() {
           this.siswaPage = 1;
           this.kelasPage = 1;
           this.loading = true;
           this.error = null;
           try {
             const params = new URLSearchParams();
             if (this.kelasId) params.append('kelas_id', this.kelasId);
             params.append('periode', this.periode);
             if (this.periode === 'bulan' && this.bulan) params.append('bulan', this.bulan);
             const res = await fetch('/admin/gamifikasi/rekap?' + params.toString(), {
               headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
             });
             if (!res.ok) throw new Error('Gagal memuat data rekap (HTTP ' + res.status + ')');
             const json = await res.json();
             if (!json.success) throw new Error(json.message || 'Respons tidak valid');
             this.summary   = json.data.summary  || {};
             this.siswadata = json.data.siswa    || [];
             this.kelasdata = json.data.kelas    || [];
             this.badgedata = json.data.badge    || [];
             this.loaded = true;
           } catch (e) {
             this.error = e.message;
           } finally {
             this.loading = false;
           }
         },

         resetFilter() {
           this.kelasId   = '';
           this.periode   = 'semua';
           this.bulan     = '{{ now()->format('Y-m') }}';
           this.loaded    = false;
           this.error     = null;
           this.summary   = {};
           this.siswadata = [];
           this.kelasdata = [];
           this.badgedata = [];
           this.expandedBadge = null;
           this.siswaPage = 1;
           this.kelasPage = 1;
         },

         sortSiswa(col) {
           if (this.sortSiswaCol === col) {
             this.sortSiswaDir = this.sortSiswaDir === 'asc' ? 'desc' : 'asc';
           } else {
             this.sortSiswaCol = col;
             this.sortSiswaDir = 'asc';
           }
           this.siswadata = [...this.siswadata].sort((a, b) => {
             let av = a[col], bv = b[col];
             if (typeof av === 'string') av = av.toLowerCase();
             if (typeof bv === 'string') bv = bv.toLowerCase();
             if (av < bv) return this.sortSiswaDir === 'asc' ? -1 : 1;
             if (av > bv) return this.sortSiswaDir === 'asc' ?  1 : -1;
             return 0;
           });
         },

         sortKelas(col) {
           if (this.sortKelasCol === col) {
             this.sortKelasDir = this.sortKelasDir === 'asc' ? 'desc' : 'asc';
           } else {
             this.sortKelasCol = col;
             this.sortKelasDir = 'asc';
           }
           this.kelasdata = [...this.kelasdata].sort((a, b) => {
             let av = a[col], bv = b[col];
             if (typeof av === 'string') av = av.toLowerCase();
             if (typeof bv === 'string') bv = bv.toLowerCase();
             if (av < bv) return this.sortKelasDir === 'asc' ? -1 : 1;
             if (av > bv) return this.sortKelasDir === 'asc' ?  1 : -1;
             return 0;
           });
         },

         exportUrl(type) {
           const params = new URLSearchParams({ type });
           if (this.kelasId) params.append('kelas_id', this.kelasId);
           params.append('periode', this.periode);
           if (this.periode === 'bulan' && this.bulan) params.append('bulan', this.bulan);
           return '/admin/gamifikasi/rekap/export?' + params.toString();
         },

         sortIcon(col, active) {
           if (active !== col) return 'tabler-arrows-sort';
           return active === col && this['sort' + (active === this.sortSiswaCol ? 'Siswa' : 'Kelas') + 'Dir'] === 'asc'
             ? 'tabler-sort-ascending' : 'tabler-sort-descending';
         }
       }"
       x-init="fetchRekap()"
       class="mb-6"
  >
    {{-- Card 1: Filter Panel (Atas) --}}
    <div class="card card-grad-primary mb-6">
      <div class="card-header pb-2">
        <div class="d-flex align-items-center gap-2">
          <span class="das-panel__icon-dot das-panel__icon-dot--info"></span>
          <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-filter me-1 text-info"></i>Filter Rekapitulasi</h5>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md">
            <label class="form-label text-body-secondary small fw-bold mb-1">PERIODE</label>
            <select x-model="periode" @change="fetchRekap()" class="form-select form-select-sm">
              <option value="semua">Semua Waktu</option>
              <option value="minggu">Minggu Ini</option>
              <option value="bulan">Bulan Ini</option>
              <option value="semester">Semester Ini</option>
              <option value="tahun">Tahun Ajaran Ini</option>
            </select>
          </div>
          <div class="col-md">
            <label class="form-label text-body-secondary small fw-bold mb-1">KELAS</label>
            <select x-model="kelasId" @change="fetchRekap()" class="form-select form-select-sm">
              <option value="">Semua Kelas</option>
              @foreach($kelasList ?? [] as $kls)
                <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md" x-show="periode === 'bulan'">
            <label class="form-label text-body-secondary small fw-bold mb-1">BULAN</label>
            <input type="month"
                   x-model="bulan"
                   @change="fetchRekap()"
                   class="form-control form-control-sm"
                   style="color-scheme:dark;">
          </div>
          <div class="col-md-auto d-flex gap-2">
            <button class="btn das-btn --info d-flex align-items-center gap-1"
                    @click="fetchRekap()"
                    :disabled="loading">
              <span x-show="!loading"><i class="ti tabler-search me-1"></i> Tampilkan</span>
              <span x-show="loading" x-cloak style="display:none !important;"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memuat...</span>
            </button>
            <button class="btn das-btn --secondary d-flex align-items-center gap-1" @click="resetFilter()">
              <i class="ti tabler-rotate-clockwise me-1"></i> Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    {{-- Card 2: Tabel Rekapitulasi Panel (Bawah) --}}
    <div class="card card-grad-primary">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="das-panel__icon-dot das-panel__icon-dot--success"></span>
          <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-chart-bar me-2 text-success"></i>Laporan Rekapitulasi</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
          {{-- Export Dropdown --}}
          <div class="position-relative" x-data>
            <button class="btn das-btn --ghost d-flex align-items-center gap-1"
                    @click="exportOpen = !exportOpen"
                    @click.outside="exportOpen = false"
                    :disabled="!loaded">
              <i class="ti tabler-file-export me-1"></i>
              <span>Export CSV</span>
              <i class="ti tabler-chevron-down ms-1" style="font-size:.75rem;"></i>
            </button>
            <div x-show="exportOpen"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="position-absolute end-0 mt-1 rounded-3 shadow-lg border"
                 style="min-width:220px;z-index:50;background:var(--das-surface-2,#16213A);border-color:rgba(255,255,255,.1)!important;">
              <a :href="exportUrl('siswa')"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
                 style="transition:background .15s;"
                 @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
                 @mouseleave="$el.style.background='transparent'">
                <i class="ti tabler-users text-primary" style="font-size:.95rem;"></i> Export Rekap Siswa (.csv)
              </a>
              <a :href="exportUrl('kelas')"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
                 style="transition:background .15s;"
                 @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
                 @mouseleave="$el.style.background='transparent'">
                <i class="ti tabler-school text-info" style="font-size:.95rem;"></i> Export Rekap Kelas (.csv)
              </a>
              <a :href="exportUrl('badge')"
                 class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
                 style="transition:background .15s;"
                 @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
                 @mouseleave="$el.style.background='transparent'">
                <i class="ti tabler-award text-warning" style="font-size:.95rem;"></i> Export Rekap Badge (.csv)
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body">

        {{-- ── ERROR STATE ─────────────────────────────────────────────────── --}}
        <div x-show="error" x-cloak
             class="alert d-flex align-items-center gap-2 mb-4"
             style="background:rgba(239,90,90,.12);border:1px solid rgba(239,90,90,.3);color:#f87171;border-radius:8px;">
          <i class="ti tabler-alert-circle fs-5"></i>
          <span x-text="error"></span>
        </div>

        {{-- ── EMPTY / INITIAL STATE ───────────────────────────────────────── --}}
        <div x-show="!loaded && !loading && !error" x-cloak
             class="text-center py-5 text-muted">
          <i class="ti tabler-chart-bar-off" style="font-size:2.8rem;opacity:.3;"></i>
          <p class="mt-2 small opacity-75">Klik <strong>Tampilkan</strong> untuk memuat data rekapitulasi.</p>
        </div>

        {{-- ── LOADING STATE ───────────────────────────────────────────────── --}}
        <div x-show="loading" x-cloak class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <p class="small opacity-75">Memuat data rekapitulasi...</p>
        </div>

        {{-- ── DATA LOADED ─────────────────────────────────────────────────── --}}
        <div x-show="loaded && !loading" x-cloak>

          {{-- ── SUMMARY CARDS ROW ─────────────────────────────────────────── --}}
          <div class="row g-4 mb-5">
            <div class="col-6 col-md-3">
              <div class="card card-grad-success h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="avatar-initial rounded bg-label-success p-1" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                      <i class="ti tabler-user-check fs-5"></i>
                    </span>
                    <small class="text-body-secondary text-uppercase" style="font-size:.65rem;letter-spacing:.5px;">Total Kehadiran</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-success" x-text="summary.total_kehadiran ?? 0"></h4>
                  <small class="text-body-secondary" style="font-size:.68rem;">Hadir & Terlambat</small>
                </div>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card card-grad-primary h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="avatar-initial rounded bg-label-primary p-1" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                      <i class="ti tabler-clock fs-5"></i>
                    </span>
                    <small class="text-body-secondary text-uppercase" style="font-size:.65rem;letter-spacing:.5px;">Rata-rata Terawal</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-primary" x-text="summary.avg_jam_terawal ?? '-'"></h4>
                  <small class="text-body-secondary" style="font-size:.68rem;">Jam Masuk Presensi</small>
                </div>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card card-grad-info h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="avatar-initial rounded bg-label-info p-1" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                      <i class="ti tabler-flame fs-5"></i>
                    </span>
                    <small class="text-body-secondary text-uppercase" style="font-size:.65rem;letter-spacing:.5px;">Konsistensi</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-info" x-text="(summary.tingkat_konsistensi ?? 0) + '%'"></h4>
                  <small class="text-body-secondary" style="font-size:.68rem;">Avg Streak: <span class="fw-bold" x-text="summary.avg_streak ?? 0"></span> hr</small>
                </div>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="card card-grad-warning h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="avatar-initial rounded bg-label-warning p-1" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;">
                      <i class="ti tabler-award fs-5"></i>
                    </span>
                    <small class="text-body-secondary text-uppercase" style="font-size:.65rem;letter-spacing:.5px;">Total Badge</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-warning" x-text="summary.total_badge_diraih ?? 0"></h4>
                  <small class="text-body-secondary" style="font-size:.68rem;">Badge Berhasil Diraih</small>
                </div>
              </div>
            </div>
          </div>

          {{-- ── SUB-TABS NAVIGATION ────────────────────────────────────────── --}}
          <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom" style="border-color: rgba(255,255,255,.08) !important;">
            <button class="btn das-btn"
                    :class="activeSubTab === 'siswa' ? '--primary' : '--secondary'"
                    @click="activeSubTab = 'siswa'">
              <i class="ti tabler-users me-1"></i> Rekap Siswa
            </button>
            <button class="btn das-btn"
                    :class="activeSubTab === 'kelas' ? '--primary' : '--secondary'"
                    @click="activeSubTab = 'kelas'">
              <i class="ti tabler-school me-1"></i> Rekap Kelas
            </button>
            <button class="btn das-btn"
                    :class="activeSubTab === 'badge' ? '--primary' : '--secondary'"
                    @click="activeSubTab = 'badge'">
              <i class="ti tabler-award me-1"></i> Rekap Badge
            </button>
          </div>

          {{-- ── SUB-TAB: REKAP SISWA ─────────────────────────────────────── --}}
          <div x-show="activeSubTab === 'siswa'" x-cloak>
            <div x-show="siswadata.length === 0" class="text-center py-5 text-muted opacity-50 small">
              Tidak ada data siswa untuk filter ini.
            </div>
            <div x-show="siswadata.length > 0">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr style="background: rgba(255,255,255,0.03);">
                      <th class="text-center" style="cursor:pointer;" @click="sortSiswa('rank')">
                        RANK <i class="ti" :class="sortIcon('rank', sortSiswaCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th style="cursor:pointer;" @click="sortSiswa('nama_lengkap')">
                        NAMA SISWA <i class="ti" :class="sortIcon('nama_lengkap', sortSiswaCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th>KELAS</th>
                      <th class="text-center" style="cursor:pointer;" @click="sortSiswa('total_hadir')">
                        HADIR <i class="ti" :class="sortIcon('total_hadir', sortSiswaCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th class="text-center" style="cursor:pointer;" @click="sortSiswa('total_alpha')">
                        ALPHA <i class="ti" :class="sortIcon('total_alpha', sortSiswaCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th class="text-center" style="cursor:pointer;" @click="sortSiswa('skor')">
                        SKOR <i class="ti" :class="sortIcon('skor', sortSiswaCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th class="text-center">BADGE</th>
                      <th class="text-center">AKSI</th>
                    </tr>
                  </thead>
                  <tbody>
                    <template x-for="(item, idx) in paginatedSiswa()" :key="idx">
                      <tr class="rekap-row-hover">
                        <td class="text-center fw-bold fs-5">
                          <span x-text="item.rank === 1 ? '🥇' : item.rank === 2 ? '🥈' : item.rank === 3 ? '🥉' : item.rank"></span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="fw-bold text-white" style="font-size:.84rem;" x-text="item.nama_lengkap || '-'"></span>
                            <template x-if="item.is_teladan || (item.total_alpha === 0 && item.total_hadir > 0)">
                              <span class="badge bg-label-success" style="font-size:.62rem;padding:2px 6px;" title="Siswa Teladan (0 Alpha)">⭐ Teladan</span>
                            </template>
                          </div>
                          <div class="text-muted font-monospace" style="font-size:.68rem;" x-text="'NIS: ' + (item.nis || '-')"></div>
                        </td>
                        <td>
                          <span class="badge bg-label-secondary" style="font-size:.68rem;" x-text="item.kelas?.nama || '-'"></span>
                        </td>
                        <td class="text-center fw-semibold text-success" x-text="item.total_hadir ?? 0"></td>
                        <td class="text-center fw-semibold text-danger" x-text="item.total_alpha ?? 0"></td>
                        <td class="text-center">
                          <span class="fw-bold text-warning" x-text="item.skor ?? 0"></span>
                        </td>
                        <td class="text-center">
                          <div class="d-flex align-items-center justify-content-center gap-1" style="min-width:80px;">
                            <template x-if="item.badge_list && item.badge_list.length > 0">
                              <div class="d-flex align-items-center gap-1">
                                <template x-for="(b, bi) in item.badge_list.slice(0,3)" :key="bi">
                                  <span class="das-stat-card__icon"
                                        :title="b.name"
                                        style="width:24px;height:24px;background:rgba(255,215,0,.15);color:#ffd700;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;">
                                    <i :class="'ti ' + (b.icon || 'tabler-award')"></i>
                                  </span>
                                </template>
                                <span x-show="item.badge_list.length > 3"
                                      class="text-muted"
                                      style="font-size:.68rem;"
                                      x-text="'+' + (item.badge_list.length - 3)"></span>
                              </div>
                            </template>
                            <template x-if="!item.badge_list || item.badge_list.length === 0">
                              <span class="text-muted" style="font-size:.68rem;">-</span>
                            </template>
                          </div>
                        </td>
                        <td class="text-center">
                          <a :href="'/admin/siswa/' + (item.siswa_id || '') + '/profil'"
                             class="action-btn"
                             title="Lihat Profil Siswa">
                            <i class="ti tabler-eye" style="font-size:.9rem;"></i>
                          </a>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              {{-- PAGINATION SISWA --}}
              <div class="px-3 py-3 border-top mt-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <nav aria-label="Navigasi Halaman" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                  <div class="text-muted small font-monospace" style="font-size: 0.78rem;">
                    Menampilkan <span class="text-white fw-semibold" x-text="((siswaPage - 1) * perPage) + 1"></span>–<span class="text-white fw-semibold" x-text="Math.min(siswaPage * perPage, siswadata.length)"></span> dari <span class="text-white fw-semibold" x-text="siswadata.length"></span> data
                  </div>
                  <ul class="pagination pagination-sm mb-0 gap-1" style="list-style:none; display:flex; align-items:center; flex-wrap:wrap;">
                    <li class="page-item" :class="siswaPage === 1 ? 'disabled' : ''">
                      <button class="das-page-btn" :disabled="siswaPage === 1" @click="siswaPage = Math.max(1, siswaPage - 1)" aria-label="Sebelumnya">
                        <i class="ti tabler-chevron-left"></i>
                      </button>
                    </li>
                    <template x-for="(page, pIdx) in getPages(siswaPage, totalSiswaPages)" :key="pIdx">
                      <li class="page-item" :class="page === siswaPage ? 'active' : (page === '...' ? 'disabled' : '')">
                        <template x-if="page === '...'">
                          <span class="das-page-btn das-page-dots">...</span>
                        </template>
                        <template x-if="page !== '...'">
                          <button class="das-page-btn" :class="page === siswaPage ? 'das-page-active' : ''" @click="siswaPage = page" x-text="page"></button>
                        </template>
                      </li>
                    </template>
                    <li class="page-item" :class="siswaPage === totalSiswaPages ? 'disabled' : ''">
                      <button class="das-page-btn" :disabled="siswaPage === totalSiswaPages" @click="siswaPage = Math.min(totalSiswaPages, siswaPage + 1)" aria-label="Selanjutnya">
                        <i class="ti tabler-chevron-right"></i>
                      </button>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>

          {{-- ── SUB-TAB: REKAP KELAS ──────────────────────────────────────── --}}
          <div x-show="activeSubTab === 'kelas'" x-cloak>
            <div x-show="kelasdata.length === 0" class="text-center py-5 text-muted opacity-50 small">
              Tidak ada data kelas untuk filter ini.
            </div>
            <div x-show="kelasdata.length > 0">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr style="background: rgba(255,255,255,0.03);">
                      <th class="text-center" style="cursor:pointer;" @click="sortKelas('rank')">
                        RANK <i class="ti" :class="sortIcon('rank', sortKelasCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th style="cursor:pointer;" @click="sortKelas('nama')">
                        KELAS <i class="ti" :class="sortIcon('nama', sortKelasCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th>JURUSAN</th>
                      <th class="text-center" style="cursor:pointer;" @click="sortKelas('total_siswa')">
                        TOTAL SISWA <i class="ti" :class="sortIcon('total_siswa', sortKelasCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th class="text-center" style="cursor:pointer;" @click="sortKelas('percentage')">
                        % KEHADIRAN <i class="ti" :class="sortIcon('percentage', sortKelasCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                      <th class="text-center" style="cursor:pointer;" @click="sortKelas('jumlah_badge_diraih')">
                        BADGE DIRAIH <i class="ti" :class="sortIcon('jumlah_badge_diraih', sortKelasCol)" style="font-size:.75rem;opacity:.6;"></i>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <template x-for="(item, idx) in paginatedKelas()" :key="idx">
                      <tr class="rekap-row-hover">
                        <td class="text-center fw-bold fs-5">
                          <span x-text="item.rank === 1 ? '🏆' : item.rank === 2 ? '🥈' : item.rank === 3 ? '🥉' : item.rank"></span>
                        </td>
                        <td>
                          <div class="fw-bold text-white" style="font-size:.85rem;" x-text="item.nama || '-'"></div>
                        </td>
                        <td>
                          <span class="badge bg-label-info" style="font-size:.68rem;" x-text="(typeof item.jurusan === 'object' ? (item.jurusan?.nama || item.jurusan?.nama_jurusan) : item.jurusan) || '-'"></span>
                        </td>
                        <td class="text-center" x-text="item.total_siswa ?? 0"></td>
                        <td class="text-center">
                          <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="progress w-px-75" style="height:6px;background:rgba(255,255,255,.06);">
                              <div class="progress-bar"
                                   :class="parseFloat(item.percentage) > 85 ? 'bg-success' : (parseFloat(item.percentage) > 70 ? 'bg-warning' : 'bg-danger')"
                                   :style="'width:' + (item.percentage || 0) + '%'"></div>
                            </div>
                            <span class="fw-bold" x-text="parseFloat(item.percentage || 0).toFixed(1) + '%'"></span>
                          </div>
                        </td>
                        <td class="text-center">
                          <span class="das-chip das-chip--warning" x-text="item.jumlah_badge_diraih ?? 0"></span>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              {{-- PAGINATION KELAS --}}
              <div class="px-3 py-3 border-top mt-2" style="border-color: rgba(255,255,255,0.08) !important;">
                <nav aria-label="Navigasi Halaman" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                  <div class="text-muted small font-monospace" style="font-size: 0.78rem;">
                    Menampilkan <span class="text-white fw-semibold" x-text="((kelasPage - 1) * perPage) + 1"></span>–<span class="text-white fw-semibold" x-text="Math.min(kelasPage * perPage, kelasdata.length)"></span> dari <span class="text-white fw-semibold" x-text="kelasdata.length"></span> data
                  </div>
                  <ul class="pagination pagination-sm mb-0 gap-1" style="list-style:none; display:flex; align-items:center; flex-wrap:wrap;">
                    <li class="page-item" :class="kelasPage === 1 ? 'disabled' : ''">
                      <button class="das-page-btn" :disabled="kelasPage === 1" @click="kelasPage = Math.max(1, kelasPage - 1)" aria-label="Sebelumnya">
                        <i class="ti tabler-chevron-left"></i>
                      </button>
                    </li>
                    <template x-for="(page, pIdx) in getPages(kelasPage, totalKelasPages)" :key="pIdx">
                      <li class="page-item" :class="page === kelasPage ? 'active' : (page === '...' ? 'disabled' : '')">
                        <template x-if="page === '...'">
                          <span class="das-page-btn das-page-dots">...</span>
                        </template>
                        <template x-if="page !== '...'">
                          <button class="das-page-btn" :class="page === kelasPage ? 'das-page-active' : ''" @click="kelasPage = page" x-text="page"></button>
                        </template>
                      </li>
                    </template>
                    <li class="page-item" :class="kelasPage === totalKelasPages ? 'disabled' : ''">
                      <button class="das-page-btn" :disabled="kelasPage === totalKelasPages" @click="kelasPage = Math.min(totalKelasPages, kelasPage + 1)" aria-label="Selanjutnya">
                        <i class="ti tabler-chevron-right"></i>
                      </button>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>

          {{-- ── SUB-TAB: REKAP BADGE ──────────────────────────────────────── --}}
          <div x-show="activeSubTab === 'badge'" x-cloak>
            <div x-show="badgedata.length === 0" class="text-center py-5 text-muted opacity-50 small">
              Tidak ada data badge untuk filter ini.
            </div>
            <div class="d-flex flex-column gap-3">
              <template x-for="(badge, bi) in badgedata" :key="bi">
                <div class="rounded-3" style="border:1px solid rgba(255,255,255,.08);overflow:hidden;background:rgba(255,255,255,.02);">
                  {{-- Accordion Header --}}
                  <div class="d-flex align-items-center gap-3 p-3"
                       style="cursor:pointer;"
                       @click="expandedBadge = expandedBadge === bi ? null : bi">
                    <div class="das-stat-card__icon"
                         style="width:40px;height:40px;background:rgba(255,215,0,.15);color:#ffd700;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                      <i :class="'ti ' + (badge.icon || 'tabler-award')" style="font-size:1.1rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-bold text-white small" x-text="badge.name || '-'"></div>
                      <div class="text-muted" style="font-size:.72rem;" x-text="badge.description || ''"></div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <span class="das-chip das-chip--success" style="font-size:.68rem;"
                            x-text="(badge.total_penerima ?? 0) + ' penerima'"></span>
                      <i class="ti ms-1"
                         :class="expandedBadge === bi ? 'tabler-chevron-up' : 'tabler-chevron-down'"
                         style="font-size:.9rem;color:#8B96AB;"></i>
                    </div>
                  </div>
                  {{-- Accordion Body --}}
                  <div x-show="expandedBadge === bi"
                       x-transition:enter="transition ease-out duration-150"
                       x-transition:enter-start="opacity-0 -translate-y-1"
                       x-transition:enter-end="opacity-100 translate-y-0"
                       style="border-top:1px solid rgba(255,255,255,.05);">
                    <div x-show="!badge.penerima || badge.penerima.length === 0"
                         class="text-center py-4 text-muted opacity-50 small">
                      Belum ada penerima badge ini pada periode terpilih.
                    </div>
                    <div x-show="badge.penerima && badge.penerima.length > 0" class="table-responsive">
                      <table class="das-table" style="font-size:.78rem;">
                        <thead>
                          <tr style="background: rgba(255,255,255,0.03);">
                            <th class="ps-4">NAMA SISWA</th>
                            <th>KELAS</th>
                            <th class="pe-4 text-end">TANGGAL DIRAIH</th>
                          </tr>
                        </thead>
                        <tbody>
                           <template x-for="(penerima, pi) in badge.penerima" :key="pi">
                            <tr class="rekap-row-hover">
                              <td class="ps-4 fw-semibold text-white" x-text="penerima.nama || '-'"></td>
                              <td>
                                <span class="badge bg-label-secondary" style="font-size:.68rem;"
                                      x-text="penerima.kelas?.nama || '-'"></span>
                              </td>
                              <td class="pe-4 text-end text-muted font-monospace"
                                  x-text="penerima.earned_at ? new Date(penerima.earned_at).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'}) : '-'">
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

        </div>{{-- end x-show loaded --}}
      </div>{{-- end card-body --}}
    </div>{{-- end card Rekapitulasi --}}
  </div>{{-- end Alpine wrapper --}}


  {{-- ═══════════════════════════════════════════════════════
       MODAL: KONFIGURASI BADGE BARU
       ═══════════════════════════════════════════════════════ --}}
  <div class="modal fade" id="badgeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content das-modal">
        <div class="das-modal__head">
          <h5 class="das-modal__title"><i class="ti tabler-award me-2 text-warning"></i>Konfigurasi Badge Baru</h5>
          <button type="button" class="das-modal__close" data-bs-dismiss="modal" aria-label="Tutup"><i class="ti tabler-x"></i></button>
        </div>
        <div class="das-modal__body p-4">
          <form id="badgeForm">
            <div class="row gy-3">
              <div class="col-12">
                <label class="form-label text-body-secondary small fw-bold mb-1">NAMA BADGE</label>
                <input type="text" id="badgeName" class="form-control" placeholder="Contoh: Sang Juara Absensi">
              </div>
              <div class="col-md-6">
                <label class="form-label text-body-secondary small fw-bold mb-1">ICON (TABLER)</label>
                <input type="text" id="badgeIcon" class="form-control" placeholder="tabler-star">
              </div>
              <div class="col-md-6">
                <label class="form-label text-body-secondary small fw-bold mb-1">TIPE BADGE</label>
                <select id="badgeType" class="form-select">
                  <option value="individual">Individual (Siswa)</option>
                  <option value="class">Kelas</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label text-body-secondary small fw-bold mb-1">DESKRIPSI</label>
                <textarea id="badgeDescription" class="form-control" rows="2" placeholder="Jelaskan kriteria pencapaian badge ini..."></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label text-body-secondary small fw-bold mb-1">JUMLAH HARI</label>
                <input type="number" id="badgeRequirement" class="form-control" value="30">
              </div>
              <div class="col-md-6">
                <label class="form-label text-body-secondary small fw-bold mb-1">SYARAT PRESENSI</label>
                <select id="badgeRequirementType" class="form-select">
                  <option value="consecutive">Beruntun</option>
                  <option value="total">Total Akumulasi</option>
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="das-modal__foot d-flex gap-2">
          <button type="button" class="btn das-btn --secondary w-100" data-bs-dismiss="modal">Batal</button>
          <button type="button" class="btn das-btn --primary w-100" onclick="saveBadge()">Simpan Achievement</button>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL IMPORT BADGE --}}
  <div class="modal fade" id="importBadgeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content das-modal">
        <div class="das-modal__head">
          <h5 class="das-modal__title"><i class="ti tabler-file-upload me-2 text-info"></i>Import Badge (JSON)</h5>
          <button type="button" class="das-modal__close" data-bs-dismiss="modal" aria-label="Tutup"><i class="ti tabler-x"></i></button>
        </div>
        <form id="importBadgeForm" onsubmit="submitImportBadge(event)">
          <div class="das-modal__body p-4">
            <div class="mb-3">
              <label class="form-label text-body-secondary small fw-bold mb-1">FILE JSON BADGE</label>
              <input type="file" id="badgeJsonFile" class="form-control" accept=".json,application/json" required>
            </div>
            <div class="small text-muted" style="font-size:.72rem;">
              <i class="ti tabler-info-circle me-1"></i> Pilih file JSON hasil Export Badge. Badge dengan nama sama akan otomatis diperbarui.
            </div>
          </div>
          <div class="das-modal__foot d-flex gap-2">
            <button type="button" class="btn das-btn --secondary w-100" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn das-btn --primary w-100" id="btnSubmitImport"><i class="ti tabler-upload me-1"></i> Import JSON</button>
          </div>
        </form>
      </div>
    </div>
  </div>
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

    let currentLeaderboardPeriod = 'bulan';

    document.addEventListener('DOMContentLoaded', function() {
      loadLeaderboard(currentLeaderboardPeriod);
      loadStudentLeaderboard(currentLeaderboardPeriod);
      loadBadges();
      loadStudentBadges();
      // Auto-refresh aktivitas perolehan badge realtime setiap 30 detik
      setInterval(loadStudentBadges, 30000);
    });

    function switchPeriodTab(periode) {
      currentLeaderboardPeriod = periode;
      document.querySelectorAll('#periodTab .nav-link').forEach(btn => {
        btn.classList.remove('active');
      });
      const activeBtn = document.getElementById('period-' + periode);
      if (activeBtn) {
        activeBtn.classList.add('active');
      }
      loadLeaderboard(currentLeaderboardPeriod);
      loadStudentLeaderboard(currentLeaderboardPeriod);
    }

    async function loadLeaderboard(periode = 'bulan') {
      const tbody = document.getElementById('leaderboardBody');
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted opacity-75"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat peringkat kelas...</td></tr>`;
      }
      try {
        const response = await fetch('/api/v1/innovation/leaderboard?limit=10&periode=' + periode);
        const result = await response.json();

        const data = result.data || [];

        // Gunakan total_kelas dari API
        const totalKelasEl = document.getElementById('totalKelas');
        if (totalKelasEl) totalKelasEl.textContent = result.total_kelas ?? data.length;

        if (data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted opacity-50">Belum ada data peringkat. Klik "Hitung Ulang Skor" untuk memulai.</td></tr>`;
          return;
        }

        tbody.innerHTML = data.map((item, index) => {
          const percentage = parseFloat(item.percentage || 0);
          let chipClass = 'das-chip--success';
          let statusText = 'Sangat Baik';

          if (percentage < 70) { chipClass = 'das-chip--danger'; statusText = 'Buruk'; }
          else if (percentage < 85) { chipClass = 'das-chip--warning'; statusText = 'Cukup'; }

          const rankBadge = index === 0 ? '🏆' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);

          const jurusanName = typeof item.kelas?.jurusan === 'object'
            ? (item.kelas?.jurusan?.nama || item.kelas?.jurusan?.nama_jurusan || 'Semua Jurusan')
            : (item.kelas?.jurusan || 'Semua Jurusan');

          return `
            <tr class="rekap-row-hover">
              <td class="text-center fw-bold fs-5">${rankBadge}</td>
              <td>
                <div class="fw-bold text-white">${item.kelas?.nama || '-'}</div>
                <div class="small text-muted" style="font-size:.7rem;">${jurusanName}</div>
              </td>
              <td class="text-center font-monospace">${item.total_present} / ${item.total_attendance}</td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <div class="progress w-px-75" style="height: 6px; background: rgba(255,255,255,0.06);">
                    <div class="progress-bar ${percentage > 85 ? 'bg-success' : (percentage > 70 ? 'bg-warning' : 'bg-danger')}" style="width: ${percentage}%"></div>
                  </div>
                  <span class="fw-bold">${percentage.toFixed(1)}%</span>
                </div>
              </td>
              <td class="text-center"><span class="das-chip ${chipClass}">${statusText}</span></td>
            </tr>
          `;
        }).join('');

      } catch (e) {
        console.error('Error loading leaderboard:', e);
      }
    }

    async function loadStudentLeaderboard(periode = 'bulan') {
      const tbody = document.getElementById('studentLeaderboardBody');
      if (tbody) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted opacity-75"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat peringkat siswa...</td></tr>`;
      }
      try {
        const response = await fetch('/api/v1/innovation/leaderboard/students?limit=10&periode=' + periode);
        const result = await response.json();

        const data = result.data || [];

        if (data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted opacity-50">Belum ada data peringkat siswa. Klik "Hitung Ulang Skor" untuk memulai.</td></tr>`;
          return;
        }

        tbody.innerHTML = data.map((item, index) => {
          const rankBadge = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);
          const badges = item.siswa?.student_badges || [];
          const badgeIcons = badges.slice(0, 3).map(b => 
            `<span class="das-stat-card__icon" style="width:22px;height:22px;background:rgba(255,215,0,0.15);color:#ffd700;border-radius:4px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;margin:0 1px;"><i class="ti ${b.badge?.icon || 'tabler-award'}"></i></span>`
          ).join('');

          return `
            <tr class="rekap-row-hover">
              <td class="text-center fw-bold fs-5">${rankBadge}</td>
              <td>
                <div class="fw-bold text-white" style="font-size:.82rem;">${item.siswa?.nama_lengkap || '-'}</div>
                <div class="text-muted font-monospace" style="font-size:.65rem;">NIS: ${item.siswa?.nis || '-'}</div>
              </td>
              <td>
                <span class="badge bg-label-secondary" style="font-size:.65rem;">${item.siswa?.kelas?.nama || '-'}</span>
              </td>
              <td class="text-center fw-semibold text-success">${item.total_present}/${item.total_attendance}</td>
              <td class="text-center">
                <span class="fw-bold text-warning" style="font-size:.95rem;">${item.score}</span>
              </td>
              <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1" style="min-width:70px;">
                  ${badgeIcons || '<span class="text-muted" style="font-size:.65rem;">-</span>'}
                </div>
              </td>
            </tr>
          `;
        }).join('');

      } catch (e) {
        console.error('Error loading student leaderboard:', e);
      }
    }

    function switchLeaderboardTab(tab) {
      document.querySelectorAll('#leaderboardTab .nav-link').forEach(btn => {
        btn.classList.remove('active');
      });
      const activeBtn = document.getElementById(tab + '-tab');
      if (activeBtn) {
        activeBtn.classList.add('active');
      }
      if (tab === 'kelas') {
        loadLeaderboard(currentLeaderboardPeriod);
      } else if (tab === 'siswa') {
        loadStudentLeaderboard(currentLeaderboardPeriod);
      }
    }

    async function calculateLeaderboard() {
      const btn = event.currentTarget;
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader-2 spin me-1"></i> Menghitung...';

      try {
        const response = await fetch('/api/v1/innovation/leaderboard/calculate', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });
        const result = await response.json();
        if (result.success) {
          await loadLeaderboard(currentLeaderboardPeriod);
          await loadStudentLeaderboard(currentLeaderboardPeriod);
          await loadStudentBadges();
        }
      } catch (e) {
        console.error('Error:', e);
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    }

    async function loadBadges() {
      try {
        const response = await fetch('/api/v1/innovation/badges');
        const result = await response.json();

        const container = document.getElementById('badgesContainer');
        const data = result.data || [];

        const totalBadgesEl = document.getElementById('totalBadges');
        if (totalBadgesEl) totalBadgesEl.textContent = data.length;

        if (data.length === 0) {
          container.innerHTML = `<div class="text-center py-4 text-muted">Belum ada badge.</div>`;
          return;
        }

        container.innerHTML = data.map(badge => `
          <div class="d-flex align-items-center gap-3 p-2.5 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); transition: background 0.15s ease;">
            <div class="das-stat-card__icon" style="width: 40px; height: 40px; background: rgba(255, 215, 0, 0.15); color: #ffd700; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <i class="ti ${badge.icon} fs-4"></i>
            </div>
            <div class="flex-grow-1 overflow-hidden">
              <div class="d-flex align-items-center justify-content-between gap-1">
                <span class="fw-bold text-white small text-truncate">${badge.name}</span>
                <span class="badge bg-label-primary" style="font-size: 0.6rem; padding: 2px 6px;">${badge.badge_type}</span>
              </div>
              <div class="text-muted mt-0.5" style="font-size: 0.7rem;">${badge.requirement_days} hari ${badge.requirement_type}</div>
            </div>
          </div>
        `).join('');

      } catch (e) {
        console.error('Error:', e);
      }
    }

    async function loadStudentBadges() {
      try {
        const response = await fetch('/api/v1/innovation/badges/history');
        const result = await response.json();

        const tbody = document.getElementById('studentBadgesBody');
        const data = result.data || [];

        const studentEarnedEl = document.getElementById('studentEarned');
        if (studentEarnedEl) studentEarnedEl.textContent = result.total_earned_students || '0';

        if (data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted opacity-50">Belum ada aktivitas perolehan badge terbaru.</td></tr>`;
          return;
        }

        tbody.innerHTML = data.map(item => {
          const earnedAt = item.earned_at ? new Date(item.earned_at).toLocaleDateString('id-ID', {
            day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
          }) : '-';

          return `
            <tr class="rekap-row-hover">
              <td class="ps-4">
                <div class="fw-bold text-white small">${item.siswa?.nama_lengkap || '-'}</div>
                <div class="text-muted font-monospace" style="font-size: 0.68rem;">NISN: ${item.siswa?.nisn || '-'}</div>
              </td>
              <td>
                <span class="badge bg-label-secondary">${item.siswa?.kelas?.nama || '-'}</span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="das-stat-card__icon" style="width: 30px; height: 30px; background: rgba(255, 215, 0, 0.15); color: #ffd700; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.95rem;">
                    <i class="ti ${item.badge?.icon || 'tabler-award'}"></i>
                  </span>
                  <div>
                    <div class="fw-bold text-white" style="font-size: 0.8rem;">${item.badge?.name || '-'}</div>
                    <div class="text-muted" style="font-size: 0.68rem;">${item.badge?.description || '-'}</div>
                  </div>
                </div>
              </td>
              <td class="pe-4 text-end text-muted font-monospace small">${earnedAt}</td>
            </tr>
          `;
        }).join('');

      } catch (e) {
        console.error('Error loading student badges:', e);
      }
    }

    function openBadgeModal() {
      const modal = new bootstrap.Modal(document.getElementById('badgeModal'));
      modal.show();
    }

    async function saveBadge() {
      const btn = event.currentTarget;
      btn.disabled = true;

      const data = {
        name: document.getElementById('badgeName').value,
        icon: document.getElementById('badgeIcon').value,
        description: document.getElementById('badgeDescription').value,
        badge_type: document.getElementById('badgeType').value,
        requirement_days: document.getElementById('badgeRequirement').value,
        requirement_type: document.getElementById('badgeRequirementType').value
      };

      try {
        const response = await fetch('/api/v1/innovation/badges', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(data)
        });

        if (response.ok) {
          await loadBadges();
          bootstrap.Modal.getInstance(document.getElementById('badgeModal')).hide();
          document.getElementById('badgeForm').reset();
        }
      } catch (e) {
        console.error('Error:', e);
      } finally {
        btn.disabled = false;
      }
    }

    function openImportBadgeModal() {
      document.getElementById('importBadgeForm').reset();
      const modal = new bootstrap.Modal(document.getElementById('importBadgeModal'));
      modal.show();
    }

    async function submitImportBadge(e) {
      e.preventDefault();
      const fileInput = document.getElementById('badgeJsonFile');
      if (!fileInput.files || fileInput.files.length === 0) return;

      const btn = document.getElementById('btnSubmitImport');
      const originalHtml = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="ti tabler-loader-2 spin me-1"></i> Memproses...';

      const formData = new FormData();
      formData.append('file', fileInput.files[0]);

      try {
        const response = await fetch('/api/v1/innovation/badges/import', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        });

        const result = await response.json();
        if (result.success) {
          const modalEl = document.getElementById('importBadgeModal');
          const modalInstance = bootstrap.Modal.getInstance(modalEl);
          if (modalInstance) modalInstance.hide();

          alert(result.message || 'Berhasil mengimpor badge!');
          await loadBadges();
        } else {
          alert(result.message || 'Gagal mengimpor badge.');
        }
      } catch (err) {
        console.error('Import error:', err);
        alert('Terjadi kesalahan saat mengunggah file JSON badge.');
      } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
      }
    }
  </script>
@endsection