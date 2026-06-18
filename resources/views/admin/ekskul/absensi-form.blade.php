@extends('layouts/layoutMaster')

@section('title', 'Form Absensi Ekskul')

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-success: #28c76f;
    --das-success-soft: rgba(40, 199, 111, 0.12);
    --das-info: #00cfe8;
    --das-info-soft: rgba(0, 207, 232, 0.12);
    --das-warning: #ff9f43;
    --das-warning-soft: rgba(255, 159, 67, 0.12);
    --das-danger: #ea5455;
    --das-danger-soft: rgba(234, 84, 85, 0.12);
    --das-surface: rgba(15, 23, 42, 0.4);
    --das-surface-hover: rgba(30, 41, 59, 0.6);
    --das-border: rgba(255, 255, 255, 0.06);
    --das-radius: 5px;
  }

  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }

  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .absensi-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .absensi-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .6rem .75rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); position: sticky; top: 0; z-index: 1; }
  .absensi-table tbody td { padding: .5rem .75rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; }
  .absensi-table tbody tr:hover td { background: var(--das-surface-hover); }

  /* Radio group styling */
  .radio-group { display: flex; gap: 2px; }
  .radio-option { position: relative; }
  .radio-option input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
  .radio-option label {
    display: flex; align-items: center; justify-content: center;
    padding: .25rem .55rem; border-radius: 4px;
    font-size: .68rem; font-weight: 600; cursor: pointer;
    transition: all .15s ease; white-space: nowrap;
    border: 1px solid transparent;
    background: rgba(255,255,255,.03); color: #888;
    min-width: 50px; text-align: center;
  }
  .radio-option input[type="radio"]:checked + label[data-status="hadir"]    { background: rgba(40,199,111,.15); border-color: var(--das-success); color: var(--das-success); }
  .radio-option input[type="radio"]:checked + label[data-status="izin"]     { background: rgba(0,207,232,.15); border-color: var(--das-info); color: var(--das-info); }
  .radio-option input[type="radio"]:checked + label[data-status="sakit"]    { background: rgba(255,159,67,.15); border-color: var(--das-warning); color: var(--das-warning); }
  .radio-option input[type="radio"]:checked + label[data-status="alpha"]    { background: rgba(234,84,85,.15); border-color: var(--das-danger); color: var(--das-danger); }
  .radio-option input[type="radio"]:checked + label[data-status="terlambat"]{ background: rgba(168,85,247,.15); border-color: #a855f7; color: #a855f7; }
  .radio-option label:hover { background: rgba(255,255,255,.06); color: #ccc; }

  .bulk-action { display: flex; align-items: center; gap: .5rem; }
  .bulk-action .das-btn { font-size: .65rem; padding: .25rem .6rem; }

  @media (max-width: 768px) {
    .radio-group { flex-wrap: wrap; }
  }
</style>
@endsection

@section('content')

  {{-- ═══════════ HERO HEADER ═══════════ --}}
  <div class="row mb-4 slide-in-up">
    <div class="col-12">
      <div class="card border-0 text-white overflow-hidden shadow-lg"
        style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-radius: 4px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
              <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                style="width:52px;height:52px;border-radius:12px !important;background:rgba(40,199,111,0.2);border:1px solid rgba(40,199,111,0.4);">
                <i class="ti tabler-clipboard-check text-success fs-3"></i>
              </div>
              <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="text-white text-decoration-none">Absensi</a></li>
                    <li class="breadcrumb-item active text-white">Form</li>
                  </ol>
                </nav>
                <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                  {{ $ekskul->nama }}
                </h4>
              </div>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="das-btn" style="background:rgba(255,255,255,.05);border-color:var(--das-border);color:#ccc;cursor:default;font-size:.8rem;">
                <i class="ti tabler-calendar me-1 text-info"></i>
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
      role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-circle-check fs-4 text-success"></i>
      <div class="text-white small fw-medium">{{ session('success') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if (session('error'))
    <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4 border-0 shadow-lg slide-in-up"
      role="alert" style="border-radius:8px;background:rgba(0,0,0,.3);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.1)!important;">
      <i class="ti tabler-alert-circle fs-4 text-danger"></i>
      <div class="text-white small fw-medium">{{ session('error') }}</div>
      <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible d-flex align-items-start gap-2 mb-4 border-0 shadow-sm slide-in-up"
      style="border-radius:8px; background: rgba(234, 84, 85, 0.15); color: #ea5455;">
      <i class="ti tabler-alert-circle fs-5 mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-3 small">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ═══════════ FORM ABSENSI ═══════════ --}}
  <form action="{{ route('admin.ekskul.absensi.store', [$ekskul->id, $tanggal]) }}" method="POST" id="absensiForm" x-data="absensiForm()">
    @csrf

    <div class="das-panel slide-in-up">
      {{-- Pembina + Bulk Action --}}
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot"></span>
          Daftar Anggota ({{ $anggota->count() }})
        </div>
        <div class="d-flex align-items-center gap-3">
          {{-- Bulk set --}}
          <div class="bulk-action d-none d-md-flex">
            <span class="text-white-50 small me-1" style="font-size:.65rem;">Set Semua:</span>
            <button type="button" class="das-btn das-btn--ghost" @click="setAll('hadir')" style="font-size:.6rem;padding:.15rem .5rem;">Hadir</button>
            <button type="button" class="das-btn das-btn--ghost" @click="setAll('izin')" style="font-size:.6rem;padding:.15rem .5rem;">Izin</button>
            <button type="button" class="das-btn das-btn--ghost" @click="setAll('sakit')" style="font-size:.6rem;padding:.15rem .5rem;">Sakit</button>
            <button type="button" class="das-btn das-btn--ghost" @click="setAll('alpha')" style="font-size:.6rem;padding:.15rem .5rem;">Alpha</button>
          </div>

          {{-- Pembina --}}
          <div style="min-width:180px;">
            <select name="pembina_id" class="form-select das-form-control" style="font-size:.72rem!important;padding:.35rem .65rem!important;">
              <option value="">Pilih Pembina</option>
              @foreach(\App\Models\Guru::orderBy('nama_lengkap')->get() as $g)
                <option value="{{ $g->id }}" {{ old('pembina_id') == $g->id ? 'selected' : '' }}>
                  {{ $g->nama_lengkap }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      {{-- Tabel Anggota --}}
      <div class="table-responsive" style="max-height:60vh;overflow-y:auto;">
        <table class="absensi-table">
          <thead>
            <tr>
              <th width="40">#</th>
              <th>Nama Siswa</th>
              <th class="d-none d-md-table-cell">Kelas</th>
              <th style="min-width:280px;">Status Kehadiran</th>
            </tr>
          </thead>
          <tbody>
            @forelse($anggota as $a)
              @php
                $siswa = $a->siswa;
                $existing = $absensiHariIni->get($siswa->id);
                $currentStatus = old("absensi.{$loop->index}.status", $existing->status ?? 'hadir');
              @endphp
              <tr>
                <td class="text-muted small text-center">{{ $loop->iteration }}</td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary-soft);border:1px solid rgba(115,103,240,.2);display:flex;align-items:center;justify-content:center;font-size:.65rem;color:var(--das-primary);flex-shrink:0;">
                      {{ strtoupper(substr($siswa->nama_lengkap ?? '-', 0, 1)) }}
                    </div>
                    <div>
                      <div class="fw-medium text-white" style="font-size:.78rem;">{{ $siswa->nama_lengkap ?? '-' }}</div>
                      <div class="text-white-50" style="font-size:.6rem;">{{ $siswa->nis ?? '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="d-none d-md-table-cell text-white-50 small">
                  {{ $siswa->kelas->nama ?? '-' }}
                </td>
                <td>
                  <input type="hidden" name="absensi[{{ $loop->index }}][siswa_id]" value="{{ $siswa->id }}">
                  <div class="radio-group"
                    x-data="{ status: '{{ $currentStatus }}' }"
                    x-init="$watch('status', val => { $el.querySelector('input[value='+val+']').checked = true })">
                    @foreach(['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpha'=>'Alpha','terlambat'=>'Terlambat'] as $st => $label)
                      <div class="radio-option">
                        <input type="radio"
                          name="absensi[{{ $loop->parent->index }}][status]"
                          id="abs_{{ $a->id }}_{{ $st }}"
                          value="{{ $st }}"
                          {{ $currentStatus == $st ? 'checked' : '' }}
                          @change="status = '{{ $st }}'">
                        <label for="abs_{{ $a->id }}_{{ $st }}" data-status="{{ $st }}">{{ $label }}</label>
                      </div>
                    @endforeach
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="py-5 text-center">
                  <div class="d-flex flex-column align-items-center gap-2 opacity-40">
                    <i class="ti tabler-users-minus" style="font-size:3rem;"></i>
                    <h6 class="text-white mb-1">Tidak Ada Anggota Aktif</h6>
                    <p class="text-white-50 small mb-3">Belum ada siswa terdaftar yang berstatus aktif di ekskul ini.</p>
                    <a href="{{ route('admin.ekskul.anggota.index', $ekskul->id) }}" class="das-btn das-btn--primary">
                      <i class="ti tabler-user-plus me-1"></i> Kelola Anggota
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Footer Actions --}}
      @if($anggota->isNotEmpty())
        <div class="p-3 d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-top:1px solid var(--das-border);">
          <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('admin.ekskul.absensi.rekap', $ekskul->id) }}" class="das-btn das-btn--ghost">
              <i class="ti tabler-report-analytics me-1"></i> Rekap
            </a>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="text-white-50 small" style="font-size:.65rem;">
              <i class="ti tabler-info-circle me-1"></i> Data akan tersimpan otomatis. Status default: Hadir.
            </span>
            <button type="submit" class="btn btn-success fw-semibold px-4 shadow-sm">
              <i class="ti tabler-device-floppy me-1"></i> Simpan Absensi
            </button>
          </div>
        </div>
      @endif
    </div>
  </form>

@endsection

@section('page-script')
<script>
  function absensiForm() {
    return {
      setAll(status) {
        if (!confirm('Set semua anggota menjadi "' + status + '"?')) return;
        const radios = document.querySelectorAll('input[type="radio"][value="' + status + '"]');
        radios.forEach(r => {
          r.checked = true;
          r.dispatchEvent(new Event('change', { bubbles: true }));
        });
      }
    }
  }
</script>
@endsection
