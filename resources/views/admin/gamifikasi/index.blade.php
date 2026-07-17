@extends('layouts/layoutMaster')

@section('title', 'Gamifikasi Presensi')

@section('page-style')
<style>
  .rekap-row-hover {
      transition: background 0.15s ease;
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
      border-radius: 8px;
      transition: all 0.2s ease;
      border: none;
      background: rgba(255, 255, 255, 0.05);
      color: inherit;
  }
  .action-btn:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
  }
  .form-control,
  .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
      border-radius: 5px !important;
  }
  .form-control:focus,
  .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
      box-shadow: none;
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
      border-radius: 5px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      background: transparent;
      color: #888;
      text-decoration: none;
      transition: all 0.18s ease;
      cursor: pointer;
      line-height: 1;
      font-family: inherit;
  }
  .das-page-btn:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.12);
  }
  .das-page-btn:disabled {
      opacity: 0.35;
      cursor: not-allowed;
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
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 32px;
      height: 32px;
  }
</style>
@endsection

@section('content')
<div class="das-hero mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <div class="das-hero__logo-placeholder">
          <i class="ti tabler-trophy"></i>
        </div>
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Achievement System
        </div>
        <h4 class="das-hero__title text-gradient-gold">Gamifikasi & Prestise</h4>
        <p class="das-hero__subtitle">Tingkatkan kedisiplinan siswa melalui sistem badge dan leaderboard kelas.</p>
      </div>
    </div>
    <div class="das-hero__actions">
      <button class="das-btn das-btn--primary shadow-lg" onclick="calculateLeaderboard()">
        <i class="ti tabler-refresh"></i> Hitung Ulang Skor
      </button>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--warning">
      <div class="das-stat-card__icon"><i class="ti tabler-award"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="totalBadges">-</div>
        <div class="das-stat-card__label">Badge Tersedia</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--success">
      <div class="das-stat-card__icon"><i class="ti tabler-medal"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="studentEarned">-</div>
        <div class="das-stat-card__label">Siswa Berprestasi</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="das-stat-card das-stat-card--primary">
      <div class="das-stat-card__icon"><i class="ti tabler-school"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val" id="totalKelas">-</div>
        <div class="das-stat-card__label">Kelas Berpartisipasi</div>
      </div>
      <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-8">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Papan Peringkat
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="nav nav-tabs border-0" id="leaderboardTab" role="tablist" style="font-size:.75rem;">
            <button class="nav-link active px-3 py-1 fw-bold" id="kelas-tab" data-bs-toggle="tab" data-bs-target="#kelasTab" type="button" role="tab" style="color:#999;border:none;background:transparent;" onclick="switchLeaderboardTab('kelas')">Kelas</button>
            <button class="nav-link px-3 py-1 fw-bold" id="siswa-tab" data-bs-toggle="tab" data-bs-target="#siswaTab" type="button" role="tab" style="color:#999;border:none;background:transparent;" onclick="switchLeaderboardTab('siswa')">Siswa</button>
          </div>
          <span class="das-chip --info" id="leaderboardPeriod">Bulan Ini</span>
        </div>
      </div>
      <div class="tab-content">
        {{-- TAB KELAS --}}
        <div class="tab-pane fade show active" id="kelasTab" role="tabpanel">
          <div class="table-responsive">
            <table class="das-table">
              <thead>
                <tr>
                  <th class="text-center">RANK</th>
                  <th>KELAS</th>
                  <th class="text-center">ABSENSI</th>
                  <th class="text-center">KEHADIRAN (%)</th>
                  <th class="text-center">PERFORMA</th>
                </tr>
              </thead>
              <tbody id="leaderboardBody">
                <tr>
                  <td colspan="5" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat peringkat...
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
                <tr>
                  <th class="text-center">RANK</th>
                  <th>SISWA</th>
                  <th>KELAS</th>
                  <th class="text-center">HADIR</th>
                  <th class="text-center">SKOR</th>
                  <th class="text-center">BADGE</th>
                </tr>
              </thead>
              <tbody id="studentLeaderboardBody">
                <tr>
                  <td colspan="6" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat peringkat siswa...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-xl-4">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --warning"></span>
          Badge Mastery
        </div>
        <button class="das-btn das-btn--ghost-sm text-primary p-0" onclick="openBadgeModal()">
          <i class="ti tabler-circle-plus fs-4"></i>
        </button>
      </div>
      <div class="das-panel__body">
        <div id="badgesContainer" class="d-flex flex-column gap-3">
          <!-- Badges will be loaded here -->
           <div class="text-center py-4 text-muted small">Memuat daftar badge...</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="das-panel">
  <div class="das-panel__head">
    <div class="das-panel__title">
      <span class="das-panel__icon-dot --success"></span>
      Perolehan Badge Terbaru
    </div>
  </div>
  <div class="table-responsive">
    <table class="das-table">
      <thead>
        <tr>
          <th>SISWA</th>
          <th>KELAS</th>
          <th>BADGE</th>
          <th>TANGGAL PEROLEH</th>
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

