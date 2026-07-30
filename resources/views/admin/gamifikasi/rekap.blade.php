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
    }
    .das-page-btn:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
    }
    .das-page-btn.--active {
      background: var(--das-primary);
      border-color: var(--das-primary);
      color: #fff;
    }
    .das-page-btn:disabled {
      opacity: 0.4;
      cursor: not-allowed;
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
            Reporting & Analytics
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
          <i class="ti tabler-trophy fs-5"></i> Lihat Papan Peringkat
        </a>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       CONTAINER REKAPITULASI GAMIFIKASI (ALPINE.JS)
       ═══════════════════════════════════════════════════════ --}}
  <div x-data="{
         kelasId: '',
         periode: 'semua',
         bulan: '{{ now()->format('Y-m') }}',
         loading: false,
         loaded: false,
         error: null,
         summary: {},
         siswadata: [],
         kelasdata: [],
         badgedata: [],
         activeTab: 'siswa',
         searchQuery: '',
         sortSiswaCol: 'rank',
         sortSiswaDir: 'asc',
         sortKelasCol: 'rank',
         sortKelasDir: 'asc',
         siswaPage: 1,
         siswaPerPage: 15,
         kelasPage: 1,
         kelasPerPage: 15,
         expandedBadge: null,
         exportOpen: false,

         get filteredSiswa() {
           let list = this.siswadata;
           if (this.searchQuery.trim() !== '') {
             const q = this.searchQuery.toLowerCase();
             list = list.filter(s =>
               (s.nama_lengkap && s.nama_lengkap.toLowerCase().includes(q)) ||
               (s.nis && s.nis.toLowerCase().includes(q)) ||
               (s.kelas && s.kelas.nama && s.kelas.nama.toLowerCase().includes(q))
             );
           }
           return list;
         },

         get paginatedSiswa() {
           const start = (this.siswaPage - 1) * this.siswaPerPage;
           return this.filteredSiswa.slice(start, start + this.siswaPerPage);
         },

         get totalSiswaPages() {
           return Math.ceil(this.filteredSiswa.length / this.siswaPerPage) || 1;
         },

         get filteredKelas() {
           let list = this.kelasdata;
           if (this.searchQuery.trim() !== '') {
             const q = this.searchQuery.toLowerCase();
             list = list.filter(k => k.nama_kelas && k.nama_kelas.toLowerCase().includes(q));
           }
           return list;
         },

         get paginatedKelas() {
           const start = (this.kelasPage - 1) * this.kelasPerPage;
           return this.filteredKelas.slice(start, start + this.kelasPerPage);
         },

         get totalKelasPages() {
           return Math.ceil(this.filteredKelas.length / this.kelasPerPage) || 1;
         },

         get siswaPaginationPages() {
           const current = this.siswaPage;
           const total = this.totalSiswaPages;
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

         get kelasPaginationPages() {
           const current = this.kelasPage;
           const total = this.totalKelasPages;
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

         exportUrl(type) {
           const params = new URLSearchParams({ type });
           if (this.kelasId) params.append('kelas_id', this.kelasId);
           params.append('periode', this.periode);
           if (this.periode === 'bulan' && this.bulan) params.append('bulan', this.bulan);
           return '/admin/gamifikasi/rekap/export?' + params.toString();
         }
       }"
       x-init="fetchRekap()"
       class="mb-6"
  >
    {{-- Card 1: Filter Panel --}}
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

    {{-- Card 2: Laporan Rekapitulasi --}}
    <div class="card card-grad-primary">
      <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="das-panel__icon-dot das-panel__icon-dot--success"></span>
          <h5 class="mb-0 fw-bold text-white"><i class="ti tabler-chart-bar me-2 text-success"></i>Laporan Rekapitulasi</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
          <div class="position-relative">
            <button class="btn das-btn --ghost d-flex align-items-center gap-1"
                    @click="exportOpen = !exportOpen"
                    @click.outside="exportOpen = false"
                    :disabled="!loaded">
              <i class="ti tabler-file-export me-1"></i>
              <span>Export CSV</span>
              <i class="ti tabler-chevron-down ms-1" style="font-size:.75rem;"></i>
            </button>
            <div x-show="exportOpen"
                 x-transition
                 class="position-absolute end-0 mt-1 rounded-3 shadow-lg border"
                 style="min-width:220px;z-index:50;background:var(--das-surface-2,#16213A);border-color:rgba(255,255,255,.1)!important;">
              <a :href="exportUrl('siswa')" class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none hover-bg-light">
                <i class="ti tabler-users text-primary"></i> Export Rekap Siswa (.csv)
              </a>
              <a :href="exportUrl('kelas')" class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none hover-bg-light">
                <i class="ti tabler-school text-info"></i> Export Rekap Kelas (.csv)
              </a>
              <a :href="exportUrl('badge')" class="d-flex align-items-center gap-2 px-3 py-2 text-white small text-decoration-none hover-bg-light">
                <i class="ti tabler-award text-warning"></i> Export Rekap Badge (.csv)
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div x-show="error" x-cloak class="alert alert-danger mb-4" x-text="error"></div>
        <div x-show="!loaded && !loading && !error" x-cloak class="text-center py-5 text-muted">
          <i class="ti tabler-chart-bar-off" style="font-size:2.8rem;opacity:.3;"></i>
          <p class="mt-2 small opacity-75">Klik <strong>Tampilkan</strong> untuk memuat data rekapitulasi.</p>
        </div>
        <div x-show="loading" x-cloak class="text-center py-5 text-muted">
          <div class="spinner-border text-primary mb-2" role="status"></div>
          <p class="small opacity-75">Memuat data rekapitulasi...</p>
        </div>

        <div x-show="loaded && !loading" x-cloak>
          {{-- Summary Cards Row --}}
          <div class="row g-4 mb-5">
            <div class="col-6 col-md-3">
              <div class="card card-grad-success h-100 p-3">
                <small class="text-body-secondary text-uppercase" style="font-size:.65rem;">Total Kehadiran</small>
                <h4 class="mb-0 fw-bold text-success" x-text="summary.total_kehadiran ?? 0"></h4>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-primary h-100 p-3">
                <small class="text-body-secondary text-uppercase" style="font-size:.65rem;">Total Absensi</small>
                <h4 class="mb-0 fw-bold text-primary" x-text="summary.total_absensi ?? 0"></h4>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-warning h-100 p-3">
                <small class="text-body-secondary text-uppercase" style="font-size:.65rem;">Rata-rata Jam Masuk</small>
                <h4 class="mb-0 fw-bold text-warning" x-text="summary.avg_jam_terawal ?? '-'"></h4>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="card card-grad-info h-100 p-3">
                <small class="text-body-secondary text-uppercase" style="font-size:.65rem;">Tingkat Konsistensi</small>
                <h4 class="mb-0 fw-bold text-info" x-text="(summary.tingkat_konsistensi ?? 0) + '%'"></h4>
              </div>
            </div>
          </div>

          {{-- Sub Tabs Navigation --}}
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3" style="border-bottom:1px solid rgba(255,255,255,.08);">
            <div class="nav nav-pills bg-label-secondary p-1 rounded-2" style="font-size:.8rem;">
              <button class="nav-link px-3 py-1.5 fw-bold rounded-2" :class="{ 'active': activeTab === 'siswa' }" @click="activeTab = 'siswa'">
                <i class="ti tabler-users me-1"></i> Rekap Per Siswa (<span x-text="filteredSiswa.length"></span>)
              </button>
              <button class="nav-link px-3 py-1.5 fw-bold rounded-2" :class="{ 'active': activeTab === 'kelas' }" @click="activeTab = 'kelas'">
                <i class="ti tabler-school me-1"></i> Rekap Per Kelas (<span x-text="filteredKelas.length"></span>)
              </button>
              <button class="nav-link px-3 py-1.5 fw-bold rounded-2" :class="{ 'active': activeTab === 'badge' }" @click="activeTab = 'badge'">
                <i class="ti tabler-award me-1"></i> Rekap Per Badge (<span x-text="badgedata.length"></span>)
              </button>
            </div>
            <div class="position-relative" style="min-width:240px;" x-show="activeTab !== 'badge'">
              <input type="text" class="form-control form-control-sm ps-5" placeholder="Cari nama / NIS / kelas..." x-model="searchQuery">
              <i class="ti tabler-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </div>
          </div>

          {{-- TAB 1: REKAP SISWA --}}
          <div x-show="activeTab === 'siswa'">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr style="background: rgba(255, 255, 255, 0.03);">
                    <th class="text-center" style="width: 60px;">RANK</th>
                    <th>SISWA</th>
                    <th>KELAS</th>
                    <th class="text-center">SKOR</th>
                    <th class="text-center">HADIR</th>
                    <th class="text-center">TERLAMBAT</th>
                    <th class="text-center">SAKIT/IZIN</th>
                    <th class="text-center">ALPHA</th>
                    <th class="text-center">RATA-RATA MASUK</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="item in paginatedSiswa" :key="item.siswa_id">
                    <tr class="rekap-row-hover">
                      <td class="text-center fw-bold fs-6" x-text="item.rank"></td>
                      <td>
                        <div class="fw-bold text-white" x-text="item.nama_lengkap"></div>
                        <div class="text-muted font-monospace small" x-text="'NIS: ' + (item.nis || '-')"></div>
                      </td>
                      <td><span class="badge bg-label-secondary" x-text="item.kelas?.nama || '-'"></span></td>
                      <td class="text-center fw-bold text-warning" x-text="item.skor"></td>
                      <td class="text-center fw-semibold text-success" x-text="item.total_hadir"></td>
                      <td class="text-center text-warning" x-text="item.total_terlambat"></td>
                      <td class="text-center text-info" x-text="(item.total_sakit + item.total_izin)"></td>
                      <td class="text-center text-danger" x-text="item.total_alpha"></td>
                      <td class="text-center font-monospace" x-text="item.avg_jam_masuk_formatted || '-'"></td>
                    </tr>
                  </template>
                  <template x-if="filteredSiswa.length === 0">
                    <tr>
                      <td colspan="9" class="text-center py-5 text-muted opacity-75">Tidak ada data rekap siswa yang cocok.</td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            {{-- Pagination Siswa --}}
            <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2" x-show="totalSiswaPages > 1">
              <div class="text-muted small">
                Menampilkan <span x-text="((siswaPage - 1) * siswaPerPage) + 1"></span> - 
                <span x-text="Math.min(siswaPage * siswaPerPage, filteredSiswa.length)"></span> dari 
                <span x-text="filteredSiswa.length"></span> siswa
              </div>
              <div class="d-flex align-items-center gap-1">
                <button class="das-page-btn" :disabled="siswaPage === 1" @click="siswaPage--"><i class="ti tabler-chevron-left"></i></button>
                <template x-for="p in siswaPaginationPages" :key="p">
                  <span>
                    <template x-if="p === '...'">
                      <span class="px-2 text-muted">...</span>
                    </template>
                    <template x-if="p !== '...'">
                      <button class="das-page-btn" :class="{ '--active': siswaPage === p }" @click="siswaPage = p" x-text="p"></button>
                    </template>
                  </span>
                </template>
                <button class="das-page-btn" :disabled="siswaPage === totalSiswaPages" @click="siswaPage++"><i class="ti tabler-chevron-right"></i></button>
              </div>
            </div>
          </div>

          {{-- TAB 2: REKAP KELAS --}}
          <div x-show="activeTab === 'kelas'">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr style="background: rgba(255, 255, 255, 0.03);">
                    <th class="text-center" style="width: 60px;">RANK</th>
                    <th>KELAS</th>
                    <th class="text-center">SISWA AKTIF</th>
                    <th class="text-center">KEHADIRAN (%)</th>
                    <th class="text-center">RATA-RATA SKOR</th>
                    <th class="text-center">TOTAL BADGE</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="item in paginatedKelas" :key="item.kelas_id">
                    <tr class="rekap-row-hover">
                      <td class="text-center fw-bold fs-6" x-text="item.rank"></td>
                      <td class="fw-bold text-white" x-text="item.nama_kelas"></td>
                      <td class="text-center" x-text="item.total_siswa"></td>
                      <td class="text-center fw-bold text-success" x-text="item.tingkat_kehadiran + '%'"></td>
                      <td class="text-center fw-bold text-warning" x-text="item.avg_skor"></td>
                      <td class="text-center" x-text="item.total_badge"></td>
                    </tr>
                  </template>
                  <template x-if="filteredKelas.length === 0">
                    <tr>
                      <td colspan="6" class="text-center py-5 text-muted opacity-75">Tidak ada data rekap kelas.</td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

          {{-- TAB 3: REKAP BADGE --}}
          <div x-show="activeTab === 'badge'">
            <div class="row g-4">
              <template x-for="b in badgedata" :key="b.badge_id">
                <div class="col-md-4 col-sm-6">
                  <div class="card card-grad-warning h-100 p-3">
                    <div class="d-flex align-items-center gap-3">
                      <div class="avatar-initial rounded bg-label-warning p-2" style="width:42px;height:42px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti fs-3" :class="b.icon || 'tabler-award'"></i>
                      </div>
                      <div>
                        <h6 class="mb-0 fw-bold text-white" x-text="b.nama_badge"></h6>
                        <small class="text-muted" x-text="b.total_penerima + ' Siswa Meraih'"></small>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection
