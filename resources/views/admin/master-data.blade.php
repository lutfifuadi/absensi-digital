@extends('layouts/layoutMaster')

@section('title', 'Master Data')

@section('page-style')
  <style>
    /* Premium Glassmorphism Variables & Style */
    :root {
      --glass-bg: rgba(15, 23, 42, 0.45);
      --glass-border: rgba(255, 255, 255, 0.08);
      --glass-glow-primary: rgba(115, 103, 240, 0.15);
      --glass-glow-success: rgba(40, 199, 111, 0.15);
      --glass-glow-info: rgba(0, 207, 232, 0.15);
      --glass-glow-warning: rgba(255, 159, 67, 0.15);
      --glass-glow-danger: rgba(234, 84, 85, 0.15);
    }

    /* Glass Card Standard */
    .glass-card-mewah {
      background: var(--glass-bg) !important;
      backdrop-filter: blur(16px) saturate(180%) !important;
      -webkit-backdrop-filter: blur(16px) saturate(180%) !important;
      border: 1px solid var(--glass-border) !important;
      border-radius: 4px !important;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
      position: relative;
      overflow: hidden;
    }

    .glass-card-mewah::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.05) 0%, transparent 100%);
      pointer-events: none;
      z-index: 1;
    }

    /* Metric Cards Accent & Hover Effect */
    .metric-card {
      padding: 1.5rem;
    }
    
    .metric-card::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 5%;
      width: 90%;
      height: 3px;
      border-radius: 4px 4px 0 0;
      opacity: 0.6;
      transition: all 0.3s ease;
    }

    .metric-card-primary::after { background: var(--das-primary); }
    .metric-card-success::after { background: var(--das-success); }
    .metric-card-info::after { background: var(--das-info); }
    .metric-card-warning::after { background: var(--das-warning); }

    .metric-card:hover {
      transform: translateY(-6px);
      border-color: rgba(255, 255, 255, 0.18) !important;
      box-shadow: 0 12px 30px -10px rgba(0, 0, 0, 0.5), 0 0 20px 2px var(--hover-glow) !important;
    }

    .metric-card-primary:hover { --hover-glow: var(--glass-glow-primary); }
    .metric-card-success:hover { --hover-glow: var(--glass-glow-success); }
    .metric-card-info:hover { --hover-glow: var(--glass-glow-info); }
    .metric-card-warning:hover { --hover-glow: var(--glass-glow-warning); }

    /* Search Dropdown / Overlay */
    .search-overlay-box {
      position: absolute;
      top: calc(100% + 8px);
      left: 0;
      right: 0;
      z-index: 1050;
      background: rgba(15, 22, 42, 0.92) !important;
      backdrop-filter: blur(20px) saturate(190%) !important;
      -webkit-backdrop-filter: blur(20px) saturate(190%) !important;
      border: 1px solid rgba(255, 255, 255, 0.12) !important;
      border-radius: 4px;
      box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8), 0 0 25px 2px rgba(115, 103, 240, 0.15);
      max-height: 450px;
      overflow-y: auto;
    }

    /* Glowing Title */
    .text-gradient-gold-luxury {
      background: linear-gradient(135deg, #ffffff 10%, #ffd700 60%, #ff8c00 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      text-shadow: 0 0 30px rgba(255, 215, 0, 0.15);
    }

    /* Sub-menu Navigation Links */
    .sub-item-link-glass {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 16px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      border: 1px solid rgba(255, 255, 255, 0.05);
      background: rgba(255, 255, 255, 0.02);
      color: rgba(255, 255, 255, 0.7);
    }

    .sub-item-link-glass:hover {
      background: rgba(255, 255, 255, 0.07);
      border-color: rgba(255, 255, 255, 0.15);
      color: #fff;
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.1);
    }

    /* Pulsing Badge & Dot */
    .pulsing-badge-danger {
      position: relative;
      display: inline-flex;
    }

    .pulsing-badge-danger::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      border-radius: 4px !important;
      background: var(--das-danger);
      opacity: 0.8;
      animation: pulse-glow 1.5s infinite ease-in-out;
      z-index: -1;
    }

    @keyframes pulse-glow {
      0% {
        transform: scale(0.95);
        opacity: 0.8;
      }
      50% {
        transform: scale(1.25);
        opacity: 0;
      }
      100% {
        transform: scale(0.95);
        opacity: 0;
      }
    }

    .pulse-dot-active {
      display: inline-block;
      width: 8px;
      height: 8px;
      background-color: var(--das-success);
      border-radius: 50%;
      box-shadow: 0 0 8px var(--das-success);
      animation: pulse-dot-anim 2s infinite;
    }

    @keyframes pulse-dot-anim {
      0% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7); }
      70% { box-shadow: 0 0 0 6px rgba(40, 199, 111, 0); }
      100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0); }
    }

    /* Custom Scrollbar for Search Box */
    .search-overlay-box::-webkit-scrollbar {
      width: 6px;
    }
    .search-overlay-box::-webkit-scrollbar-track {
      background: transparent;
    }
    .search-overlay-box::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 3px;
    }
    .search-overlay-box::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.25);
    }

    /* Avatar and Badge alignment & radius overrides */
    .avatar, 
    .avatar-initial, 
    .avatar-initial.rounded, 
    .avatar.rounded, 
    .avatar-initial.rounded-circle, 
    .avatar.rounded-circle,
    .badge {
      border-radius: 4px !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      text-align: center !important;
    }
    
    .avatar i, 
    .avatar-initial i, 
    .badge i {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      vertical-align: middle !important;
      line-height: 1 !important;
    }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO SECTION & INTEGRATED LIVE SEARCH
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4 position-relative" style="min-height: 220px; overflow: visible;">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass" style="backdrop-filter: blur(12px) saturate(180%); background: rgba(15, 23, 42, 0.45); border: 1px solid rgba(255, 255, 255, 0.08);"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner p-4 p-md-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4" style="z-index: 2; position: relative;">
      <div class="das-hero__identity d-flex align-items-center gap-3">
        <div class="das-hero__logo-wrapper" style="width: 64px; height: 64px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; position: relative;">
          <i class="ti tabler-database fs-2 text-warning" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          <div class="das-hero__logo-glow" style="position: absolute; width: 100%; height: 100%; top: 0; left: 0; background: radial-gradient(circle, rgba(255, 215, 0, 0.2) 0%, transparent 70%); pointer-events: none; border-radius: 4px !important;"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge d-inline-flex align-items-center justify-content-center gap-2 mb-2" style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1); padding: 4px 12px; border-radius: 4px !important; font-size: 0.75rem; color: rgba(255,255,255,0.85); display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
            <span class="pulse-dot-active"></span>
            Control Center
          </div>
          <h3 class="das-hero__title text-gradient-gold-luxury mb-1 fw-bold">Master Data</h3>
          <p class="das-hero__subtitle text-white-50 mb-0 small" style="max-width: 480px;">Kelola dan pantau seluruh data inti akademik dan logistik sekolah dari satu pusat kendali.</p>
        </div>
      </div>

      {{-- Search Area (Alpine.js State Management) --}}
      <div class="das-hero__search flex-grow-1" style="max-width: 450px; position: relative;" x-data="{
          searchQuery: '',
          searchResults: null,
          loading: false,
          showResults: false,
          search() {
              if (this.searchQuery.trim() === '') {
                  this.searchResults = null;
                  this.showResults = false;
                  return;
              }
              this.loading = true;
              this.showResults = true;
              fetch(`/admin/master-data/search?q=${encodeURIComponent(this.searchQuery)}`)
                  .then(res => res.json())
                  .then(data => {
                      this.searchResults = data;
                      this.loading = false;
                  })
                  .catch(err => {
                      console.error(err);
                      this.loading = false;
                  });
          },
          clear() {
              this.searchQuery = '';
              this.searchResults = null;
              this.showResults = false;
          }
      }" @click.away="showResults = false">
        <div class="input-group input-group-merge shadow-lg" style="border-radius: 4px !important; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);">
          <span class="input-group-text bg-transparent border-0 text-white-50 px-3"><i class="ti tabler-search" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i></span>
          <input 
            type="text" 
            class="form-control bg-transparent border-0 text-white placeholder-light px-2" 
            placeholder="Cari Siswa, Guru, Kelas, atau Mapel..." 
            style="box-shadow: none;"
            x-model="searchQuery" 
            @input.debounce.300ms="search()"
            @focus="if(searchQuery.trim() !== '') showResults = true"
          >
          <span class="input-group-text bg-transparent border-0 px-3 cursor-pointer" x-show="searchQuery !== ''" @click="clear()" style="display: none;">
            <i class="ti tabler-x text-white-50" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          </span>
          <span class="input-group-text bg-transparent border-0 px-3" x-show="loading" style="display: none;">
            <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
          </span>
        </div>

        {{-- Live Search Results Box --}}
        <div class="search-overlay-box p-3" x-show="showResults" x-transition style="display: none; border-radius: 4px !important;">
          <template x-if="searchResults && (searchResults.siswa.length === 0 && searchResults.guru.length === 0 && searchResults.kelas.length === 0 && searchResults.mapel.length === 0)">
            <div class="text-center py-4">
              <i class="ti tabler-search-off fs-1 text-muted mb-2 d-block" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
              <span class="text-white-50 small">Tidak ditemukan hasil untuk "<strong class="text-white" x-text="searchQuery"></strong>"</span>
            </div>
          </template>

          <template x-if="searchResults">
            <div class="d-flex flex-column gap-3">
              {{-- Category: Siswa --}}
              <template x-if="searchResults.siswa && searchResults.siswa.length > 0">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2 text-primary border-bottom border-secondary border-opacity-10 pb-1">
                    <i class="ti tabler-users fs-5" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                    <span class="fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Siswa</span>
                  </div>
                  <div class="list-group list-group-flush gap-1">
                    <template x-for="item in searchResults.siswa" :key="item.id">
                      <a :href="`/admin/siswa/${item.id}`" class="list-group-item list-group-item-action bg-transparent border-0 p-2 text-white d-flex justify-content-between align-items-center sub-item-link-glass" style="border-radius: 4px !important;">
                        <div>
                          <div class="fw-semibold text-white" x-text="item.nama_lengkap"></div>
                          <div class="text-white-50" style="font-size: 0.75rem;">NIS: <span x-text="item.nis || '-'"></span> | NISN: <span x-text="item.nisn || '-'"></span></div>
                        </div>
                        <i class="ti tabler-chevron-right text-muted" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                      </a>
                    </template>
                  </div>
                </div>
              </template>

              {{-- Category: Guru --}}
              <template x-if="searchResults.guru && searchResults.guru.length > 0">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2 text-success border-bottom border-secondary border-opacity-10 pb-1">
                    <i class="ti tabler-school fs-5" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                    <span class="fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Guru</span>
                  </div>
                  <div class="list-group list-group-flush gap-1">
                    <template x-for="item in searchResults.guru" :key="item.id">
                      <a :href="`/admin/guru/${item.id}`" class="list-group-item list-group-item-action bg-transparent border-0 p-2 text-white d-flex justify-content-between align-items-center sub-item-link-glass" style="border-radius: 4px !important;">
                        <div>
                          <div class="fw-semibold text-white" x-text="item.nama_lengkap"></div>
                          <div class="text-white-50" style="font-size: 0.75rem;">NIP: <span x-text="item.nip || '-'"></span></div>
                        </div>
                        <i class="ti tabler-chevron-right text-muted" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                      </a>
                    </template>
                  </div>
                </div>
              </template>

              {{-- Category: Kelas --}}
              <template x-if="searchResults.kelas && searchResults.kelas.length > 0">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2 text-info border-bottom border-secondary border-opacity-10 pb-1">
                    <i class="ti tabler-door fs-5" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                    <span class="fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Kelas</span>
                  </div>
                  <div class="list-group list-group-flush gap-1">
                    <template x-for="item in searchResults.kelas" :key="item.id">
                      <a :href="`/admin/kelas/${item.id}`" class="list-group-item list-group-item-action bg-transparent border-0 p-2 text-white d-flex justify-content-between align-items-center sub-item-link-glass" style="border-radius: 4px !important;">
                        <div>
                          <div class="fw-semibold text-white" x-text="item.nama"></div>
                        </div>
                        <i class="ti tabler-chevron-right text-muted" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                      </a>
                    </template>
                  </div>
                </div>
              </template>

              {{-- Category: Mapel --}}
              <template x-if="searchResults.mapel && searchResults.mapel.length > 0">
                <div>
                  <div class="d-flex align-items-center gap-2 mb-2 text-warning border-bottom border-secondary border-opacity-10 pb-1">
                    <i class="ti tabler-books fs-5" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                    <span class="fw-bold text-uppercase tracking-wider" style="font-size: 0.72rem;">Mata Pelajaran</span>
                  </div>
                  <div class="list-group list-group-flush gap-1">
                    <template x-for="item in searchResults.mapel" :key="item.id">
                      <a :href="`/admin/mapel`" class="list-group-item list-group-item-action bg-transparent border-0 p-2 text-white d-flex justify-content-between align-items-center sub-item-link-glass" style="border-radius: 4px !important;">
                        <div>
                          <div class="fw-semibold text-white" x-text="item.nama_mapel"></div>
                          <div class="text-white-50" style="font-size: 0.75rem;">Kode: <span x-text="item.kode_mapel || '-'"></span></div>
                        </div>
                        <i class="ti tabler-chevron-right text-muted" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
                      </a>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       QUICK ACTIONS AREA
  ═══════════════════════════════════════════════════════ --}}
  <div class="glass-card-mewah mb-4 p-3 d-flex flex-wrap align-items-center gap-3" style="border-radius: 4px !important;">
    <div class="text-white-50 small fw-bold text-uppercase tracking-wider me-2" style="font-size: 0.7rem;">
      <i class="ti tabler-bolt text-warning me-1"></i> Quick Add:
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="{{ route('admin.siswa.create') }}" class="btn btn-sm btn-label-primary d-flex align-items-center gap-1" style="border-radius: 4px !important;">
        <i class="ti tabler-user-plus" style="font-size: 0.9rem; display: flex !important; align-items: center !important; justify-content: center !important;"></i> Siswa
      </a>
      <a href="{{ route('admin.guru.create') }}" class="btn btn-sm btn-label-success d-flex align-items-center gap-1" style="border-radius: 4px !important;">
        <i class="ti tabler-circle-plus" style="font-size: 0.9rem; display: flex !important; align-items: center !important; justify-content: center !important;"></i> Guru
      </a>
      <a href="{{ route('admin.kelas.create') }}" class="btn btn-sm btn-label-info d-flex align-items-center gap-1" style="border-radius: 4px !important;">
        <i class="ti tabler-square-plus" style="font-size: 0.9rem; display: flex !important; align-items: center !important; justify-content: center !important;"></i> Kelas
      </a>
      <a href="{{ route('admin.upload-massal.index') }}" class="btn btn-sm btn-label-warning d-flex align-items-center gap-1" style="border-radius: 4px !important;">
        <i class="ti tabler-cloud-upload" style="font-size: 0.9rem; display: flex !important; align-items: center !important; justify-content: center !important;"></i> Import Data
      </a>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       METRICS CARDS GRID
  ═══════════════════════════════════════════════════════ --}}
  <div class="row g-4 mb-5">
    {{-- Card Siswa --}}
    <div class="col-sm-6 col-xl-3">
      <div class="glass-card-mewah metric-card metric-card-primary h-100" style="border-radius: 4px !important;">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <span class="text-white-50 small text-uppercase tracking-wider fw-semibold">Total Siswa</span>
            <h2 class="mb-0 text-white fw-bold mt-1" style="font-size: 2.2rem; letter-spacing: -1px;">{{ number_format($totalSiswa) }}</h2>
          </div>
          <span class="avatar avatar-md bg-label-primary shadow-sm" style="width: 45px; height: 45px; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
            <i class="ti tabler-users fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          </span>
        </div>
        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
          <span class="text-white-50 small">Siswa Aktif Terdaftar</span>
          <a href="{{ route('admin.siswa.index') }}" class="text-primary text-decoration-none small fw-semibold d-flex align-items-center gap-1">
            Kelola <i class="ti tabler-arrow-right" style="font-size: 0.8rem;"></i>
          </a>
        </div>
      </div>
    </div>

    {{-- Card Guru --}}
    <div class="col-sm-6 col-xl-3">
      <div class="glass-card-mewah metric-card metric-card-success h-100" style="border-radius: 4px !important;">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <span class="text-white-50 small text-uppercase tracking-wider fw-semibold">Total Guru</span>
            <h2 class="mb-0 text-white fw-bold mt-1" style="font-size: 2.2rem; letter-spacing: -1px;">{{ number_format($totalGuru) }}</h2>
          </div>
          <span class="avatar avatar-md bg-label-success shadow-sm" style="width: 45px; height: 45px; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
            <i class="ti tabler-school fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          </span>
        </div>
        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
          <span class="text-white-50 small">Pendidik Aktif</span>
          <a href="{{ route('admin.guru.index') }}" class="text-success text-decoration-none small fw-semibold d-flex align-items-center gap-1">
            Kelola <i class="ti tabler-arrow-right" style="font-size: 0.8rem;"></i>
          </a>
        </div>
      </div>
    </div>

    {{-- Card Kelas --}}
    <div class="col-sm-6 col-xl-3">
      <div class="glass-card-mewah metric-card metric-card-info h-100" style="border-radius: 4px !important;">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <span class="text-white-50 small text-uppercase tracking-wider fw-semibold">Total Kelas</span>
            <h2 class="mb-0 text-white fw-bold mt-1" style="font-size: 2.2rem; letter-spacing: -1px;">{{ number_format($totalKelas) }}</h2>
          </div>
          <span class="avatar avatar-md bg-label-info shadow-sm" style="width: 45px; height: 45px; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
            <i class="ti tabler-door fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          </span>
        </div>
        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
          <span class="text-white-50 small">Rombongan Belajar</span>
          <a href="{{ route('admin.kelas.index') }}" class="text-info text-decoration-none small fw-semibold d-flex align-items-center gap-1">
            Kelola <i class="ti tabler-arrow-right" style="font-size: 0.8rem;"></i>
          </a>
        </div>
      </div>
    </div>

    {{-- Card Mapel --}}
    <div class="col-sm-6 col-xl-3">
      <div class="glass-card-mewah metric-card metric-card-warning h-100" style="border-radius: 4px !important;">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <span class="text-white-50 small text-uppercase tracking-wider fw-semibold">Mata Pelajaran</span>
            <h2 class="mb-0 text-white fw-bold mt-1" style="font-size: 2.2rem; letter-spacing: -1px;">{{ number_format($totalMapel) }}</h2>
          </div>
          <span class="avatar avatar-md bg-label-warning shadow-sm" style="width: 45px; height: 45px; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
            <i class="ti tabler-books fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
          </span>
        </div>
        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-10 mt-2">
          <span class="text-white-50 small">Mapel Aktif</span>
          <a href="{{ route('admin.mapel.index') }}" class="text-warning text-decoration-none small fw-semibold d-flex align-items-center gap-1">
            Kelola <i class="ti tabler-arrow-right" style="font-size: 0.8rem;"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       CATEGORIZED NAVIGATION SECTIONS
  ═══════════════════════════════════════════════════════ --}}
  
  {{-- 1. Data Akademik --}}
  <div class="section-label text-white-50 mb-3 fw-bold tracking-wider" style="font-size: 0.75rem; text-transform: uppercase;">
    <i class="ti tabler-books text-warning me-1"></i> Data Akademik
  </div>
  <div class="row g-4 mb-5">
    @php
      $akademik = [
        ['title' => 'Tahun Ajaran', 'desc' => 'Kelola tahun ajaran dan status aktif.', 'icon' => 'tabler-calendar-stats', 'color' => 'warning', 'route' => route('admin.tahun-akademik.index')],
        ['title' => 'Master Jurusan', 'desc' => 'Kelola program keahlian / jurusan sekolah.', 'icon' => 'tabler-books', 'color' => 'success', 'route' => route('admin.jurusan.index')],
        ['title' => 'Kelas', 'desc' => 'Kelola rombongan belajar dan wali kelas.', 'icon' => 'tabler-door', 'color' => 'info', 'route' => route('admin.kelas.index')],
        ['title' => 'Jadwal Pelajaran', 'desc' => 'Atur jadwal pelajaran per kelas and guru.', 'icon' => 'tabler-calendar-time', 'color' => 'primary', 'route' => route('admin.jadwal.index')],
        ['title' => 'Data Kegiatan Khusus', 'desc' => 'Kelola kegiatan khusus, ujian, and ekstrakurikuler.', 'icon' => 'tabler-calendar-event', 'color' => 'secondary', 'route' => route('admin.kegiatan.index')],
      ];
    @endphp

    @foreach ($akademik as $item)
      <div class="col-sm-6 col-xl-4">
        <a href="{{ $item['route'] }}" class="text-decoration-none h-100 d-block">
          <div class="glass-card-mewah h-100 p-4" style="border-radius: 4px !important;">
            <div class="avatar avatar-md mb-3" style="width: 42px; height: 42px;">
              <span class="avatar-initial bg-label-{{ $item['color'] }} shadow-sm" style="border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
                <i class="ti {{ $item['icon'] }} fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
              </span>
            </div>
            <h6 class="mb-1 text-white fw-bold">{{ $item['title'] }}</h6>
            <p class="text-white-50 mb-3 small" style="min-height: 38px;">{{ $item['desc'] }}</p>
            <span class="badge bg-label-{{ $item['color'] }} d-inline-flex align-items-center justify-content-center gap-1" style="font-size: 0.75rem; border-radius: 4px !important; padding: 6px 10px; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
              Buka Modul <i class="ti tabler-arrow-right ms-1" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
            </span>
          </div>
        </a>
      </div>
    @endforeach
  </div>

  {{-- 2. Data Pengguna --}}
  <div class="section-label text-white-50 mb-3 fw-bold tracking-wider" style="font-size: 0.75rem; text-transform: uppercase;">
    <i class="ti tabler-users text-primary me-1"></i> Data Pengguna
  </div>
  <div class="row g-4 mb-5">
    @php
      $pengguna = [
        ['title' => 'Siswa', 'desc' => 'Kelola biodata, kelas, dan QR code siswa.', 'icon' => 'tabler-users', 'color' => 'primary', 'route' => route('admin.siswa.index'), 'btn' => 'Kelola Siswa'],
        ['title' => 'Guru', 'desc' => 'Kelola biodata guru dan QR code absensi.', 'icon' => 'tabler-school', 'color' => 'success', 'route' => route('admin.guru.index'), 'btn' => 'Kelola Guru'],
        ['title' => 'Wali Kelas', 'desc' => 'Kelola biodata wali kelas dan QR code absensi.', 'icon' => 'tabler-users-group', 'color' => 'info', 'route' => route('admin.wali-kelas.index'), 'btn' => 'Kelola Wali Kelas'],
        ['title' => 'Staff TU', 'desc' => 'Kelola biodata staff dan QR code absensi.', 'icon' => 'tabler-briefcase', 'color' => 'warning', 'route' => route('admin.staff-tata-usaha.index'), 'btn' => 'Kelola Staff TU'],
        ['title' => 'Role', 'desc' => 'Kelola role sistem dan lihat jumlah user tiap role.', 'icon' => 'tabler-shield-check', 'color' => 'secondary', 'route' => route('admin.role.index'), 'btn' => 'Kelola Role'],
      ];
    @endphp

    @foreach ($pengguna as $item)
      <div class="col-sm-6 col-xl-4">
        <div class="glass-card-mewah h-100 p-4" style="border-radius: 4px !important;">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="avatar avatar-md" style="width: 42px; height: 42px;">
              <span class="avatar-initial bg-label-{{ $item['color'] }} shadow-sm" style="border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
                <i class="ti {{ $item['icon'] }} fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
              </span>
            </div>
            <div>
              <h6 class="mb-0 text-white fw-bold">{{ $item['title'] }}</h6>
              <small class="text-white-50" style="font-size: 0.75rem;">Manajemen {{ $item['title'] }}</small>
            </div>
          </div>
          <p class="text-white-50 small mb-3" style="min-height: 38px;">{{ $item['desc'] }}</p>
          <a href="{{ $item['route'] }}" class="sub-item-link-glass text-white w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 4px !important;">
            <i class="ti tabler-external-link text-{{ $item['color'] }}" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> {{ $item['btn'] }}
          </a>
        </div>
      </div>
    @endforeach
  </div>

  {{-- 3. Absensi & Pelaporan --}}
  <div class="section-label text-white-50 mb-3 fw-bold tracking-wider" style="font-size: 0.75rem; text-transform: uppercase;">
    <i class="ti tabler-clipboard-check text-danger me-1"></i> Absensi & Pelaporan
  </div>
  <div class="row g-4">
    {{-- Absensi --}}
    <div class="col-sm-6 col-xl-4">
      <div class="glass-card-mewah h-100 p-4" style="border-radius: 4px !important;">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar avatar-md" style="width: 42px; height: 42px;">
            <span class="avatar-initial bg-label-danger shadow-sm" style="border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
              <i class="ti tabler-clipboard-check fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
            </span>
          </div>
          <div>
            <h6 class="mb-0 text-white fw-bold">Absensi</h6>
            <small class="text-white-50" style="font-size: 0.75rem;">Log Kehadiran</small>
          </div>
        </div>
        <p class="text-white-50 small mb-3">Akses data absensi harian seluruh entitas.</p>
        <div class="d-flex flex-column gap-2">
          <a href="{{ route('admin.absensi-siswa.index') }}" class="sub-item-link-glass text-white" style="border-radius: 4px !important;">
            <i class="ti tabler-users text-primary" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> Absensi Siswa
          </a>
          <a href="{{ route('admin.absensi-guru.index') }}" class="sub-item-link-glass text-white" style="border-radius: 4px !important;">
            <i class="ti tabler-user-check text-success" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> Absensi Guru
          </a>
          <a href="{{ route('admin.absensi-staff.index') }}" class="sub-item-link-glass text-white" style="border-radius: 4px !important;">
            <i class="ti tabler-briefcase text-warning" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> Absensi Staff TU
          </a>
        </div>
      </div>
    </div>

    {{-- Izin & Sakit (with Red Pulsing Badge if pending > 0) --}}
    <div class="col-sm-6 col-xl-4">
      <div class="glass-card-mewah h-100 p-4" style="border-radius: 4px !important;">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar avatar-md" style="width: 42px; height: 42px;">
            <span class="avatar-initial bg-label-info shadow-sm" style="border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
              <i class="ti tabler-stethoscope fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
            </span>
          </div>
          <div>
            <h6 class="mb-0 text-white fw-bold">Izin & Sakit</h6>
            <small class="text-white-50" style="font-size: 0.75rem;">Pengajuan & Verifikasi</small>
          </div>
        </div>
        <p class="text-white-50 small mb-3">Verifikasi berkas pengajuan izin dan sakit.</p>
        <a href="{{ route('admin.izin-sakit.index') }}" class="sub-item-link-glass text-white w-100 mt-2 d-flex justify-content-between align-items-center" style="border-radius: 4px !important;">
          <span class="d-flex align-items-center gap-2">
            <i class="ti tabler-medical-cross text-info" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> Kelola Izin & Sakit
          </span>
          @if ($pendingIzinCount > 0)
            <span class="badge bg-danger pulsing-badge-danger fw-bold" style="padding: 5px 8px; font-size: 0.7rem; border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
              {{ $pendingIzinCount }} Pending
            </span>
          @endif
        </a>
      </div>
    </div>

    {{-- Laporan --}}
    <div class="col-sm-6 col-xl-4">
      <div class="glass-card-mewah h-100 p-4" style="border-radius: 4px !important;">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="avatar avatar-md" style="width: 42px; height: 42px;">
            <span class="avatar-initial bg-label-secondary shadow-sm" style="border-radius: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; text-align: center !important;">
              <i class="ti tabler-chart-bar fs-4" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i>
            </span>
          </div>
          <div>
            <h6 class="mb-0 text-white fw-bold">Laporan & Rekap</h6>
            <small class="text-white-50" style="font-size: 0.75rem;">Analitik & Export</small>
          </div>
        </div>
        <p class="text-white-50 small mb-3">Generate laporan kehadiran bulanan.</p>
        <a href="{{ route('admin.laporan.index') }}" class="sub-item-link-glass text-white w-100 mt-2" style="border-radius: 4px !important;">
          <i class="ti tabler-table-export text-secondary" style="display: flex !important; align-items: center !important; justify-content: center !important;"></i> Buka Modul Laporan
        </a>
      </div>
    </div>
  </div>

@endsection
