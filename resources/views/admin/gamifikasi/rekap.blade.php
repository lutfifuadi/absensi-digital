@extends('layouts/layoutMaster')

@section('title', 'Rekapitulasi Gamifikasi — ' . ($pengaturanArr['nama_sekolah'] ?? 'Sistem Absensi'))

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
      transition: all 0.15s ease;
      cursor: pointer;
      font-family: inherit;
    }
    .das-page-btn:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.15);
    }
    .das-page-btn.--active {
      background: var(--das-primary);
      border-color: var(--das-primary);
      color: #fff;
      box-shadow: 0 4px 12px rgba(108, 124, 240, 0.3);
    }
    .das-page-btn:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }
    .rekap-summary-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .export-item:hover {
      background: rgba(255, 255, 255, 0.05) !important;
    }
    .tab-nav-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 6px;
      border: none;
      font-size: 0.8rem;
      font-weight: 600;
      background: transparent;
      color: var(--das-text-muted);
      cursor: pointer;
      transition: all 0.18s ease;
    }
    .tab-nav-btn.active,
    .tab-nav-btn:hover {
      background: rgba(108, 124, 240, 0.15);
      color: #fff;
    }
    .tab-nav-btn.active {
      box-shadow: 0 2px 8px rgba(108, 124, 240, 0.25);
    }
    .search-wrapper {
      position: relative;
    }
    .search-wrapper .search-icon {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--das-text-muted);
      pointer-events: none;
      font-size: 0.9rem;
    }
    .search-wrapper input {
      padding-left: 32px !important;
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER — Rekapitulasi Gamifikasi
       ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-6">
    <div class="das-hero__bg" aria-hidden="true"></div>
    <div class="das-hero__scanline" aria-hidden="true"></div>
    <div class="das-hero__grid-lines" aria-hidden="true"></div>

    <div class="das-hero__inner d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-4">
      <div class="das-hero__identity me-md-3">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder" style="background: rgba(99, 102, 241, 0.15); border: 1px solid rgba(99, 102, 241, 0.35);">
            <i class="ti tabler-chart-bar text-primary fs-2" aria-hidden="true"></i>
          </div>
        </div>
        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="das-hero__pulse-dot" aria-hidden="true"></span>
            Reporting &amp; Analytics
          </div>
          <h1 class="das-hero__school text-gradient-gold">Rekapitulasi Gamifikasi</h1>
          <p class="das-hero__welcome">
            Laporan rekapitulasi poin, badge, dan tingkat kedisiplinan siswa per periode.
            @if(isset($tahunAkademikAktif))
              <span class="badge bg-label-primary ms-1" style="font-size:0.7rem;">TA {{ $tahunAkademikAktif->nama }} {{ $tahunAkademikAktif->semester }}</span>
            @endif
          </p>
        </div>
      </div>

      <div class="das-hero__actions d-flex align-items-center gap-3 flex-wrap flex-shrink-0 ms-md-auto">
        <a href="{{ route('admin.gamifikasi.index') }}" class="btn das-btn --secondary d-flex align-items-center gap-2">
          <i class="ti tabler-trophy fs-5"></i> Papan Peringkat
        </a>
      </div>
    </div>
  </div>{{-- /das-hero --}}


  {{-- ═══════════════════════════════════════════════════════
       ALPINE.JS CONTAINER — Filter + Laporan
       ═══════════════════════════════════════════════════════ --}}
  <div x-data="{
         kelasId:      '',
         periode:      'semua',
         bulan:        '{{ now()->format('Y-m') }}',
         loading:      false,
         loaded:       false,
         error:        null,
         exportOpen:   false,
         activeTab:    'siswa',
         searchQuery:  '',
         summary:      {},
         siswadata:    [],
         kelasdata:    [],
         badgedata:    [],
         siswaPage:    1,
         siswaPerPage: 15,
         kelasPage:    1,
         kelasPerPage: 15,

         get filteredSiswa() {
           if (!this.searchQuery.trim()) return this.siswadata;
           const q = this.searchQuery.toLowerCase();
           return this.siswadata.filter(s =>
             (s.nama_lengkap && s.nama_lengkap.toLowerCase().includes(q)) ||
             (s.nis && s.nis.toLowerCase().includes(q)) ||
             (s.kelas && s.kelas.nama && s.kelas.nama.toLowerCase().includes(q))
           );
         },

         get paginatedSiswa() {
           const start = (this.siswaPage - 1) * this.siswaPerPage;
           return this.filteredSiswa.slice(start, start + this.siswaPerPage);
         },

         get totalSiswaPages() {
           return Math.max(1, Math.ceil(this.filteredSiswa.length / this.siswaPerPage));
         },

         get filteredKelas() {
           if (!this.searchQuery.trim()) return this.kelasdata;
           const q = this.searchQuery.toLowerCase();
           return this.kelasdata.filter(k =>
             (k.nama_kelas && k.nama_kelas.toLowerCase().includes(q)) ||
             (k.nama && k.nama.toLowerCase().includes(q))
           );
         },

         get paginatedKelas() {
           const start = (this.kelasPage - 1) * this.kelasPerPage;
           return this.filteredKelas.slice(start, start + this.kelasPerPage);
         },

         get totalKelasPages() {
           return Math.max(1, Math.ceil(this.filteredKelas.length / this.kelasPerPage));
         },

         siswaPaginationPages() {
           return this._buildPages(this.siswaPage, this.totalSiswaPages);
         },

         kelasPaginationPages() {
           return this._buildPages(this.kelasPage, this.totalKelasPages);
         },

         _buildPages(current, total) {
           const pages = [];
           for (let i = 1; i <= total; i++) {
             if (i === 1 || i === total || (i >= current - 2 && i <= current + 2)) {
               pages.push(i);
             } else if (pages[pages.length - 1] !== '...') {
               pages.push('...');
             }
           }
           return pages;
         },

         async fetchRekap() {
           this.siswaPage  = 1;
           this.kelasPage  = 1;
           this.loading    = true;
           this.error      = null;
           try {
             const params = new URLSearchParams();
             if (this.kelasId) params.append('kelas_id', this.kelasId);
             params.append('periode', this.periode);
             if (this.periode === 'bulan' && this.bulan) params.append('bulan', this.bulan);
             const res = await fetch('/admin/gamifikasi/rekap?' + params.toString(), {
               headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
             });
             if (!res.ok) throw new Error('Gagal memuat data (HTTP ' + res.status + ')');
             const json = await res.json();
             if (!json.success) throw new Error(json.message || 'Respons tidak valid');
             this.summary   = json.data.summary || {};
             this.siswadata = json.data.siswa   || [];
             this.kelasdata = json.data.kelas   || [];
             this.badgedata = json.data.badge   || [];
             this.loaded    = true;
           } catch (e) {
             this.error = e.message;
           } finally {
             this.loading = false;
           }
         },

         resetFilter() {
           this.kelasId     = '';
           this.periode     = 'semua';
           this.bulan       = '{{ now()->format('Y-m') }}';
           this.loaded      = false;
           this.error       = null;
           this.searchQuery = '';
           this.summary     = {};
           this.siswadata   = [];
           this.kelasdata   = [];
           this.badgedata   = [];
           this.siswaPage   = 1;
           this.kelasPage   = 1;
         },

         exportUrl(type) {
           const params = new URLSearchParams({ type });
           if (this.kelasId) params.append('kelas_id', this.kelasId);
           params.append('periode', this.periode);
           if (this.periode === 'bulan' && this.bulan) params.append('bulan', this.bulan);
           return '/admin/gamifikasi/rekap/export?' + params.toString();
         }
       }"
       x-init="fetchRekap()"
  >

    {{-- ══════════════════════════════════════════════
         CARD FILTER
         ══════════════════════════════════════════════ --}}
    <div class="card card-grad-primary mb-6">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="das-panel__icon-dot das-panel__icon-dot--info"></span>
          <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-filter me-2 text-info"></i>Filter Rekapitulasi</h5>
        </div>
        {{-- Export Dropdown --}}
        <div class="position-relative" @click.outside="exportOpen = false">
          <button class="btn das-btn --ghost d-flex align-items-center gap-1"
                  @click="exportOpen = !exportOpen"
                  :disabled="!loaded">
            <i class="ti tabler-file-export me-1"></i>
            Export CSV
            <i class="ti tabler-chevron-down ms-1" style="font-size:.72rem;"></i>
          </button>
          <div x-show="exportOpen"
               x-transition:enter="transition ease-out duration-100"
               x-transition:enter-start="opacity-0 scale-95"
               x-transition:enter-end="opacity-100 scale-100"
               x-transition:leave="transition ease-in duration-75"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               class="position-absolute end-0 mt-1 rounded-3 shadow-lg py-1"
               style="min-width:220px;z-index:60;background:var(--das-surface-2,#16213A);border:1px solid rgba(255,255,255,.1);">
            <a :href="exportUrl('siswa')"
               class="export-item d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .12s;">
              <i class="ti tabler-users text-primary"></i> Export Rekap Siswa (.csv)
            </a>
            <a :href="exportUrl('kelas')"
               class="export-item d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .12s;">
              <i class="ti tabler-school text-info"></i> Export Rekap Kelas (.csv)
            </a>
            <a :href="exportUrl('badge')"
               class="export-item d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none"
               style="transition:background .12s;">
              <i class="ti tabler-award text-warning"></i> Export Rekap Badge (.csv)
            </a>
          </div>
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
          <div class="col-md" x-show="periode === 'bulan'" x-cloak>
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
              <span x-show="loading" x-cloak style="display:none !important;">
                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memuat...
              </span>
            </button>
            <button class="btn das-btn --secondary d-flex align-items-center gap-1" @click="resetFilter()">
              <i class="ti tabler-rotate-clockwise me-1"></i> Reset
            </button>
          </div>
        </div>
      </div>
    </div>


    {{-- ══════════════════════════════════════════════
         CARD LAPORAN
         ══════════════════════════════════════════════ --}}
    <div class="card card-grad-primary mb-6">
      <div class="card-header d-flex align-items-center gap-2 pb-3">
        <span class="das-panel__icon-dot das-panel__icon-dot--success"></span>
        <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-chart-bar me-2 text-success"></i>Laporan Rekapitulasi</h5>
      </div>

      <div class="card-body">

        {{-- STATE: Error --}}
        <div x-show="error" x-cloak
             class="alert d-flex align-items-center gap-2 mb-4"
             style="background:rgba(239,90,90,.12);border:1px solid rgba(239,90,90,.3);color:#f87171;border-radius:8px;">
          <i class="ti tabler-alert-circle fs-5"></i>
          <span x-text="error"></span>
        </div>

        {{-- STATE: Initial --}}
        <div x-show="!loaded && !loading && !error" x-cloak class="text-center py-6">
          <i class="ti tabler-chart-bar-off" style="font-size:3rem;opacity:.25;"></i>
          <p class="mt-3 text-muted small opacity-75">Pilih filter dan klik <strong class="text-white">Tampilkan</strong> untuk memuat data rekapitulasi.</p>
        </div>

        {{-- STATE: Loading --}}
        <div x-show="loading" x-cloak class="text-center py-6">
          <div class="spinner-border text-primary mb-3" role="status" style="width:2.5rem;height:2.5rem;"></div>
          <p class="text-muted small opacity-75">Memuat data rekapitulasi...</p>
        </div>

        {{-- STATE: Data loaded --}}
        <div x-show="loaded && !loading" x-cloak>

          {{-- SUMMARY CARDS --}}
          <div class="row g-4 mb-5">
            <div class="col-6 col-md-3">
              <div class="card card-grad-success h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rekap-summary-icon bg-label-success">
                      <i class="ti tabler-user-check text-success fs-5"></i>
                    </div>
                    <small class="text-body-secondary text-uppercase fw-bold" style="font-size:.63rem;letter-spacing:.5px;">Total Kehadiran</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-success" x-text="summary.total_kehadiran ?? 0"></h4>
                  <small class="text-muted" style="font-size:.7rem;">Hadir + Terlambat</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-primary h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rekap-summary-icon bg-label-primary">
                      <i class="ti tabler-clock text-primary fs-5"></i>
                    </div>
                    <small class="text-body-secondary text-uppercase fw-bold" style="font-size:.63rem;letter-spacing:.5px;">Rata-rata Jam Masuk</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-primary" x-text="summary.avg_jam_terawal ?? '-'"></h4>
                  <small class="text-muted" style="font-size:.7rem;">Jam masuk terawal</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-info h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rekap-summary-icon bg-label-info">
                      <i class="ti tabler-flame text-info fs-5"></i>
                    </div>
                    <small class="text-body-secondary text-uppercase fw-bold" style="font-size:.63rem;letter-spacing:.5px;">Avg Streak</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-info" x-text="(summary.avg_streak ?? 0) + ' hr'"></h4>
                  <small class="text-muted" style="font-size:.7rem;">Beruntun tepat waktu</small>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-warning h-100">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rekap-summary-icon bg-label-warning">
                      <i class="ti tabler-percentage text-warning fs-5"></i>
                    </div>
                    <small class="text-body-secondary text-uppercase fw-bold" style="font-size:.63rem;letter-spacing:.5px;">Konsistensi</small>
                  </div>
                  <h4 class="mb-0 fw-bold text-warning" x-text="(summary.tingkat_konsistensi ?? 0) + '%'"></h4>
                  <small class="text-muted" style="font-size:.7rem;">Tingkat hadir tepat waktu</small>
                </div>
              </div>
            </div>
          </div>{{-- /summary --}}

          {{-- TAB NAVIGATION --}}
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3"
               style="border-bottom:1px solid rgba(255,255,255,.08);">
            <div class="d-flex align-items-center gap-1 p-1 rounded-2 bg-label-secondary">
              <button class="tab-nav-btn" :class="{ 'active': activeTab === 'siswa' }" @click="activeTab = 'siswa'; siswaPage = 1;">
                <i class="ti tabler-users"></i> Per Siswa
                <span class="badge bg-primary fw-bold" style="font-size:.6rem;" x-text="siswadata.length"></span>
              </button>
              <button class="tab-nav-btn" :class="{ 'active': activeTab === 'kelas' }" @click="activeTab = 'kelas'; kelasPage = 1;">
                <i class="ti tabler-school"></i> Per Kelas
                <span class="badge bg-info fw-bold" style="font-size:.6rem;" x-text="kelasdata.length"></span>
              </button>
              <button class="tab-nav-btn" :class="{ 'active': activeTab === 'badge' }" @click="activeTab = 'badge'">
                <i class="ti tabler-award"></i> Per Badge
                <span class="badge bg-warning fw-bold" style="font-size:.6rem;" x-text="badgedata.length"></span>
              </button>
            </div>

            {{-- Search box (visible on siswa & kelas tab only) --}}
            <div class="search-wrapper" x-show="activeTab !== 'badge'" x-cloak style="min-width:240px;">
              <i class="ti tabler-search search-icon"></i>
              <input type="text"
                     class="form-control form-control-sm"
                     placeholder="Cari nama / NIS / kelas..."
                     x-model="searchQuery"
                     @input="siswaPage = 1; kelasPage = 1;">
            </div>
          </div>

          {{-- ─────────────────────────
               TAB 1: REKAP SISWA
          ───────────────────────── --}}
          <div x-show="activeTab === 'siswa'">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr style="background:rgba(255,255,255,.03);">
                    <th class="text-center" style="width:65px;">RANK</th>
                    <th>SISWA</th>
                    <th>KELAS</th>
                    <th class="text-center">SKOR</th>
                    <th class="text-center">HADIR</th>
                    <th class="text-center">TERLAMBAT</th>
                    <th class="text-center">SAKIT/IZIN</th>
                    <th class="text-center">ALPHA</th>
                    <th class="text-center">JAM MASUK</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="item in paginatedSiswa" :key="item.siswa_id">
                    <tr class="rekap-row-hover">
                      <td class="text-center fw-bold fs-6">
                        <span x-text="item.rank === 1 ? '🥇' : (item.rank === 2 ? '🥈' : (item.rank === 3 ? '🥉' : '#' + item.rank))"></span>
                      </td>
                      <td>
                        <div class="fw-bold text-white d-flex align-items-center gap-1 flex-wrap" style="font-size:.85rem;">
                          <span x-text="item.nama_lengkap || '-'"></span>
                          <template x-if="item.is_teladan || (item.total_alpha === 0 && item.total_hadir > 0)">
                            <span class="badge bg-label-success" style="font-size:.6rem;">⭐ Teladan</span>
                          </template>
                        </div>
                        <div class="text-muted font-monospace" style="font-size:.7rem;" x-text="'NIS: ' + (item.nis || '-')"></div>
                      </td>
                      <td>
                        <span class="badge bg-label-secondary" style="font-size:.7rem;" x-text="item.kelas?.nama || '-'"></span>
                      </td>
                      <td class="text-center">
                        <span class="fw-bold text-warning" style="font-size:.95rem;" x-text="item.skor ?? 0"></span>
                      </td>
                      <td class="text-center fw-semibold text-success" x-text="item.total_hadir ?? 0"></td>
                      <td class="text-center text-warning" x-text="item.total_terlambat ?? 0"></td>
                      <td class="text-center text-info" x-text="(item.total_sakit ?? 0) + (item.total_izin ?? 0)"></td>
                      <td class="text-center text-danger" x-text="item.total_alpha ?? 0"></td>
                      <td class="text-center font-monospace text-muted" style="font-size:.78rem;" x-text="item.avg_jam_masuk_formatted || '-'"></td>
                    </tr>
                  </template>
                  <template x-if="filteredSiswa.length === 0">
                    <tr>
                      <td colspan="9" class="text-center py-5 text-muted opacity-50">
                        <i class="ti tabler-search-off d-block mb-2" style="font-size:2rem;"></i>
                        Tidak ada data siswa yang cocok.
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            {{-- Pagination Siswa --}}
            <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2"
                 x-show="totalSiswaPages > 1">
              <div class="text-muted small">
                Menampilkan <strong class="text-white" x-text="((siswaPage - 1) * siswaPerPage) + 1"></strong>–<strong class="text-white" x-text="Math.min(siswaPage * siswaPerPage, filteredSiswa.length)"></strong>
                dari <strong class="text-white" x-text="filteredSiswa.length"></strong> siswa
              </div>
              <div class="d-flex align-items-center gap-1">
                <button class="das-page-btn" :disabled="siswaPage === 1" @click="siswaPage--">
                  <i class="ti tabler-chevron-left"></i>
                </button>
                <template x-for="p in siswaPaginationPages()" :key="'sp-' + p">
                  <span>
                    <template x-if="p === '...'">
                      <span class="px-1 text-muted small">...</span>
                    </template>
                    <template x-if="p !== '...'">
                      <button class="das-page-btn" :class="{ '--active': siswaPage === p }" @click="siswaPage = p" x-text="p"></button>
                    </template>
                  </span>
                </template>
                <button class="das-page-btn" :disabled="siswaPage === totalSiswaPages" @click="siswaPage++">
                  <i class="ti tabler-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>{{-- /tab siswa --}}


          {{-- ─────────────────────────
               TAB 2: REKAP KELAS
          ───────────────────────── --}}
          <div x-show="activeTab === 'kelas'">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr style="background:rgba(255,255,255,.03);">
                    <th class="text-center" style="width:65px;">RANK</th>
                    <th>KELAS</th>
                    <th class="text-center">JURUSAN</th>
                    <th class="text-center">TOTAL SISWA</th>
                    <th class="text-center">KEHADIRAN (%)</th>
                    <th class="text-center">RATA-RATA SKOR</th>
                    <th class="text-center">TOTAL BADGE</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="item in paginatedKelas" :key="item.kelas_id">
                    <tr class="rekap-row-hover">
                      <td class="text-center fw-bold fs-6">
                        <span x-text="item.rank === 1 ? '🏆' : (item.rank === 2 ? '🥈' : (item.rank === 3 ? '🥉' : '#' + item.rank))"></span>
                      </td>
                      <td class="fw-bold text-white" x-text="item.nama_kelas || item.nama || '-'"></td>
                      <td class="text-center">
                        <span class="badge bg-label-info" style="font-size:.7rem;" x-text="item.jurusan || 'Umum'"></span>
                      </td>
                      <td class="text-center text-muted fw-semibold" x-text="item.total_siswa ?? 0"></td>
                      <td class="text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                          <div class="progress" style="height:5px;width:64px;background:rgba(255,255,255,.06);">
                            <div class="progress-bar"
                                 :class="parseFloat(item.tingkat_kehadiran ?? item.percentage ?? 0) >= 85 ? 'bg-success' : (parseFloat(item.tingkat_kehadiran ?? item.percentage ?? 0) >= 70 ? 'bg-warning' : 'bg-danger')"
                                 :style="'width:' + (item.tingkat_kehadiran ?? item.percentage ?? 0) + '%'"></div>
                          </div>
                          <span class="fw-bold" x-text="parseFloat(item.tingkat_kehadiran ?? item.percentage ?? 0).toFixed(1) + '%'"></span>
                        </div>
                      </td>
                      <td class="text-center fw-bold text-warning" x-text="item.avg_skor ?? 0"></td>
                      <td class="text-center">
                        <span class="badge bg-label-warning fw-bold" x-text="item.total_badge ?? item.jumlah_badge_diraih ?? 0"></span>
                      </td>
                    </tr>
                  </template>
                  <template x-if="filteredKelas.length === 0">
                    <tr>
                      <td colspan="7" class="text-center py-5 text-muted opacity-50">
                        <i class="ti tabler-search-off d-block mb-2" style="font-size:2rem;"></i>
                        Tidak ada data kelas yang cocok.
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            {{-- Pagination Kelas --}}
            <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2"
                 x-show="totalKelasPages > 1">
              <div class="text-muted small">
                Menampilkan <strong class="text-white" x-text="((kelasPage - 1) * kelasPerPage) + 1"></strong>–<strong class="text-white" x-text="Math.min(kelasPage * kelasPerPage, filteredKelas.length)"></strong>
                dari <strong class="text-white" x-text="filteredKelas.length"></strong> kelas
              </div>
              <div class="d-flex align-items-center gap-1">
                <button class="das-page-btn" :disabled="kelasPage === 1" @click="kelasPage--">
                  <i class="ti tabler-chevron-left"></i>
                </button>
                <template x-for="p in kelasPaginationPages()" :key="'kp-' + p">
                  <span>
                    <template x-if="p === '...'">
                      <span class="px-1 text-muted small">...</span>
                    </template>
                    <template x-if="p !== '...'">
                      <button class="das-page-btn" :class="{ '--active': kelasPage === p }" @click="kelasPage = p" x-text="p"></button>
                    </template>
                  </span>
                </template>
                <button class="das-page-btn" :disabled="kelasPage === totalKelasPages" @click="kelasPage++">
                  <i class="ti tabler-chevron-right"></i>
                </button>
              </div>
            </div>
          </div>{{-- /tab kelas --}}


          {{-- ─────────────────────────
               TAB 3: REKAP BADGE
          ───────────────────────── --}}
          <div x-show="activeTab === 'badge'">
            <div x-show="badgedata.length === 0" class="text-center py-5 text-muted opacity-50">
              <i class="ti tabler-award-off d-block mb-2" style="font-size:2.5rem;"></i>
              Belum ada data badge untuk periode ini.
            </div>
            <div class="row g-4" x-show="badgedata.length > 0">
              <template x-for="b in badgedata" :key="b.badge_id">
                <div class="col-md-6 col-xl-4">
                  <div class="card card-grad-gold h-100">
                    <div class="card-body p-4">
                      <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="das-stat-card__icon"
                             style="width:46px;height:46px;background:rgba(212,169,74,.2);color:var(--das-gold,#D4A94A);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                          <i class="ti" :class="b.icon || 'tabler-award'"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                          <h6 class="mb-1 fw-bold text-white" style="font-size:.9rem;" x-text="b.nama_badge || b.name || '-'"></h6>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-warning" style="font-size:.65rem;" x-text="b.kategori || b.badge_type || 'Individual'"></span>
                            <span class="text-muted" style="font-size:.7rem;" x-text="'+' + (b.poin ?? b.requirement_days ?? 0) + ' Poin'"></span>
                          </div>
                        </div>
                      </div>
                      <div class="rounded-2 p-2 d-flex align-items-center justify-content-between"
                           style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);">
                        <span class="text-muted small">Total Penerima</span>
                        <strong class="text-warning font-monospace" x-text="(b.total_penerima ?? 0) + ' Siswa'"></strong>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>{{-- /tab badge --}}

        </div>{{-- /loaded --}}
      </div>{{-- /card-body --}}
    </div>{{-- /card laporan --}}

  </div>{{-- /x-data --}}

@endsection
