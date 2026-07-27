@extends('layouts/layoutMaster')

@section('title', 'Absensi Cepat — Bulk Input')

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

    .absensi-radios .btn-check:checked + .btn {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }

    .student-row:hover {
      background: rgba(255, 255, 255, 0.02) !important;
    }

    /* Search Input */
    #search_nama::placeholder {
      color: rgba(255, 255, 255, 0.35);
    }
    #search_nama:focus {
      outline: none;
      box-shadow: 0 0 0 2px rgba(0, 207, 232, 0.2);
      border-color: rgba(0, 207, 232, 0.5) !important;
      background: rgba(255, 255, 255, 0.08) !important;
    }
    .student-row.hidden-search {
      display: none;
    }

    /* Search Dropdown */
    .search-item {
      padding: 10px 14px;
      cursor: pointer;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      transition: background 0.15s;
    }
    .search-item:last-child { border-bottom: none; }
    .search-item:hover, .search-item.active {
      background: rgba(0, 207, 232, 0.12);
    }
    .search-item .si-name {
      color: #fff;
      font-weight: 600;
      font-size: 0.88rem;
    }
    .search-item .si-meta {
      color: rgba(255,255,255,0.4);
      font-size: 0.76rem;
      margin-top: 2px;
    }
    .search-item .si-class {
      color: #00cfe8;
      font-size: 0.76rem;
      font-weight: 600;
    }

    /* Spinner */
    .spin { animation: spinAnim 0.8s linear infinite; }
    @keyframes spinAnim { 100% { transform: rotate(360deg); } }
  </style>
@endsection

