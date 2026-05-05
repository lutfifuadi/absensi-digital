@extends('layouts/layoutMaster')

@section('title', 'Kalender Absensi — ' . $startOfMonth->translatedFormat('F Y'))

@section('page-style')
<style>
  :root {
    --das-primary: #7367f0;
    --das-primary-soft: rgba(115, 103, 240, 0.12);
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
    --das-border-hover: rgba(255, 255, 255, 0.12);
    --das-radius: 5px;
  }

  /* HERO */
  .das-hero { position: relative; border-radius: var(--das-radius); overflow: hidden; margin-bottom: 2rem; }
  .das-hero__bg { position: absolute; inset: 0; background: linear-gradient(135deg, #1e1b4b 0%, #312d89 40%, #4338ca 100%); z-index: 0; }
  .das-hero__glass { position: absolute; inset: 0; background: radial-gradient(circle at top right, rgba(115,103,240,.15), transparent 40%); z-index: 1; }
  .das-hero__grid-lines { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px); background-size: 40px 40px; z-index: 1; }
  .das-hero__inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; padding: 2.5rem; gap: 1.5rem; flex-wrap: wrap; }
  .das-hero__identity { display: flex; align-items: center; gap: 1.25rem; }
  .das-hero__icon { width: 64px; height: 64px; background: rgba(115,103,240,.2); border: 1px solid rgba(115,103,240,.3); border-radius: 5px; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: #a5a2f7; }
  .das-hero__title { font-size: 1.5rem; font-weight: 800; color: white; margin: 0 0 4px; }
  .das-hero__welcome { margin: 0; font-size: .88rem; color: rgba(255,255,255,.6); }

  /* BUTTONS */
  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #aaa !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--sm { padding: .35rem .7rem; font-size: .72rem; }

  /* PANEL */
  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); flex-wrap: wrap; gap: .75rem; }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); flex-shrink: 0; }

  /* CHIP */
  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--info    { background: var(--das-info-soft);    color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }

  /* LEGENDA */
  .legend-dot { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }

  /* CALENDAR HEADER DAYS */
  .cal-header { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 4px; }
  .cal-header-cell { text-align: center; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #555; padding: 8px 0; background: rgba(255,255,255,.02); border-radius: 4px; }

  /* CALENDAR GRID */
  .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; }
  .cal-cell { border-radius: 6px; padding: 8px; min-height: 90px; border: 1px solid var(--das-border); transition: all .15s ease; position: relative; }
  .cal-cell--empty { border: none; background: transparent; }
  .cal-cell--weekend { background: rgba(255,255,255,.01); }
  .cal-cell--today { border-color: var(--das-primary) !important; border-width: 2px !important; }
  .cal-cell--holiday { background: var(--das-danger-soft); border-color: rgba(234,84,85,.2); }
  .cal-cell--success { background: var(--das-success-soft); border-color: rgba(40,199,111,.15); }
  .cal-cell--warning { background: var(--das-warning-soft); border-color: rgba(255,159,67,.15); }
  .cal-cell--danger  { background: var(--das-danger-soft);  border-color: rgba(234,84,85,.15); }
  .cal-cell--clickable { cursor: pointer; }
  .cal-cell--clickable:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.25); border-color: var(--das-border-hover); }
  .cal-day-num { font-size: .82rem; font-weight: 700; color: #aaa; line-height: 1; }
  .cal-day-num--today { color: var(--das-primary); }
  .cal-day-num--weekend { color: #555; }
  .cal-progress { height: 3px; border-radius: 2px; margin-top: 6px; background: rgba(255,255,255,.06); overflow: hidden; }
  .cal-progress-bar { height: 100%; border-radius: 2px; }

  /* STAT CARD */
  .das-stat { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); backdrop-filter: blur(6px); transition: all .2s; }
  .das-stat:hover { transform: translateY(-3px); border-color: var(--das-border-hover); box-shadow: 0 8px 24px rgba(0,0,0,.2); }

  /* MODAL */
  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(115,103,240,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }
  .das-modal-stat { background: rgba(255,255,255,.03); border: 1px solid var(--das-border); border-radius: 6px; padding: 1rem .5rem; text-align: center; }

  /* FORM SELECT DARK */
  .das-select { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; color: #ddd !important; border-radius: var(--das-radius) !important; }
  .das-select:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; box-shadow: none !important; outline: none !important; }
  .das-select option { background: #1a1a2e; color: #ccc; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }
</style>
@endsection

@section('content')

  {{-- ── HERO HEADER ────────────────────────────────── --}}
  <div class="das-hero slide-in-up">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>
    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__icon">
          <i class="ti tabler-calendar-stats"></i>
        </div>
        <div>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:1px;opacity:.6;">
              <li class="breadcrumb-item text-white">Monitoring &amp; Kalender</li>
              <li class="breadcrumb-item active text-white opacity-100">Kalender Absensi</li>
            </ol>
          </nav>
          <h4 class="das-hero__title">Kalender Absensi</h4>
          <p class="das-hero__welcome">Ringkasan visual kehadiran siswa per hari dalam satu bulan secara komprehensif.</p>
        </div>
      </div>

      {{-- Navigasi Bulan --}}
      <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.kalender-absensi', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
           class="das-btn das-btn--ghost das-btn--sm">
          <i class="ti tabler-chevron-left"></i>
        </a>
        <form method="GET" action="{{ route('admin.kalender-absensi') }}" class="d-flex gap-2">
          <select name="month" class="form-select form-select-sm das-select" style="width:120px;" onchange="this.form.submit()">
            @foreach (range(1, 12) as $m)
              <option value="{{ $m }}" @selected($m == $month)>{{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}</option>
            @endforeach
          </select>
          <select name="year" class="form-select form-select-sm das-select" style="width:85px;" onchange="this.form.submit()">
            @foreach (range(now()->year - 2, now()->year + 1) as $y)
              <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
          </select>
        </form>
        <a href="{{ route('admin.kalender-absensi', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
           class="das-btn das-btn--ghost das-btn--sm">
          <i class="ti tabler-chevron-right"></i>
        </a>
        <a href="{{ route('admin.holidays') }}" class="das-btn das-btn--ghost das-btn--sm ms-1" title="Kelola Hari Libur">
          <i class="ti tabler-calendar-plus me-1"></i> Atur Libur
        </a>
      </div>
    </div>
  </div>

  {{-- ── LEGENDA ──────────────────────────────────────── --}}
  <div class="das-panel mb-4 slide-in-up" style="overflow:visible;">
    <div class="d-flex align-items-center gap-3 flex-wrap px-4 py-3">
      <span class="das-panel__title" style="text-transform:none;font-size:.75rem;margin:0;">Keterangan:</span>
      @foreach ([
        ['bg-success',   '#28c76f', '≥90% Hadir'],
        ['bg-warning',   '#ff9f43', '80–90% Hadir'],
        ['bg-danger',    '#ea5455', '<80% / Libur'],
        ['bg-secondary', '#555',    'Weekend'],
        ['bg-primary',   '#7367f0', 'Hari Ini'],
      ] as [$bg, $color, $label])
      <div class="d-flex align-items-center gap-2">
        <span class="legend-dot" style="background:{{ $color }};opacity:.7;"></span>
        <span class="small text-muted" style="font-size:.75rem;">{{ $label }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- ── KALENDER PANEL ──────────────────────────────── --}}
  <div class="das-panel mb-4 slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        {{ $startOfMonth->translatedFormat('F Y') }}
      </div>
      <span class="das-chip das-chip--primary">{{ number_format($totalSiswa) }} Siswa</span>
    </div>
    <div style="padding:1rem 1.25rem;">
      {{-- Header Hari --}}
      <div class="cal-header">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
          <div class="cal-header-cell">{{ $d }}</div>
        @endforeach
      </div>

      {{-- Grid --}}
      <div class="cal-grid">
        {{-- Offset --}}
        @for ($i = 0; $i < $offset; $i++)
          <div class="cal-cell cal-cell--empty"></div>
        @endfor

        {{-- Hari dalam bulan --}}
        @for ($day = 1; $day <= $daysInMonth; $day++)
          @php
            $date        = \Carbon\Carbon::create($year, $month, $day)->toDateString();
            $dow         = \Carbon\Carbon::create($year, $month, $day)->dayOfWeek;
            $isWeekend   = in_array($dow, [0, 6]);
            $isToday     = $date === $today;
            $dataHari    = $calendarData[$date] ?? null;
            $isFuture    = $date > $today;
            $isHoliday   = isset($holidays[$date]);
            $holidayName = $holidays[$date] ?? null;

            // Determine cell style class
            $cellClass = 'cal-cell';
            if ($isWeekend && !$dataHari && !$isHoliday) $cellClass .= ' cal-cell--weekend';
            if ($isToday) $cellClass .= ' cal-cell--today';
            if ($isHoliday && !$dataHari) $cellClass .= ' cal-cell--holiday';

            // Color based on attendance
            $pct       = null;
            $pctColor  = null;
            $textClass = 'text-muted';

            if ($dataHari && $totalSiswa > 0 && !$isFuture) {
              $pct = $dataHari['total'] > 0
                ? round(($dataHari['hadir'] + $dataHari['terlambat']) / $totalSiswa * 100, 1)
                : 0;
              if ($pct >= 90)     { $cellClass .= ' cal-cell--success'; $pctColor = '#28c76f'; $textClass = 'text-success'; }
              elseif ($pct >= 80) { $cellClass .= ' cal-cell--warning'; $pctColor = '#ff9f43'; $textClass = 'text-warning'; }
              else                { $cellClass .= ' cal-cell--danger';  $pctColor = '#ea5455'; $textClass = 'text-danger'; }
            }
            if ($dataHari && !$isFuture) $cellClass .= ' cal-cell--clickable';
          @endphp

          <div class="{{ $cellClass }}"
               @if($dataHari && !$isFuture) onclick="showDayDetail('{{ $date }}')" @endif>

            {{-- Nomor hari + badge hari ini --}}
            <div class="d-flex justify-content-between align-items-start mb-1">
              <span class="cal-day-num {{ $isToday ? 'cal-day-num--today' : ($isWeekend ? 'cal-day-num--weekend' : '') }}">
                {{ $day }}
              </span>
              @if($isToday)
                <span class="das-chip das-chip--primary" style="font-size:.5rem;padding:1px 5px;border-radius:3px;">HARI INI</span>
              @endif
            </div>

            {{-- Konten hari --}}
            @if($isHoliday)
              <div class="text-center mt-1">
                <span style="font-size:.58rem;color:var(--das-danger);display:block;line-height:1.3;font-weight:600;">{{ $holidayName }}</span>
              </div>
            @elseif($isWeekend && !$dataHari)
              <div class="text-center mt-2 opacity-20">
                <i class="ti tabler-moon-stars" style="font-size:1rem;color:#aaa;"></i>
              </div>
            @elseif($isFuture)
              <div class="text-center mt-2 opacity-15">
                <i class="ti tabler-clock" style="color:#aaa;"></i>
              </div>
            @elseif($dataHari)
              <div class="mt-1">
                <div class="{{ $textClass }} fw-bold" style="font-size:.8rem;">
                  {{ $dataHari['hadir'] + $dataHari['terlambat'] }}<span class="fw-normal" style="font-size:.7rem;opacity:.7;">/{{ $totalSiswa }}</span>
                </div>
                <div class="{{ $textClass }}" style="font-size:.7rem;font-weight:600;">{{ $pct }}%</div>
                <div class="cal-progress">
                  <div class="cal-progress-bar" style="width:{{ $pct }}%;background:{{ $pctColor }};"></div>
                </div>
              </div>
            @else
              <div class="text-center mt-2 opacity-15">
                <i class="ti tabler-minus" style="color:#aaa;font-size:.85rem;"></i>
              </div>
            @endif
          </div>
        @endfor
      </div>
    </div>
  </div>

  {{-- ── SUMMARY STATS ───────────────────────────────── --}}
  @php
    $totalHariEfektif = count($calendarData);
    $rataHadir = $totalHariEfektif > 0
      ? round(collect($calendarData)->avg(fn($d) => ($d['hadir'] + ($d['terlambat'] ?? 0)) / max($totalSiswa, 1) * 100), 1)
      : 0;
    $hariTerbaik     = collect($calendarData)->sortByDesc(fn($d) => $d['hadir'])->keys()->first();
    $totalAbsensiAll = collect($calendarData)->sum('total');
  @endphp
  <div class="row g-3 slide-in-up">
    @foreach ([
      ['Hari Efektif',    $totalHariEfektif . ' hari',              'tabler-calendar-event', 'var(--das-primary)', 'var(--das-primary-soft)'],
      ['Rata² Kehadiran', $rataHadir . '%',                         'tabler-chart-line',     'var(--das-success)', 'var(--das-success-soft)'],
      ['Total Absensi',   number_format($totalAbsensiAll) . ' data','tabler-database',        'var(--das-info)',    'var(--das-info-soft)'],
      ['Hari Terbaik',    $hariTerbaik ? \Carbon\Carbon::parse($hariTerbaik)->translatedFormat('d M') : '—', 'tabler-trophy', 'var(--das-warning)', 'var(--das-warning-soft)'],
    ] as [$label, $val, $icon, $color, $bg])
    <div class="col-6 col-md-3">
      <div class="das-stat">
        <div class="p-3 d-flex align-items-center gap-3">
          <div style="width:42px;height:42px;border-radius:8px;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="ti {{ $icon }}" style="font-size:1.1rem;color:{{ $color }};"></i>
          </div>
          <div>
            <div class="fw-bold text-white" style="font-size:.95rem;">{{ $val }}</div>
            <div style="font-size:.72rem;color:#555;text-transform:uppercase;letter-spacing:.4px;">{{ $label }}</div>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

{{-- ── MODAL DETAIL HARI ────────────────────────────── --}}
<div class="modal fade" id="modalDayDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content das-modal shadow-lg">
      <div class="das-modal-head d-flex align-items-center justify-content-between">
        <h5 class="das-modal-title">
          <i class="ti tabler-calendar-event me-2 text-primary"></i>
          Detail Absensi — <span id="modal-day-date"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="das-modal-body">
        <div class="row g-2 text-center mb-3">
          @foreach ([
            ['md-hadir',    'var(--das-success)',  'var(--das-success-soft)',  'Hadir'],
            ['md-terlambat','var(--das-warning)',  'var(--das-warning-soft)', 'Terlambat'],
            ['md-sakit',    'var(--das-info)',     'var(--das-info-soft)',    'Sakit'],
            ['md-izin',     'var(--das-primary)',  'var(--das-primary-soft)', 'Izin'],
            ['md-alpha',    'var(--das-danger)',   'var(--das-danger-soft)',  'Alpha'],
            ['md-pct',      '#aaa',                'rgba(255,255,255,.04)',   'Kehadiran'],
          ] as [$id, $color, $bg, $lbl])
          <div class="col-4">
            <div class="das-modal-stat">
              <div class="fw-bold mb-1" id="{{ $id }}" style="font-size:1.4rem;color:{{ $color }};">0{{ $id === 'md-pct' ? '%' : '' }}</div>
              <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;color:#555;">{{ $lbl }}</div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="d-flex gap-2 justify-content-center pt-2">
          <a href="#" id="modal-rekap-link" class="das-btn das-btn--ghost das-btn--sm">
            <i class="ti tabler-file-analytics me-1"></i> Lihat Rekap Harian
          </a>
          <button type="button" class="das-btn das-btn--ghost das-btn--sm" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('page-script')
<script>
const TOTAL_SISWA = {{ $totalSiswa }};

async function showDayDetail(date) {
  try {
    const res  = await fetch(`{{ url('/admin/kalender-absensi/detail') }}?tanggal=${date}`);
    const data = await res.json();

    const d    = new Date(date);
    const opts = { weekday:'long', day:'numeric', month:'long', year:'numeric' };
    document.getElementById('modal-day-date').textContent = d.toLocaleDateString('id-ID', opts);

    document.getElementById('md-hadir').textContent     = data.hadir     ?? 0;
    document.getElementById('md-terlambat').textContent = data.terlambat ?? 0;
    document.getElementById('md-sakit').textContent     = data.sakit     ?? 0;
    document.getElementById('md-izin').textContent      = data.izin      ?? 0;
    document.getElementById('md-alpha').textContent     = data.alpha     ?? 0;

    const hadir = (data.hadir ?? 0) + (data.terlambat ?? 0);
    const pct   = TOTAL_SISWA > 0 ? ((hadir / TOTAL_SISWA) * 100).toFixed(1) : 0;
    document.getElementById('md-pct').textContent = pct + '%';

    document.getElementById('modal-rekap-link').href =
      `{{ route('admin.rekap-harian') }}?tanggal=${date}`;

    new bootstrap.Modal(document.getElementById('modalDayDetail')).show();
  } catch(e) { console.error(e); }
}
</script>
@endpush
