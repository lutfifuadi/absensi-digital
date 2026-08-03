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
                      <th class="text-center" title="Jumlah sesi hadir dibanding total sesi absensi dalam periode ini (bukan jumlah siswa)">SESI HADIR <i class="ti tabler-info-circle" style="font-size:.75rem; opacity:.55; cursor:help;"></i></th>
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
              <td class="text-center font-monospace" title="${item.total_present} sesi hadir dari ${item.total_attendance} total sesi absensi">
                ${item.total_present}
                <span class="text-muted" style="font-size:.75rem;">/ ${item.total_attendance} sesi</span>
              </td>
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

  @if(request()->routeIs('admin.gamifikasi.rekap') || request()->is('*gamifikasi/rekap*'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const el = document.getElementById('rekapitulasi-section');
      if (el) {
        setTimeout(function() {
          el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
      }
    });
  </script>
  @endif
@endsection