@section('content')

  {{-- ═══════════════════════════════════════════════════════
       HERO HEADER
  ═══════════════════════════════════════════════════════ --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-bolt text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            Absensi / Absensi Cepat
          </div>
          <h4 class="das-hero__title text-gradient-gold">Absensi Cepat</h4>
          <p class="das-hero__subtitle">Input absensi massal untuk memproses data seluruh kelas dalam satu langkah.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white rounded-pill">
          <i class="ti tabler-keyboard me-1"></i> Shortcut Keyboard: <span class="text-info ms-1 fw-bold">Angka 1-5</span>
        </div>
      </div>
    </div>
  </div>

  <div class="das-panel mb-4">
    <div class="das-panel__body">
      <form action="{{ route('admin.absensi-cepat') }}" method="GET" id="form-filter">
        <div class="row align-items-end g-3">
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="search_nama">Cari Siswa</label>
            <div class="position-relative">
              <span class="position-absolute top-50 start-0 translate-middle-y ps-3" style="color: rgba(255,255,255,0.4); pointer-events: none;">
                <i class="ti tabler-search"></i>
              </span>
              <input type="text" id="search_nama" class="form-control ps-9" placeholder="Ketik nama / NIS / NISN…"
                style="height: 38px;" autocomplete="off">
              <span id="searchSpinner" class="position-absolute top-50 end-0 translate-middle-y pe-3 d-none" style="color: rgba(255,255,255,0.4);">
                <i class="ti tabler-loader-2 spin"></i>
              </span>
              {{-- Dropdown Hasil Pencarian --}}
              <div id="searchDropdown" class="position-absolute w-100 d-none" style="top: 100%; z-index: 1050; margin-top: 4px;">
                <div class="rounded-3 shadow-lg border" style="background: rgba(20,25,35,0.97); border-color: rgba(255,255,255,0.1) !important; max-height: 320px; overflow-y: auto; backdrop-filter: blur(12px);">
                  <div id="searchResults"></div>
                  <div id="searchEmpty" class="d-none p-3 text-center text-white-50 small">
                    <i class="ti tabler-search-off me-1"></i> Tidak ada siswa ditemukan
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="kelas_id">Pilih Kelas</label>
            @if(isset($isWaliKelas) && $isWaliKelas)
              <select id="kelas_id_disabled" class="form-select" disabled>
                @foreach ($kelasOptions as $kelas)
                  <option value="{{ $kelas->id }}" selected>{{ $kelas->nama }}</option>
                @endforeach
              </select>
              <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
            @else
              <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" onchange="this.form.submit()">
                <option value="">-- Pilih Kelas --</option>
                @foreach ($kelasOptions as $kelas)
                  <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                    {{ $kelas->nama }}
                  </option>
                @endforeach
              </select>
            @endif
          </div>
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="tanggal">Tanggal Absensi</label>
            <input type="date" name="tanggal" id="tanggal_filter" class="form-control" value="{{ request('tanggal', now()->toDateString()) }}">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn das-btn --info w-100">
              <i class="ti tabler-refresh me-1"></i> Muat Siswa
            </button>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <span id="searchResultCount" class="text-white-50 small" style="font-size: 0.8rem;"></span>
          </div>
        </div>
      </form>
    </div>
  </div>

  @if ($selectedKelasId && count($siswa) > 0)
    <form action="{{ route('admin.absensi-cepat.store') }}" method="POST">
      @csrf
      <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
      <input type="hidden" name="tanggal" id="tanggal_submit" value="{{ request('tanggal', now()->toDateString()) }}">

      <div class="das-panel overflow-hidden mb-4">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <span class="das-panel__icon-dot --info"></span>
            Daftar Siswa — <span class="text-info">{{ $siswa[0]->kelas->nama ?? '' }}</span>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn das-btn --success" onclick="markAll('hadir')">
              <i class="ti tabler-check me-1"></i> Tandai Semua Hadir
            </button>
            <button type="button" class="btn das-btn --secondary" onclick="resetForm()">
              <i class="ti tabler-rotate me-1"></i> Reset
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="das-table align-middle mb-0">
            <thead>
              <tr>
                <th width="50" class="text-center ps-4">NO</th>
                <th>NAMA SISWA</th>
                <th width="350" class="text-center">STATUS KEHADIRAN</th>
                <th class="pe-4 text-end">KETERANGAN</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($siswa as $index => $s)
                <tr class="student-row">
                  <td class="text-center text-white-50 ps-4 small">{{ $index + 1 }}</td>
                  <td>
                    <div class="fw-bold text-white">{{ $s->nama_lengkap }}</div>
                    <div class="small text-white-50 opacity-75">{{ $s->nis }} / {{ $s->nisn }}</div>
                    <input type="hidden" name="absensi[{{ $index }}][siswa_id]" value="{{ $s->id }}">
                  </td>
                  <td>
                    <div class="d-flex justify-content-center gap-2 absensi-radios">
                      {{-- HADIR --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="h-{{ $s->id }}" value="hadir" checked autocomplete="off" onchange="updateSummary()">
                      <label class="btn btn-sm btn-outline-success rounded-pill px-3" for="h-{{ $s->id }}" title="Hadir">H</label>
 
                      {{-- SAKIT --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="s-{{ $s->id }}" value="sakit" autocomplete="off" onchange="updateSummary()">
                      <label class="btn btn-sm btn-outline-info rounded-pill px-3" for="s-{{ $s->id }}" title="Sakit">S</label>
 
                      {{-- IZIN --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="i-{{ $s->id }}" value="izin" autocomplete="off" onchange="updateSummary()">
                      <label class="btn btn-sm btn-outline-warning rounded-pill px-3" for="i-{{ $s->id }}" title="Izin">I</label>
 
                      {{-- ALPHA --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="a-{{ $s->id }}" value="alpha" autocomplete="off" onchange="updateSummary()">
                      <label class="btn btn-sm btn-outline-danger rounded-pill px-3" for="a-{{ $s->id }}" title="Alpha">A</label>
 
                      @php
                        $activeJenjang = \App\Helpers\JenjangHelper::getActiveJenjang();
                      @endphp
                      @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
                        {{-- TERLAMBAT --}}
                        <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                          id="t-{{ $s->id }}" value="terlambat" autocomplete="off" onchange="updateSummary()">
                        <label class="btn btn-sm btn-outline-primary rounded-pill px-3" for="t-{{ $s->id }}" title="Terlambat">T</label>
                      @endif
                    </div>
                  </td>
                  <td class="pe-4 text-end">
                    <input type="text" name="absensi[{{ $index }}][keterangan]" class="form-control form-control-sm ms-auto" style="max-width:200px;" placeholder="...">
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="border-top p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3"
          style="border-color:rgba(255,255,255,0.08) !important; background:rgba(255,255,255,0.02);">
          <div class="d-flex flex-wrap gap-4 fw-bold" id="summary-badge" style="font-size:0.85rem;">
            <div class="text-success d-flex align-items-center gap-1"><i class="ti tabler-circle-check fs-5"></i> <span id="sum-h">0</span> Hadir</div>
            <div class="text-info d-flex align-items-center gap-1"><i class="ti tabler-stethoscope fs-5"></i> <span id="sum-s">0</span> Sakit</div>
            <div class="text-warning d-flex align-items-center gap-1"><i class="ti tabler-file-description fs-5"></i> <span id="sum-i">0</span> Izin</div>
            <div class="text-danger d-flex align-items-center gap-1"><i class="ti tabler-x fs-5"></i> <span id="sum-a">0</span> Alpha</div>
            @if(!in_array($activeJenjang, ['SD/MI', 'SMP/MTs']))
              <div class="text-primary d-flex align-items-center gap-1"><i class="ti tabler-clock fs-5"></i> <span id="sum-t">0</span> Telat</div>
            @else
              <div class="d-none"><span id="sum-t">0</span></div>
            @endif
          </div>
          <div class="flex-shrink-0">
            <button type="submit" class="btn das-btn --info px-4">
              <i class="ti tabler-device-floppy me-2"></i> Simpan Absensi
            </button>
          </div>
        </div>
      </div>
    </form>
  @elseif($selectedKelasId)
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert" style="background:rgba(0,207,232,0.1);">
       <i class="ti tabler-info-circle me-3 fs-3 text-info"></i>
       <div class="text-info fw-medium">Tidak ada siswa aktif ditemukan di kelas ini.</div>
    </div>
  @else
    <div class="das-panel">
      <div class="das-panel__body text-center py-5">
        <div class="avatar avatar-xl bg-label-info mx-auto mb-4 shadow-sm" style="width:72px; height:72px;">
          <span class="avatar-initial rounded-circle"><i class="ti tabler-users-group fs-1"></i></span>
        </div>
        <h5 class="text-white fw-bold">Silahkan Pilih Kelas</h5>
        <p class="text-white-50 opacity-50 mx-auto" style="max-width:400px;">Pilih kelas di atas untuk memuat daftar siswa dan melakukan pengisian absensi massal dengan cepat.</p>
      </div>
    </div>
  @endif
@endsection

@push('page-script')
<script>
  function updateSummary() {
    const h = document.querySelectorAll('input[value="hadir"]:checked').length;
    const s = document.querySelectorAll('input[value="sakit"]:checked').length;
    const i = document.querySelectorAll('input[value="izin"]:checked').length;
    const a = document.querySelectorAll('input[value="alpha"]:checked').length;
    const t = document.querySelectorAll('input[value="terlambat"]:checked').length;

    const sumH = document.getElementById('sum-h');
    const sumS = document.getElementById('sum-s');
    const sumI = document.getElementById('sum-i');
    const sumA = document.getElementById('sum-a');
    const sumT = document.getElementById('sum-t');

    if(sumH) sumH.innerText = h;
    if(sumS) sumS.innerText = s;
    if(sumI) sumI.innerText = i;
    if(sumA) sumA.innerText = a;
    if(sumT) sumT.innerText = t;
  }

  function markAll(status) {
    const radios = document.querySelectorAll(`input[value="${status}"]`);
    radios.forEach(radio => radio.checked = true);
    updateSummary();
  }

  function resetForm() {
    if(confirm('Reset semua input ke default (Hadir)?')) {
      markAll('hadir');
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    updateSummary();

    // Sync tanggal filter ke tanggal submit
    const tFilter = document.getElementById('tanggal_filter');
    const tSubmit = document.getElementById('tanggal_submit');
    if (tFilter && tSubmit) {
       tFilter.addEventListener('change', () => {
          tSubmit.value = tFilter.value;
       });
    }

    // ══ AJAX Search Siswa ══
    const searchInput = document.getElementById('search_nama');
    const dropdown    = document.getElementById('searchDropdown');
    const resultsDiv  = document.getElementById('searchResults');
    const emptyMsg    = document.getElementById('searchEmpty');
    const spinner     = document.getElementById('searchSpinner');
    const resultCount = document.getElementById('searchResultCount');
    const tbody       = document.querySelector('.das-table tbody');

    if (!searchInput || !dropdown) return;

    let debounceTimer;
    let activeIndex = -1;

    searchInput.addEventListener('input', function() {
      clearTimeout(debounceTimer);
      const q = this.value.trim();
      if (q.length < 2) {
        hideDropdown();
        // Kalau ada tabel (sudah pilih kelas), lakukan filter client-side
        if (tbody) filterClientSide(q);
        return;
      }
      debounceTimer = setTimeout(() => fetchSearch(q), 250);
    });

    // Tutup dropdown kalau klik di luar
    document.addEventListener('click', function(e) {
      if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        hideDropdown();
      }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
      const items = resultsDiv.querySelectorAll('.search-item');
      if (!items.length) return;

      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        updateActive(items);
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        updateActive(items);
      } else if (e.key === 'Enter' && activeIndex >= 0) {
        e.preventDefault();
        items[activeIndex].click();
      } else if (e.key === 'Escape') {
        hideDropdown();
      }
    });

    function updateActive(items) {
      items.forEach((item, i) => {
        item.classList.toggle('active', i === activeIndex);
      });
      if (items[activeIndex]) {
        items[activeIndex].scrollIntoView({ block: 'nearest' });
      }
    }

    async function fetchSearch(query) {
      spinner.classList.remove('d-none');
      try {
        const resp = await fetch(`{{ route('admin.absensi-cepat.search') }}?query=${encodeURIComponent(query)}`);
        const json = await resp.json();
        renderResults(json.data || []);
      } catch (err) {
        renderResults([]);
      } finally {
        spinner.classList.add('d-none');
      }
    }

    function renderResults(data) {
      resultsDiv.innerHTML = '';
      activeIndex = -1;

      if (data.length === 0) {
        emptyMsg.classList.remove('d-none');
        dropdown.classList.remove('d-none');
        return;
      }

      emptyMsg.classList.add('d-none');
      data.forEach(item => {
        const div = document.createElement('div');
        div.className = 'search-item';
        div.dataset.kelasId = item.kelas_id;
        div.dataset.siswaId = item.id;
        div.innerHTML = `
          <div class="si-name">${escapeHtml(item.nama)}</div>
          <div class="d-flex gap-2 align-items-center mt-1">
            <span class="si-meta">NIS: ${escapeHtml(item.nis || '-')} | NISN: ${escapeHtml(item.nisn || '-')}</span>
            <span class="si-class"><i class="ti tabler-school me-1"></i>${escapeHtml(item.kelas_nama)}</span>
          </div>
        `;
        div.addEventListener('click', () => {
          // Navigate ke kelas siswa tersebut (load bulk form)
          const url = new URL('{{ route("admin.absensi-cepat") }}', window.location.origin);
          url.searchParams.set('kelas_id', item.kelas_id);
          url.searchParams.set('tanggal', document.getElementById('tanggal_filter')?.value || '');
          url.searchParams.set('highlight', item.siswa_id);
          window.location.href = url.toString();
        });
        resultsDiv.appendChild(div);
      });

      dropdown.classList.remove('d-none');
    }

    function hideDropdown() {
      dropdown.classList.add('d-none');
      resultsDiv.innerHTML = '';
      emptyMsg.classList.add('d-none');
      activeIndex = -1;
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ══ Client-side filter (saat tabel sudah ada) ══
    function filterClientSide(query) {
      if (!tbody) return;
      const rows = Array.from(tbody.querySelectorAll('tr.student-row'));
      let visibleCount = 0;
      const q = query.toLowerCase();

      rows.forEach(row => {
        const nameEl = row.querySelector('.fw-bold.text-white');
        const detailEl = row.querySelector('.small.text-white-50');
        const name = nameEl ? nameEl.textContent.toLowerCase() : '';
        const detail = detailEl ? detailEl.textContent.toLowerCase() : '';
        const fullText = name + ' ' + detail;

        if (!q || fullText.includes(q)) {
          row.classList.remove('hidden-search');
          visibleCount++;
        } else {
          row.classList.add('hidden-search');
        }
      });

      if (resultCount) {
        resultCount.textContent = q ? visibleCount + ' dari ' + rows.length + ' siswa' : '';
      }
    }

    // ══ Highlight siswa dari URL param (setelah klik search result) ══
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');
    if (highlightId && tbody) {
      setTimeout(() => {
        // Cari radio button siswa ini
        const radio = document.getElementById('h-' + highlightId);
        if (radio) {
          const row = radio.closest('tr');
          if (row) {
            row.style.background = 'rgba(0, 207, 232, 0.1)';
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => { row.style.background = ''; }, 2500);
          }
        }
        // Bersihkan URL param
        urlParams.delete('highlight');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, '', newUrl);
      }, 400);
    }
  });

  // Keyboard Shortcuts: 1-5 to select status for focused row
  document.addEventListener('keydown', (e) => {
     if (['1','2','3','4','5'].includes(e.key)) {
        const active = document.activeElement;
        const row = active.closest('tr');
        if (row) {
           const map = {'1':'hadir', '2':'sakit', '3':'izin', '4':'alpha', '5':'terlambat'};
           const radio = row.querySelector(`input[value="${map[e.key]}"]`);
           if (radio) {
              radio.checked = true;
              updateSummary();
           }
        }
     }
  });
</script>
@endpush
