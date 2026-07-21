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
          <div class="col-md-4">
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
