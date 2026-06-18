@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Ekskul')

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
    --das-purple: #a855f7;
    --das-purple-soft: rgba(168, 85, 247, 0.12);
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

  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .stat-card { background: rgba(255,255,255,.025); border: 1px solid var(--das-border); border-radius: var(--das-radius); padding: 1rem; display: flex; align-items: center; gap: .75rem; transition: all .2s; }
  .stat-card:hover { background: rgba(255,255,255,.04); transform: translateY(-2px); }
  .stat-card__icon { width: 42px; height: 42px; border-radius: var(--das-radius); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
  .stat-card__value { font-size: 1.3rem; font-weight: 800; line-height: 1.2; }
  .stat-card__label { font-size: .65rem; text-transform: uppercase; letter-spacing: .5px; color: #888; }

  .progress-wrap { display: flex; align-items: center; gap: 8px; }
  .progress-bar-custom { flex: 1; height: 8px; border-radius: 10px; background: rgba(255,255,255,.08); overflow: hidden; }
  .progress-bar-custom__fill { height: 100%; border-radius: 10px; transition: width .5s ease; }
  .persentase-badge { font-size: .75rem; font-weight: 700; min-width: 48px; text-align: right; }
  .persentase-badge--good { color: var(--das-success); }
  .persentase-badge--warn { color: var(--das-warning); }
  .persentase-badge--bad { color: var(--das-danger); }
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
                style="width:52px;height:52px;border-radius:12px !important;background:rgba(255,159,67,0.2);border:1px solid rgba(255,159,67,0.4);">
                <i class="ti tabler-report-analytics text-warning fs-3"></i>
              </div>
              <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="text-white text-decoration-none">Absensi</a></li>
                    <li class="breadcrumb-item active text-white">Rekap</li>
                  </ol>
                </nav>
                <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                  Rekap Absensi: {{ $ekskul->nama }}
                </h4>
              </div>
            </div>
            <div class="d-flex gap-2">
              <a href="{{ route('admin.ekskul.absensi.rekap.export-excel', ['ekskul' => $ekskul->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="das-btn das-btn--primary">
                <i class="ti tabler-file-spreadsheet me-1"></i> Export Excel
              </a>
              <a href="{{ route('admin.ekskul.absensi.rekap.export-pdf', ['ekskul' => $ekskul->id, 'bulan' => $bulan, 'tahun' => $tahun]) }}" class="das-btn das-btn--ghost">
                <i class="ti tabler-file-type-pdf me-1"></i> Export PDF
              </a>
              <a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="das-btn das-btn--ghost">
                <i class="ti tabler-arrow-left me-1"></i> Absensi
              </a>
              <a href="{{ route('admin.ekskul.anggota.index', $ekskul->id) }}" class="das-btn das-btn--ghost">
                <i class="ti tabler-users me-1"></i> Anggota
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════ FILTER BULAN/TAHUN ═══════════ --}}
  <div class="das-panel mb-4 slide-in-up">
    <div class="p-3">
      <form method="GET" action="{{ route('admin.ekskul.absensi.rekap', $ekskul->id) }}" class="row g-3 align-items-end">
        <div class="col-md-3 col-sm-6">
          <label class="das-form-label" for="bulan">
            <i class="ti tabler-calendar-month me-1 text-info"></i> Bulan
          </label>
          <select name="bulan" id="bulan" class="form-select das-form-control">
            @foreach(range(1, 12) as $b)
              <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 col-sm-6">
          <label class="das-form-label" for="tahun">
            <i class="ti tabler-calendar me-1 text-info"></i> Tahun
          </label>
          <select name="tahun" id="tahun" class="form-select das-form-control">
            @foreach(range(now()->year - 2, now()->year) as $y)
              <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 col-sm-6">
          <button type="submit" class="das-btn das-btn--primary w-100 justify-content-center">
            <i class="ti tabler-filter me-1"></i> Tampilkan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ═══════════ STATISTIK RINGKASAN ═══════════ --}}
  <div class="row g-3 mb-4 slide-in-up">
    @php
      $statCards = [
        ['label'=>'Hadir','value'=>$total['hadir'],'icon'=>'tabler-circle-check','bg'=>'rgba(40,199,111,.15)','border'=>'rgba(40,199,111,.3)','color'=>'var(--das-success)'],
        ['label'=>'Izin','value'=>$total['izin'],'icon'=>'tabler-file-info','bg'=>'rgba(0,207,232,.15)','border'=>'rgba(0,207,232,.3)','color'=>'var(--das-info)'],
        ['label'=>'Sakit','value'=>$total['sakit'],'icon'=>'tabler-stethoscope','bg'=>'rgba(255,159,67,.15)','border'=>'rgba(255,159,67,.3)','color'=>'var(--das-warning)'],
        ['label'=>'Alpha','value'=>$total['alpha'],'icon'=>'tabler-x','bg'=>'rgba(234,84,85,.15)','border'=>'rgba(234,84,85,.3)','color'=>'var(--das-danger)'],
        ['label'=>'Terlambat','value'=>$total['terlambat'],'icon'=>'tabler-clock-exclamation','bg'=>'rgba(168,85,247,.15)','border'=>'rgba(168,85,247,.3)','color'=>'var(--das-purple)'],
      ];
    @endphp
    @foreach($statCards as $card)
      <div class="col-lg col-md-4 col-sm-6">
        <div class="stat-card" style="border-color:{{ $card['border'] }};">
          <div class="stat-card__icon" style="background:{{ $card['bg'] }};color:{{ $card['color'] }};">
            <i class="ti {{ $card['icon'] }}"></i>
          </div>
          <div>
            <div class="stat-card__value" style="color:{{ $card['color'] }};">{{ $card['value'] }}</div>
            <div class="stat-card__label">{{ $card['label'] }}</div>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ═══════════ TABEL REKAP PER SISWA ═══════════ --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Rekap Per Siswa — {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
      </div>
      <span class="das-chip das-chip--info">{{ $rekapPerSiswa->count() }} Siswa</span>
    </div>
    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Nama Siswa</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Izin</th>
            <th class="text-center">Sakit</th>
            <th class="text-center">Alpha</th>
            <th class="text-center">Terlambat</th>
            <th style="min-width:160px;">Persentase Kehadiran</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rekapPerSiswa as $r)
            @php
              $badgeClass = $r->persentase >= 80 ? 'persentase-badge--good' : ($r->persentase >= 60 ? 'persentase-badge--warn' : 'persentase-badge--bad');
              $barColor = $r->persentase >= 80 ? 'var(--das-success)' : ($r->persentase >= 60 ? 'var(--das-warning)' : 'var(--das-danger)');
            @endphp
            <tr>
              <td class="text-muted small text-center">{{ $loop->iteration }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width:28px;height:28px;border-radius:50%;background:var(--das-primary-soft);border:1px solid rgba(115,103,240,.2);display:flex;align-items:center;justify-content:center;font-size:.65rem;color:var(--das-primary);flex-shrink:0;">
                    {{ strtoupper(substr($r->siswa->nama_lengkap ?? '-', 0, 1)) }}
                  </div>
                  <div>
                    <div class="fw-medium text-white" style="font-size:.8rem;">{{ $r->siswa->nama_lengkap ?? '-' }}</div>
                    <div class="text-white-50" style="font-size:.6rem;">{{ $r->siswa->nis ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td class="text-center">
                <span class="fw-bold" style="color:var(--das-success);">{{ $r->hadir }}</span>
              </td>
              <td class="text-center">
                <span class="fw-bold" style="color:var(--das-info);">{{ $r->izin }}</span>
              </td>
              <td class="text-center">
                <span class="fw-bold" style="color:var(--das-warning);">{{ $r->sakit }}</span>
              </td>
              <td class="text-center">
                <span class="fw-bold" style="color:var(--das-danger);">{{ $r->alpha }}</span>
              </td>
              <td class="text-center">
                <span class="fw-bold" style="color:var(--das-purple);">{{ $r->terlambat }}</span>
              </td>
              <td>
                <div class="progress-wrap">
                  <div class="progress-bar-custom">
                    <div class="progress-bar-custom__fill" style="width:{{ $r->persentase }}%;background:{{ $barColor }};"></div>
                  </div>
                  <span class="persentase-badge {{ $badgeClass }}">{{ $r->persentase }}%</span>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="py-5 text-center">
                <div class="d-flex flex-column align-items-center gap-2 opacity-40">
                  <i class="ti tabler-database-off" style="font-size:3rem;"></i>
                  <h6 class="text-white mb-1">Belum Ada Data Absensi</h6>
                  <p class="text-white-50 small mb-3">
                    Tidak ada data absensi untuk periode
                    {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}.
                  </p>
                  <a href="{{ route('admin.ekskul.absensi.show', [$ekskul->id, date('Y-m-d')]) }}" class="das-btn das-btn--primary">
                    <i class="ti tabler-clipboard-check me-1"></i> Mulai Absensi
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($rekapPerSiswa->isNotEmpty())
      <div class="p-3 d-flex align-items-center justify-content-between" style="border-top:1px solid var(--das-border);">
        <div class="d-flex align-items-center gap-3 small">
          <span class="d-flex align-items-center gap-1" style="font-size:.65rem;">
            <span style="width:10px;height:10px;border-radius:3px;background:var(--das-success);"></span> Hadir
          </span>
          <span class="d-flex align-items-center gap-1" style="font-size:.65rem;">
            <span style="width:10px;height:10px;border-radius:3px;background:var(--das-info);"></span> Izin
          </span>
          <span class="d-flex align-items-center gap-1" style="font-size:.65rem;">
            <span style="width:10px;height:10px;border-radius:3px;background:var(--das-warning);"></span> Sakit
          </span>
          <span class="d-flex align-items-center gap-1" style="font-size:.65rem;">
            <span style="width:10px;height:10px;border-radius:3px;background:var(--das-danger);"></span> Alpha
          </span>
          <span class="d-flex align-items-center gap-1" style="font-size:.65rem;">
            <span style="width:10px;height:10px;border-radius:3px;background:var(--das-purple);"></span> Terlambat
          </span>
        </div>
        <div class="text-white-50 small" style="font-size:.65rem;">
          Total: {{ $total['total'] }} catatan absensi
        </div>
      </div>
    @endif
  </div>

@endsection
