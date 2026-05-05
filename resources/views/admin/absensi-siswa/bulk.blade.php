@extends('layouts/layoutMaster')

@section('title', 'Absensi Cepat — Bulk Input')

@section('page-style')
  <style>
    .glass-card {
      background: rgba(255, 255, 255, 0.04) !important;
      border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }

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

  {{-- HERO HEADER --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 12px;">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-7">
              <div class="d-flex align-items-center gap-3">
                <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                  style="width:52px;height:52px;border-radius:12px !important;background:rgba(0,207,232,0.2);border:1px solid rgba(0,207,232,0.4);">
                  <i class="ti tabler-bolt text-info fs-3"></i>
                </div>
                <div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                      <li class="breadcrumb-item"><span class="text-white opacity-50">Absensi</span></li>
                      <li class="breadcrumb-item active text-white">Absensi Cepat</li>
                    </ol>
                  </nav>
                  <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Absensi Cepat</h4>
                  <p class="mb-0 text-white opacity-60 small">Input absensi massal untuk memproses data seluruh kelas dalam satu langkah.</p>
                </div>
              </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
               <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
                  Shortcut Keyboard: <span class="text-info ms-1">Angka 1-5</span>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card glass-card mb-4">
    <div class="card-body">
      <form action="{{ route('admin.absensi-cepat') }}" method="GET" id="form-filter">
        <div class="row align-items-end g-3">
          <div class="col-md-4">
            <label class="form-label text-white-50 small fw-bold" for="kelas_id">Pilih Kelas</label>
            <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" onchange="this.form.submit()">
              <option value="">-- Pilih Kelas --</option>
              @foreach ($kelasOptions as $kelas)
                <option value="{{ $kelas->id }}" {{ $selectedKelasId == $kelas->id ? 'selected' : '' }}>
                  {{ $kelas->nama }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label text-white-50 small fw-bold" for="tanggal">Tanggal Absensi</label>
            <input type="date" name="tanggal" id="tanggal_filter" class="form-control" value="{{ request('tanggal', now()->toDateString()) }}">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-info w-100 fw-bold">
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

      <div class="card glass-card overflow-hidden mb-4">
        <div class="card-header border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3" style="background:transparent; border-color: rgba(255,255,255,0.08) !important;">
          <h6 class="mb-0 text-white d-flex align-items-center gap-2">
            <i class="ti tabler-users-group text-info"></i> Daftar Siswa — <span class="text-info">{{ $siswa[0]->kelas->nama ?? '' }}</span>
          </h6>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-label-success" onclick="markAll('hadir')">
              <i class="ti tabler-check me-1"></i> Tandai Semua Hadir
            </button>
            <button type="button" class="btn btn-sm btn-label-secondary" onclick="resetForm()">
              <i class="ti tabler-rotate me-1"></i> Reset
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table align-middle mb-0" style="color:inherit;">
            <thead style="background:rgba(255,255,255,0.02); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; opacity:0.7;">
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
 
                      {{-- TERLAMBAT --}}
                      <input type="radio" class="btn-check" name="absensi[{{ $index }}][status]" 
                        id="t-{{ $s->id }}" value="terlambat" autocomplete="off" onchange="updateSummary()">
                      <label class="btn btn-sm btn-outline-primary rounded-pill px-3" for="t-{{ $s->id }}" title="Terlambat">T</label>
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
        <div class="card-footer border-top p-4" style="background:rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
          <div class="row align-items-center">
            <div class="col-md-9 mb-3 mb-md-0">
               <div class="d-flex flex-wrap gap-4 fs-6 fw-bold" id="summary-badge">
                 <div class="text-success d-flex align-items-center gap-1"><i class="ti tabler-circle-check fs-5"></i> <span id="sum-h">0</span> Hadir</div>
                 <div class="text-info d-flex align-items-center gap-1"><i class="ti tabler-stethoscope fs-5"></i> <span id="sum-s">0</span> Sakit</div>
                 <div class="text-warning d-flex align-items-center gap-1"><i class="ti tabler-file-description fs-5"></i> <span id="sum-i">0</span> Izin</div>
                 <div class="text-danger d-flex align-items-center gap-1"><i class="ti tabler-x fs-5"></i> <span id="sum-a">0</span> Alpha</div>
                 <div class="text-primary d-flex align-items-center gap-1"><i class="ti tabler-clock fs-5"></i> <span id="sum-t">0</span> Telat</div>
               </div>
            </div>
            <div class="col-md-3 text-md-end">
               <button type="submit" class="btn btn-info px-4 py-2 fw-bold shadow-sm">
                 <i class="ti tabler-device-floppy me-2"></i> Simpan Absensi
               </button>
            </div>
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
    <div class="card glass-card">
      <div class="card-body text-center py-5">
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

    document.getElementById('sum-h').innerText = h;
    document.getElementById('sum-s').innerText = s;
    document.getElementById('sum-i').innerText = i;
    document.getElementById('sum-a').innerText = a;
    document.getElementById('sum-t').innerText = t;
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
