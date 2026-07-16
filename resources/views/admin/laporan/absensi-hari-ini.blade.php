@extends('layouts/layoutMaster')

@section('title', 'Absensi Hari Ini')

@section('page-style')
  <style>
    .form-control,
    .form-select {
      background: rgba(255, 255, 255, 0.05) !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
      color: #fff !important;
    }

    .form-control:focus,
    .form-select:focus {
      background: rgba(255, 255, 255, 0.08) !important;
      border-color: var(--bs-info) !important;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.35);
    }

    .rekap-row-hover {
      transition: background 0.15s ease;
    }

    .rekap-row-hover:hover {
      background: rgba(255, 255, 255, 0.04) !important;
    }

    /* PAGINATION */
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

    .das-page-btn:hover {
      background: rgba(255, 255, 255, 0.08);
      color: #fff;
      border-color: rgba(255, 255, 255, 0.12);
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
            <i class="ti tabler-calendar-time text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Hari Ini</h4>
          <p class="das-hero__subtitle">Audit status kehadiran siswa secara real-time hari ini.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- STATS GRID --}}
  <div class="row g-2 mb-4 row-cols-7" id="absensiStatsGrid">
    @php
      $cardBgs = [
        'info' => 'background: rgba(0, 207, 232, 0.15) !important; border: 1px solid rgba(0, 207, 232, 0.3) !important; color: #00cfe8 !important;',
        'success' => 'background: rgba(40, 199, 111, 0.15) !important; border: 1px solid rgba(40, 199, 111, 0.3) !important; color: #28c76f !important;',
        'warning' => 'background: rgba(255, 159, 67, 0.15) !important; border: 1px solid rgba(255, 159, 67, 0.3) !important; color: #ff9f43 !important;',
        'danger' => 'background: rgba(234, 84, 85, 0.15) !important; border: 1px solid rgba(234, 84, 85, 0.3) !important; color: #ea5455 !important;',
        'secondary' => 'background: rgba(168, 179, 191, 0.15) !important; border: 1px solid rgba(168, 179, 191, 0.3) !important; color: #a8b3bf !important;',
      ];
      $avatarBgs = [
        'info' => 'background: rgba(0, 207, 232, 0.25) !important; color: #00cfe8 !important;',
        'success' => 'background: rgba(40, 199, 111, 0.25) !important; color: #28c76f !important;',
        'warning' => 'background: rgba(255, 159, 67, 0.25) !important; color: #ff9f43 !important;',
        'danger' => 'background: rgba(234, 84, 85, 0.25) !important; color: #ea5455 !important;',
        'secondary' => 'background: rgba(168, 179, 191, 0.25) !important; color: #a8b3bf !important;',
      ];
    @endphp
    @foreach ([
      ['label' => 'Total Siswa',  'val' => $summary['total'],       'color' => 'info',      'icon' => 'tabler-users',              'status' => ''],
      ['label' => 'Hadir',        'val' => $summary['hadir'],       'color' => 'success',   'icon' => 'tabler-circle-check',       'status' => 'hadir'],
      ['label' => 'Terlambat',    'val' => $summary['terlambat'],   'color' => 'warning',   'icon' => 'tabler-clock',              'status' => 'terlambat'],
      ['label' => 'Sakit',        'val' => $summary['sakit'],       'color' => 'info',      'icon' => 'tabler-stethoscope',        'status' => 'sakit'],
      ['label' => 'Izin',         'val' => $summary['izin'],        'color' => 'warning',   'icon' => 'tabler-file-description',   'status' => 'izin'],
      ['label' => 'Alpha',        'val' => $summary['alpha'],       'color' => 'danger',    'icon' => 'tabler-circle-x',           'status' => 'alpha'],
      ['label' => 'Belum Absen',  'val' => $summary['belum_absen'],'color' => 'secondary', 'icon' => 'tabler-circle-minus',       'status' => 'belum_absen'],
    ] as $stat)
      <div class="col">
        <div class="card text-center h-100 border-0 shadow-sm stat-card" style="cursor: pointer; {{ $cardBgs[$stat['color']] ?? '' }}" data-status="{{ $stat['status'] }}">
          <div class="card-body p-2 d-flex flex-column align-items-center justify-content-center">
            <div class="avatar avatar-xs mb-1">
              <span class="avatar-initial rounded" style="width: 24px; height: 24px; min-width: 24px; line-height: 24px; {{ $avatarBgs[$stat['color']] ?? '' }}">
                <i class="ti {{ $stat['icon'] }}" style="font-size: 0.8rem;"></i>
              </span>
            </div>
            <h5 class="mb-0 fw-bold" style="font-size: 1rem; color: inherit !important;">{{ $stat['val'] }}</h5>
            <small class="opacity-75" style="font-size: 0.65rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">{{ $stat['label'] }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- FILTER CARD --}}
  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">Cari Siswa</label>
          <input type="text" name="search" class="form-control"
            placeholder="Nama / NIS…" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Filter Kelas</label>
          <select name="kelas_id" class="form-select">
            <option value="">Semua Kelas</option>
            @foreach ($kelasOptions as $k)
              <option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">Status Absen</label>
          <select name="status" class="form-select">
            <option value="">Semua Status</option>
            <option value="hadir"       @selected(request('status') === 'hadir')>Hadir</option>
            <option value="terlambat"   @selected(request('status') === 'terlambat')>Terlambat</option>
            <option value="sakit"       @selected(request('status') === 'sakit')>Sakit</option>
            <option value="izin"        @selected(request('status') === 'izin')>Izin</option>
            <option value="alpha"       @selected(request('status') === 'alpha')>Alpha</option>
            <option value="belum_absen" @selected(request('status') === 'belum_absen')>Belum Absen</option>
          </select>
        </div>
        <div class="col-md-2">
          <div class="d-flex gap-2">
            <button type="submit" class="btn das-btn --info w-100">
              <i class="ti tabler-search me-1"></i> Cari
            </button>
            <a href="{{ request()->url() }}" class="btn das-btn --secondary" title="Reset">
              <i class="ti tabler-refresh"></i>
            </a>
          </div>
        </div>
      </form>
    </div>
  </div>

  {{-- TABLE CARD --}}
  <div class="das-panel mb-4" id="absensiTableCard">
    <div class="das-panel__head border-bottom py-3 px-4"
      style="border-color: rgba(255,255,255,0.08) !important;">
      <h6 class="das-panel__title mb-0">
        <span class="das-panel__icon-dot --info"></span> Daftar Siswa
      </h6>
      <span class="das-chip --info">{{ $siswa->total() }} Siswa</span>
    </div>
    <div class="das-panel__body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="color:inherit;">
          <thead style="background:rgba(255,255,255,0.02); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
            <tr>
              <th class="ps-4 py-3" width="60">#</th>
              <th class="py-3">Nama Lengkap</th>
              <th class="py-3">NIS</th>
              <th class="py-3">Kelas</th>
              <th class="py-3">Jam Masuk</th>
              <th class="py-3">Jam Pulang</th>
              <th class="py-3 text-center">Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($siswa as $row)
              @php
                $absensi = $row->absensi->first();
                $status  = $absensi ? $absensi->status : 'belum_absen';
                $scolor  = match($status) {
                    'hadir'      => 'success',
                    'terlambat'  => 'warning',
                    'sakit'      => 'info',
                    'izin'       => 'warning',
                    'alpha'      => 'danger',
                    default      => 'secondary',
                };
              @endphp
              <tr class="rekap-row-hover">
                <td class="ps-4 text-white-50 small">{{ $loop->iteration + ($siswa->currentPage() - 1) * $siswa->perPage() }}</td>
                <td><span class="fw-bold text-white">{{ $row->nama_lengkap }}</span></td>
                <td><span class="text-white-50 small">{{ $row->nis ?? '-' }}</span></td>
                <td><span class="das-chip --secondary">{{ $row->kelas->nama ?? '-' }}</span></td>
                <td>
                  @if($absensi && $absensi->jam_masuk)
                    <code class="text-info">{{ $absensi->jam_masuk }}</code>
                  @else
                    <span class="text-white-50">—</span>
                  @endif
                </td>
                <td>
                  @if($absensi && $absensi->jam_pulang)
                    <code class="text-info">{{ $absensi->jam_pulang }}</code>
                  @else
                    <span class="text-white-50">—</span>
                  @endif
                </td>
                <td class="text-center">
                  <span class="das-chip --{{ $scolor }}">
                    {{ $status === 'belum_absen' ? 'Belum Absen' : ucfirst($status) }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <div class="opacity-50">
                    <i class="ti tabler-users-minus fs-1 d-block mb-2"></i>
                    <span class="small">Tidak ada data siswa ditemukan.</span>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if($siswa->hasPages())
      <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
        {{ $siswa->links('vendor.pagination.users') }}
      </div>
    @endif
  </div>



@section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.querySelector('.das-panel form');
      if (!form) return;

      const searchInput = form.querySelector('input[name="search"]');
      const kelasSelect = form.querySelector('select[name="kelas_id"]');
      const statusSelect = form.querySelector('select[name="status"]');
      const tableCard = document.getElementById('absensiTableCard');
      const statsGrid = document.getElementById('absensiStatsGrid');
      const resetButton = form.querySelector('a[title="Reset"]');

      let currentUrl = window.location.search ? window.location.pathname + window.location.search : window.location.pathname;
      let searchTimeout;
      let lastSearch = searchInput.value.trim();

      function fetchData(url, isPolling = false) {
        if (!isPolling && tableCard) {
          tableCard.style.opacity = '0.5';
          tableCard.style.pointerEvents = 'none';
        }

        currentUrl = url;

        fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
        .then(res => res.text())
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newCard = doc.getElementById('absensiTableCard');
          const newGrid = doc.getElementById('absensiStatsGrid');
          if (tableCard && newCard) {
            tableCard.innerHTML = newCard.innerHTML;
          }
          if (statsGrid && newGrid) {
            statsGrid.innerHTML = newGrid.innerHTML;
          }
        })
        .catch(err => {
          console.error('Fetch error:', err);
        })
        .finally(() => {
          if (!isPolling && tableCard) {
            tableCard.style.opacity = '1';
            tableCard.style.pointerEvents = 'auto';
          }
        });
      }

      function triggerSearch() {
        const params = new URLSearchParams();
        const searchValue = searchInput.value.trim();

        if (searchValue.length >= 2) {
          params.append('search', searchValue);
        }

        if (kelasSelect.value) {
          params.append('kelas_id', kelasSelect.value);
        }
        if (statusSelect.value) {
          params.append('status', statusSelect.value);
        }

        const url = `${window.location.pathname}?${params.toString()}`;
        fetchData(url);
      }

      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const val = searchInput.value.trim();
        if (val.length >= 2 || val.length === 0) {
          if (val !== lastSearch) {
            lastSearch = val;
            searchTimeout = setTimeout(triggerSearch, 400);
          }
        } else if (lastSearch !== '') {
          lastSearch = '';
          searchTimeout = setTimeout(triggerSearch, 400);
        }
      });

      kelasSelect.addEventListener('change', triggerSearch);
      statusSelect.addEventListener('change', triggerSearch);

      form.addEventListener('submit', function(e) {
        e.preventDefault();
        triggerSearch();
      });

      if (resetButton) {
        resetButton.addEventListener('click', function(e) {
          e.preventDefault();
          form.reset();
          searchInput.value = '';
          kelasSelect.value = '';
          statusSelect.value = '';
          lastSearch = '';
          triggerSearch();
        });
      }

      if (tableCard) {
        tableCard.addEventListener('click', function(e) {
          const link = e.target.closest('a.das-page-btn');
          if (link) {
            e.preventDefault();
            fetchData(link.href);
          }
        });
      }

      document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', function() {
          const status = this.getAttribute('data-status');
          statusSelect.value = status;
          triggerSearch();
        });
      });

    });
  </script>
@endsection
@endsection