{{-- =====================================================================
     SECTION: REKAPITULASI (PRD-004)
     ===================================================================== --}}
<div x-data="{
       kelasId: '',
       periode: 'bulan',
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
         this.periode   = 'bulan';
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
     class="mt-4"
>
  {{-- Card 1: Filter Panel (Atas) --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <div class="row g-2 align-items-end">
        <div class="col-sm-4 col-md-3">
          <label class="form-label text-white-50 small fw-bold mb-1">PILIH PERIODE</label>
          <select x-model="periode" @change="fetchRekap()" class="form-select form-select-sm bg-dark border-0 text-white" style="border-radius: 5px !important;">
            <option value="semua">Semua Waktu</option>
            <option value="minggu">Minggu Ini</option>
            <option value="bulan">Bulan Ini</option>
            <option value="semester">Semester Ini</option>
            <option value="tahun">Tahun Ajaran Ini</option>
          </select>
        </div>
        <div class="col-sm-4 col-md-3">
          <label class="form-label text-white-50 small fw-bold mb-1">PILIH KELAS</label>
          <select x-model="kelasId" @change="fetchRekap()" class="form-select form-select-sm bg-dark border-0 text-white" style="border-radius: 5px !important;">
            <option value="">Semua Kelas</option>
            @foreach($kelasList ?? [] as $kls)
              <option value="{{ $kls->id }}">{{ $kls->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-4 col-md-3" x-show="periode === 'bulan'">
          <label class="form-label text-white-50 small fw-bold mb-1">PILIH BULAN</label>
          <input type="month"
                 x-model="bulan"
                 @change="fetchRekap()"
                 class="form-control form-control-sm bg-dark border-0 text-white"
                 style="color-scheme:dark; border-radius: 5px !important;">
        </div>
        <div class="col-sm-4 col-md-auto d-flex gap-2">
          <button class="das-btn das-btn--primary das-btn--sm"
                   @click="fetchRekap()"
                   :disabled="loading"
                   style="border-radius: 5px !important;">
            <span x-show="!loading" x-cloak><i class="ti tabler-search"></i> Tampilkan Rekap</span>
            <span x-show="loading" x-cloak>
              <span class="d-inline-flex align-items-center gap-1">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memuat...
              </span>
            </span>
          </button>
          <button class="das-btn das-btn--secondary das-btn--sm" @click="resetFilter()" style="border-radius: 5px !important;">
            <i class="ti tabler-x"></i> Reset
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Card 2: Tabel Rekapitulasi Panel (Bawah) --}}
  <div class="das-panel">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot --primary"></span>
        Rekapitulasi
      </div>
      <div class="d-flex align-items-center gap-2">
        {{-- Export Dropdown --}}
        <div class="position-relative" x-data>
          <button class="das-btn das-btn--ghost-sm d-flex align-items-center gap-1"
                  @click="exportOpen = !exportOpen"
                  @click.outside="exportOpen = false"
                  :disabled="!loaded">
            <i class="ti tabler-file-export"></i>
            <span>Export</span>
            <i class="ti tabler-chevron-down" style="font-size:.75rem;"></i>
          </button>
          <div x-show="exportOpen"
               x-transition:enter="transition ease-out duration-100"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="position-absolute end-0 mt-1 rounded shadow-lg border"
               style="min-width:220px;z-index:50;background:var(--das-card-bg,#1e2433);border-color:rgba(255,255,255,.08)!important;">
            <a :href="exportUrl('siswa')"
               class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .15s;"
               @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
               @mouseleave="$el.style.background='transparent'">
              <i class="ti tabler-users" style="font-size:.95rem;"></i> Export Rekap Siswa (.csv)
            </a>
            <a :href="exportUrl('kelas')"
               class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .15s;"
               @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
               @mouseleave="$el.style.background='transparent'">
              <i class="ti tabler-school" style="font-size:.95rem;"></i> Export Rekap Kelas (.csv)
            </a>
            <a :href="exportUrl('badge')"
               class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .15s;"
               @mouseenter="$el.style.background='rgba(255,255,255,.05)'"
               @mouseleave="$el.style.background='transparent'">
              <i class="ti tabler-award" style="font-size:.95rem;"></i> Export Rekap Badge (.csv)
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="das-panel__body">

    {{-- ── ERROR STATE ─────────────────────────────────────────────────── --}}
    <div x-show="error" x-cloak
         class="alert d-flex align-items-center gap-2 mb-3"
         style="background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.3);color:#f87171;border-radius:8px;">
      <i class="ti tabler-alert-circle fs-5"></i>
      <span x-text="error"></span>
    </div>

    {{-- ── EMPTY / INITIAL STATE ───────────────────────────────────────── --}}
    <div x-show="!loaded && !loading && !error" x-cloak
         class="text-center py-5 text-muted">
      <i class="ti tabler-chart-bar-off" style="font-size:2.5rem;opacity:.3;"></i>
      <p class="mt-2 small opacity-50">Klik <strong>Tampilkan Rekap</strong> untuk memuat data.</p>
    </div>

    {{-- ── LOADING STATE ───────────────────────────────────────────────── --}}
    <div x-show="loading" x-cloak class="text-center py-5 text-muted">
      <div class="spinner-border text-primary mb-2"></div>
      <p class="small opacity-50">Memuat data rekapitulasi...</p>
    </div>

    {{-- ── DATA LOADED ─────────────────────────────────────────────────── --}}
    <div x-show="loaded && !loading" x-cloak>

      {{-- ── SUMMARY CARDS ─────────────────────────────────────────────── --}}
      <div class="row mb-4">
        <div class="col-6 col-md-3">
          <div class="das-stat-card das-stat-card--primary">
            <div class="das-stat-card__icon"><i class="ti tabler-users"></i></div>
            <div class="das-stat-card__body">
              <div class="das-stat-card__val" x-text="summary.total_siswa_aktif ?? 0"></div>
              <div class="das-stat-card__label">Siswa Aktif Gamifikasi</div>
            </div>
            <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="das-stat-card das-stat-card--warning">
            <div class="das-stat-card__icon"><i class="ti tabler-award"></i></div>
            <div class="das-stat-card__body">
              <div class="das-stat-card__val" x-text="summary.total_badge_diraih ?? 0"></div>
              <div class="das-stat-card__label">Total Badge Diraih</div>
            </div>
            <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="das-stat-card das-stat-card--success">
            <div class="das-stat-card__icon"><i class="ti tabler-school"></i></div>
            <div class="das-stat-card__body">
              <div class="das-stat-card__val" x-text="summary.total_kelas_aktif ?? 0"></div>
              <div class="das-stat-card__label">Kelas Aktif</div>
            </div>
            <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="das-stat-card das-stat-card--info" style="--das-info:#06b6d4;">
            <div class="das-stat-card__icon"><i class="ti tabler-percentage"></i></div>
            <div class="das-stat-card__body">
              <div class="das-stat-card__val" x-text="(summary.avg_kehadiran_persen ?? 0) + '%'"></div>
              <div class="das-stat-card__label">Rata-rata Kehadiran</div>
            </div>
            <div class="das-stat-card__arrow"><i class="ti tabler-chevron-right"></i></div>
          </div>
        </div>
      </div>

      {{-- ── SUB-TABS NAVIGATION ────────────────────────────────────────── --}}
      <div class="d-flex align-items-center gap-1 mb-3 pb-2"
           style="border-bottom:1px solid rgba(255,255,255,.07);">
        <button class="das-btn das-btn--sm"
                :class="activeSubTab === 'siswa'
                  ? 'das-btn--primary'
                  : 'das-btn--ghost-sm'"
                @click="activeSubTab = 'siswa'">
          <i class="ti tabler-users"></i> Rekap Siswa
        </button>
        <button class="das-btn das-btn--sm"
                :class="activeSubTab === 'kelas'
                  ? 'das-btn--primary'
                  : 'das-btn--ghost-sm'"
                @click="activeSubTab = 'kelas'">
          <i class="ti tabler-school"></i> Rekap Kelas
        </button>
        <button class="das-btn das-btn--sm"
                :class="activeSubTab === 'badge'
                  ? 'das-btn--primary'
                  : 'das-btn--ghost-sm'"
                @click="activeSubTab = 'badge'">
          <i class="ti tabler-award"></i> Rekap Badge
        </button>
      </div>

      {{-- ── SUB-TAB: REKAP SISWA ─────────────────────────────────────── --}}
      <div x-show="activeSubTab === 'siswa'" x-cloak>
        <div x-show="siswadata.length === 0" class="text-center py-4 text-muted opacity-50 small">
          Tidak ada data siswa untuk filter ini.
        </div>
        <div x-show="siswadata.length > 0">
          <div class="table-responsive">
            <table class="das-table">
              <thead>
                <tr>
                  <th class="text-center" style="cursor:pointer;" @click="sortSiswa('rank')">
                    RANK <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th style="cursor:pointer;" @click="sortSiswa('nama_lengkap')">
                    NAMA SISWA <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th>KELAS</th>
                  <th class="text-center" style="cursor:pointer;" @click="sortSiswa('total_hadir')">
                    HADIR <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th class="text-center" style="cursor:pointer;" @click="sortSiswa('total_alpha')">
                    ALPHA <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th class="text-center" style="cursor:pointer;" @click="sortSiswa('skor')">
                    SKOR <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
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
                      <div class="fw-bold text-white" style="font-size:.82rem;" x-text="item.nama_lengkap || '-'"></div>
                      <div class="text-muted" style="font-size:.65rem;" x-text="'NIS: ' + (item.nis || '-')"></div>
                    </td>
                    <td>
                      <span class="badge bg-label-secondary" style="font-size:.65rem;" x-text="item.kelas?.nama || '-'"></span>
                    </td>
                    <td class="text-center fw-semibold" style="color:var(--das-success);" x-text="item.total_hadir ?? 0"></td>
                    <td class="text-center fw-semibold" style="color:#f87171;" x-text="item.total_alpha ?? 0"></td>
                    <td class="text-center">
                      <span class="fw-bold" style="color:#ffd700;" x-text="item.skor ?? 0"></span>
                    </td>
                    <td class="text-center">
                      <div class="d-flex align-items-center justify-content-center gap-1" style="min-width:80px;">
                        <template x-if="item.badge_list && item.badge_list.length > 0">
                          <div class="d-flex align-items-center gap-1">
                            <template x-for="(b, bi) in item.badge_list.slice(0,3)" :key="bi">
                              <span class="das-stat-card__icon"
                                    :title="b.name"
                                    style="width:22px;height:22px;background:rgba(255,215,0,.1);color:#ffd700;border-radius:4px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;">
                                <i :class="'ti ' + (b.icon || 'tabler-award')"></i>
                              </span>
                            </template>
                            <span x-show="item.badge_list.length > 3"
                                  class="text-muted"
                                  style="font-size:.65rem;"
                                  x-text="'+' + (item.badge_list.length - 3)"></span>
                          </div>
                        </template>
                        <template x-if="!item.badge_list || item.badge_list.length === 0">
                          <span class="text-muted" style="font-size:.65rem;">-</span>
                        </template>
                      </div>
                    </td>
                    <td class="text-center">
                      <a :href="'/admin/siswa/' + (item.nis || '')"
                         class="action-btn"
                         title="Lihat Detail">
                        <i class="ti tabler-eye" style="font-size:.85rem;"></i>
                      </a>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          {{-- PAGINATION SISWA --}}
          <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
            <nav aria-label="Navigasi Halaman" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="text-muted small font-monospace">
                Menampilkan <span class="text-white fw-semibold" x-text="((siswaPage - 1) * perPage) + 1"></span>–<span class="text-white fw-semibold" x-text="Math.min(siswaPage * perPage, siswadata.length)"></span> dari <span class="text-white fw-semibold" x-text="siswadata.length"></span> data
              </div>
              <ul class="pagination pagination-sm mb-0 gap-1" style="list-style:none; display:flex; align-items:center; flex-wrap:wrap;">
                {{-- Previous --}}
                <li class="page-item" :class="siswaPage === 1 ? 'disabled' : ''">
                  <button class="das-page-btn" :disabled="siswaPage === 1" @click="siswaPage = Math.max(1, siswaPage - 1)" aria-label="Sebelumnya">
                    <i class="ti tabler-chevron-left" style="font-size:0.85rem;"></i>
                  </button>
                </li>

                {{-- Page Numbers --}}
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

                {{-- Next --}}
                <li class="page-item" :class="siswaPage === totalSiswaPages ? 'disabled' : ''">
                  <button class="das-page-btn" :disabled="siswaPage === totalSiswaPages" @click="siswaPage = Math.min(totalSiswaPages, siswaPage + 1)" aria-label="Selanjutnya">
                    <i class="ti tabler-chevron-right" style="font-size:0.85rem;"></i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>

      {{-- ── SUB-TAB: REKAP KELAS ──────────────────────────────────────── --}}
      <div x-show="activeSubTab === 'kelas'" x-cloak>
        <div x-show="kelasdata.length === 0" class="text-center py-4 text-muted opacity-50 small">
          Tidak ada data kelas untuk filter ini.
        </div>
        <div x-show="kelasdata.length > 0">
          <div class="table-responsive">
            <table class="das-table">
              <thead>
                <tr>
                  <th class="text-center" style="cursor:pointer;" @click="sortKelas('rank')">
                    RANK <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th style="cursor:pointer;" @click="sortKelas('nama')">
                    KELAS <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th>JURUSAN</th>
                  <th class="text-center" style="cursor:pointer;" @click="sortKelas('total_siswa')">
                    TOTAL SISWA <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th class="text-center" style="cursor:pointer;" @click="sortKelas('percentage')">
                    % KEHADIRAN <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
                  </th>
                  <th class="text-center" style="cursor:pointer;" @click="sortKelas('jumlah_badge_diraih')">
                    BADGE DIRAIH <i class="ti tabler-arrows-sort" style="font-size:.7rem;opacity:.5;"></i>
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
                      <span class="badge bg-label-info" style="font-size:.65rem;" x-text="item.jurusan || '-'"></span>
                    </td>
                    <td class="text-center" x-text="item.total_siswa ?? 0"></td>
                    <td class="text-center">
                      <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="progress w-px-75" style="height:6px;background:rgba(255,255,255,.05);">
                          <div class="progress-bar"
                               :class="parseFloat(item.percentage) > 85 ? 'bg-success' : (parseFloat(item.percentage) > 70 ? 'bg-warning' : 'bg-danger')"
                               :style="'width:' + (item.percentage || 0) + '%'"></div>
                        </div>
                        <span class="fw-bold" x-text="parseFloat(item.percentage || 0).toFixed(1) + '%'"></span>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="das-chip --warning" x-text="item.jumlah_badge_diraih ?? 0"></span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          {{-- PAGINATION KELAS --}}
          <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
            <nav aria-label="Navigasi Halaman" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
              <div class="text-muted small font-monospace">
                Menampilkan <span class="text-white fw-semibold" x-text="((kelasPage - 1) * perPage) + 1"></span>–<span class="text-white fw-semibold" x-text="Math.min(kelasPage * perPage, kelasdata.length)"></span> dari <span class="text-white fw-semibold" x-text="kelasdata.length"></span> data
              </div>
              <ul class="pagination pagination-sm mb-0 gap-1" style="list-style:none; display:flex; align-items:center; flex-wrap:wrap;">
                {{-- Previous --}}
                <li class="page-item" :class="kelasPage === 1 ? 'disabled' : ''">
                  <button class="das-page-btn" :disabled="kelasPage === 1" @click="kelasPage = Math.max(1, kelasPage - 1)" aria-label="Sebelumnya">
                    <i class="ti tabler-chevron-left" style="font-size:0.85rem;"></i>
                  </button>
                </li>

                {{-- Page Numbers --}}
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

                {{-- Next --}}
                <li class="page-item" :class="kelasPage === totalKelasPages ? 'disabled' : ''">
                  <button class="das-page-btn" :disabled="kelasPage === totalKelasPages" @click="kelasPage = Math.min(totalKelasPages, kelasPage + 1)" aria-label="Selanjutnya">
                    <i class="ti tabler-chevron-right" style="font-size:0.85rem;"></i>
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>

      {{-- ── SUB-TAB: REKAP BADGE ──────────────────────────────────────── --}}
      <div x-show="activeSubTab === 'badge'" x-cloak>
        <div x-show="badgedata.length === 0" class="text-center py-4 text-muted opacity-50 small">
          Tidak ada data badge untuk filter ini.
        </div>
        <div class="d-flex flex-column gap-2">
          <template x-for="(badge, bi) in badgedata" :key="bi">
            <div class="rounded" style="border:1px solid rgba(255,255,255,.07);overflow:hidden;">
              {{-- Accordion Header --}}
              <div class="d-flex align-items-center gap-3 p-3"
                   style="background:rgba(255,255,255,.03);cursor:pointer;"
                   @click="expandedBadge = expandedBadge === bi ? null : bi">
                <div class="das-stat-card__icon"
                     style="width:38px;height:38px;background:rgba(255,215,0,.1);color:#ffd700;flex-shrink:0;">
                  <i :class="'ti ' + (badge.icon || 'tabler-award')"></i>
                </div>
                <div class="flex-grow-1">
                  <div class="fw-bold text-white small" x-text="badge.name || '-'"></div>
                  <div class="text-muted" style="font-size:.7rem;" x-text="badge.description || ''"></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="das-chip --success" style="font-size:.65rem;"
                        x-text="(badge.total_penerima ?? 0) + ' penerima'"></span>
                  <i class="ti"
                     :class="expandedBadge === bi ? 'tabler-chevron-up' : 'tabler-chevron-down'"
                     style="font-size:.85rem;color:#999;"></i>
                </div>
              </div>
              {{-- Accordion Body --}}
              <div x-show="expandedBadge === bi"
                   x-transition:enter="transition ease-out duration-150"
                   x-transition:enter-start="opacity-0 -translate-y-1"
                   x-transition:enter-end="opacity-100 translate-y-0"
                   style="border-top:1px solid rgba(255,255,255,.05);">
                <div x-show="!badge.penerima || badge.penerima.length === 0"
                     class="text-center py-3 text-muted opacity-50 small">
                  Belum ada penerima badge ini.
                </div>
                <div x-show="badge.penerima && badge.penerima.length > 0" class="table-responsive">
                  <table class="das-table" style="font-size:.78rem;">
                    <thead>
                      <tr>
                        <th>NAMA SISWA</th>
                        <th>KELAS</th>
                        <th class="text-center">TANGGAL DIRAIH</th>
                      </tr>
                    </thead>
                    <tbody>
                      <template x-for="(penerima, pi) in badge.penerima" :key="pi">
                        <tr>
                          <td class="fw-semibold text-white" x-text="penerima.nama_lengkap || '-'"></td>
                          <td>
                            <span class="badge bg-label-secondary" style="font-size:.65rem;"
                                  x-text="penerima.kelas || '-'"></span>
                          </td>
                          <td class="text-center text-muted"
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
  </div>{{-- end das-panel__body --}}
</div>{{-- end das-panel Rekapitulasi --}}
</div>{{-- end Alpine wrapper --}}

{{-- Modal Badge Style --}}
<div class="modal fade" id="badgeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal border-0 shadow-lg">
      <div class="das-modal-head">
        <h5 class="das-modal-title">Konfigurasi Badge Baru</h5>
        <p class="text-muted small mb-0 mt-1">Badge akan otomatis diberikan jika syarat terpenuhi.</p>
      </div>
      <div class="das-modal-body p-4">
        <form id="badgeForm">
          <div class="row gy-3">
            <div class="col-12">
              <label class="form-label text-white-50 small fw-bold">NAMA BADGE</label>
              <input type="text" id="badgeName" class="form-control bg-dark border-0 text-white" placeholder="Contoh: Sang Juara Absensi">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">ICON (TABLER)</label>
              <input type="text" id="badgeIcon" class="form-control bg-dark border-0 text-white" placeholder="tabler-star">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">TIPE</label>
              <select id="badgeType" class="form-select bg-dark border-0 text-white">
                <option value="individual">Individual</option>
                <option value="class">Kelas</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label text-white-50 small fw-bold">DESKRIPSI</label>
              <textarea id="badgeDescription" class="form-control bg-dark border-0 text-white" rows="2"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">JUMLAH HARI</label>
              <input type="number" id="badgeRequirement" class="form-control bg-dark border-0 text-white" value="30">
            </div>
            <div class="col-md-6">
              <label class="form-label text-white-50 small fw-bold">SYARAT</label>
              <select id="badgeRequirementType" class="form-select bg-dark border-0 text-white">
                <option value="consecutive">Beruntun</option>
                <option value="total">Total Akumulasi</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="d-flex gap-2 p-4 pt-0">
        <button type="button" class="das-btn das-btn--secondary w-100" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="das-btn das-btn--primary w-100" onclick="saveBadge()">Simpan Achievement</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
  loadLeaderboard();
  loadStudentLeaderboard();
  loadBadges();
  loadStudentBadges();
});

async function loadLeaderboard() {
  try {
    const response = await fetch('/api/v1/innovation/leaderboard');
    const result = await response.json();
    
    const tbody = document.getElementById('leaderboardBody');
    const data = result.data || [];
    
    document.getElementById('totalKelas').textContent = data.length;
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted opacity-50">Belum ada data peringkat.</td></tr>`;
      return;
    }
    
    tbody.innerHTML = data.map((item, index) => {
      const percentage = parseFloat(item.percentage || 0);
      let chipClass = '--success';
      let statusText = 'Sangat Baik';
      
      if (percentage < 70) { chipClass = '--danger'; statusText = 'Buruk'; }
      else if (percentage < 85) { chipClass = '--warning'; statusText = 'Cukup'; }
      
      const rankBadge = index === 0 ? '🏆' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);
      
      return `
        <tr>
          <td class="text-center fw-bold fs-5">${rankBadge}</td>
          <td>
            <div class="fw-bold text-white">${item.kelas?.nama || '-'}</div>
            <div class="small text-muted">${item.kelas?.jurusan || 'Semua Jurusan'}</div>
          </td>
          <td class="text-center">${item.total_present} / ${item.total_attendance}</td>
          <td class="text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <div class="progress w-px-75" style="height: 6px; background: rgba(255,255,255,0.05);">
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

async function loadStudentLeaderboard() {
  try {
    const response = await fetch('/api/v1/innovation/leaderboard/students?limit=20');
    const result = await response.json();
    
    const tbody = document.getElementById('studentLeaderboardBody');
    const data = result.data || [];
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted opacity-50">Belum ada data peringkat siswa. Klik "Hitung Ulang Skor" untuk memulai.</td></tr>`;
      return;
    }
    
    tbody.innerHTML = data.map((item, index) => {
      const rankBadge = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : (index + 1);
      const badges = item.siswa?.student_badges || [];
      const badgeIcons = badges.slice(0, 3).map(b => 
        `<span class="das-stat-card__icon" style="width:22px;height:22px;background:rgba(255,215,0,0.1);color:#ffd700;border-radius:4px;display:inline-flex;align-items:center;justify-content:center;font-size:.65rem;margin:0 1px;"><i class="ti ${b.badge?.icon || 'tabler-award'}"></i></span>`
      ).join('');
      
      return `
        <tr>
          <td class="text-center fw-bold fs-5">${rankBadge}</td>
          <td>
            <div class="fw-bold text-white" style="font-size:.82rem;">${item.siswa?.nama_lengkap || '-'}</div>
            <div class="text-muted" style="font-size:.65rem;">NIS: ${item.siswa?.nis || '-'}</div>
          </td>
          <td>
            <span class="badge bg-label-secondary" style="font-size:.65rem;">${item.siswa?.kelas?.nama || '-'}</span>
          </td>
          <td class="text-center fw-semibold" style="color:var(--das-success);">${item.total_present}/${item.total_attendance}</td>
          <td class="text-center">
            <span class="fw-bold" style="color:${item.score > 0 ? '#ffd700' : '#999'};font-size:.95rem;">${item.score}</span>
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
    btn.style.color = '#999';
    btn.style.background = 'transparent';
  });
  const activeBtn = document.getElementById(tab + '-tab');
  if (activeBtn) {
    activeBtn.classList.add('active');
    activeBtn.style.color = 'white';
    activeBtn.style.background = 'rgba(255,255,255,0.06)';
  }
}

async function calculateLeaderboard() {
  const btn = event.currentTarget;
  const originalHtml = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="ti tabler-loader-2 spin"></i> Menghitung...';

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
      await loadLeaderboard();
      await loadStudentLeaderboard();
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
    
    document.getElementById('totalBadges').textContent = data.length;
    
    if (data.length === 0) {
      container.innerHTML = `<div class="text-center py-4 text-muted">Belum ada badge.</div>`;
      return;
    }
    
    container.innerHTML = data.map(badge => `
      <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
        <div class="das-stat-card__icon" style="width: 40px; height: 40px; background: rgba(255, 215, 0, 0.1); color: #ffd700;">
          <i class="ti ${badge.icon}"></i>
        </div>
        <div class="flex-grow-1">
          <div class="d-flex align-items-center justify-content-between">
            <span class="fw-bold text-white small">${badge.name}</span>
            <span class="badge bg-label-primary" style="font-size: 0.6rem;">${badge.badge_type}</span>
          </div>
          <div class="text-muted" style="font-size: 0.7rem;">${badge.requirement_days} hari ${badge.requirement_type}</div>
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
    
    document.getElementById('studentEarned').textContent = result.total_earned_students || '0';
    
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted opacity-50">Belum ada aktivitas perolehan badge terbaru.</td></tr>`;
      return;
    }
    
    tbody.innerHTML = data.map(item => {
      const earnedAt = item.earned_at ? new Date(item.earned_at).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
      }) : '-';
      
      return `
        <tr>
          <td>
            <div class="fw-bold text-white small">${item.siswa?.nama_lengkap || '-'}</div>
            <div class="text-muted" style="font-size: 0.7rem;">NISN: ${item.siswa?.nisn || '-'}</div>
          </td>
          <td>
            <span class="badge bg-label-secondary">${item.siswa?.kelas?.nama || '-'}</span>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="das-stat-card__icon" style="width: 28px; height: 28px; background: rgba(255, 215, 0, 0.1); color: #ffd700; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.90rem;">
                <i class="ti ${item.badge?.icon || 'tabler-award'}"></i>
              </span>
              <div>
                <div class="fw-bold text-white" style="font-size: 0.8rem;">${item.badge?.name || '-'}</div>
                <div class="text-muted" style="font-size: 0.65rem;">${item.badge?.description || '-'}</div>
              </div>
            </div>
          </td>
          <td class="text-muted small">${earnedAt}</td>
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
</script>

<style>
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
@endsection