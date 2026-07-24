@extends('layouts/layoutMaster')

@section('title', 'Dashboard Siswa Belum Absen')

@section('vendor-style')
    @vite([
        'resources/assets/vendor/libs/select2/select2.scss',
        'resources/assets/vendor/libs/apex-charts/apex-charts.scss',
        'resources/assets/vendor/libs/animate-css/animate.scss',
    ])
@endsection

@section('vendor-script')
    @vite([
        'resources/assets/vendor/libs/select2/select2.js',
        'resources/assets/vendor/libs/apex-charts/apexcharts.js',
    ])
@endsection

@section('page-style')
<style>
    /* ── Summary Stat Card ──────────────────────────────────── */
    .alfa-stat-card {
        background: rgba(234, 84, 85, 0.07);
        border: 1px solid rgba(234, 84, 85, 0.2);
        border-radius: var(--das-radius, 8px);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: box-shadow 0.2s ease;
    }
    .alfa-stat-card:hover {
        box-shadow: 0 0 20px rgba(234, 84, 85, 0.12);
    }
    .alfa-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(234, 84, 85, 0.12);
        color: var(--das-danger, #ea5455);
        font-size: 1.8rem;
        flex-shrink: 0;
    }
    .alfa-stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: var(--das-danger, #ea5455);
        line-height: 1;
    }
    .alfa-stat-label {
        font-size: 0.78rem;
        color: rgba(234, 84, 85, 0.75);
        background: rgba(234, 84, 85, 0.1);
        padding: 0.3rem 0.75rem;
        border-radius: 30px;
        display: inline-block;
        margin-top: 0.75rem;
    }

    /* ── Chart Containers ───────────────────────────────────── */
    .alfa-chart-wrap {
        min-height: 320px;
    }
    .alfa-empty-chart {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        color: rgba(255,255,255,0.3);
        flex-direction: column;
        gap: 0.5rem;
    }

    /* ── Filter Row ─────────────────────────────────────────── */
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
        box-shadow: none !important;
    }
    .form-control::placeholder {
        color: rgba(255,255,255,0.35) !important;
    }
    .form-select option {
        background: #1a1a2e;
        color: #ccc;
    }

    /* ── Table row hover ─────────────────────────────────────── */
    .alfa-row-hover {
        transition: background 0.15s ease;
    }
    .alfa-row-hover:hover {
        background: rgba(234, 84, 85, 0.04) !important;
    }

    /* ── Filter badge (info bar) ─────────────────────────────── */
    .filter-info-bar {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 6px;
        padding: 0.4rem 0.85rem;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.5);
    }

    /* ── Avatar initials ─────────────────────────────────────── */
    .alfa-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(115, 103, 240, 0.2);
        color: #a5a2f7;
        font-weight: 700;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
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
                        <i class="ti tabler-user-off text-info"></i>
                    </div>
                    <div class="das-hero__logo-glow"></div>
                </div>

                <div class="das-hero__meta">
                    <div class="das-hero__badge">
                        <span class="pulse-dot"></span>
                        Dashboard / Siswa Belum Absen
                    </div>
                    <h4 class="das-hero__title text-gradient-gold">Dashboard Pemantauan Belum Absen</h4>
                    <p class="das-hero__subtitle">Pantau siswa yang <span class="text-danger fw-bold">belum melakukan absensi</span> hari ini secara real-time.</p>
                </div>
            </div>

            <div class="das-hero__actions">
                {{-- Filter Form --}}
                <form method="GET" action="{{ route('admin.dashboard.belum-absen') }}" class="d-flex flex-wrap gap-2 align-items-center">
                    <select name="kelas_id" class="select2 form-select" style="width: 200px;" data-placeholder="Semua Kelas">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ $filterKelas == $kelas->id ? 'selected' : '' }}>{{ $kelas->nama }}</option>
                        @endforeach
                    </select>
                    <input type="date" name="start_date" value="{{ $filterTanggalMulai }}"
                        class="form-control" style="width: 145px; height: 38px; font-size: 0.85rem; color-scheme: dark;"
                        onchange="this.form.submit()">
                    <span class="text-white-50">—</span>
                    <input type="date" name="end_date" value="{{ $filterTanggalAkhir }}"
                        class="form-control" style="width: 145px; height: 38px; font-size: 0.85rem; color-scheme: dark;"
                        onchange="this.form.submit()">
                    <button type="submit" class="btn das-btn --info">
                        <i class="ti tabler-search me-1"></i> Filter
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         SECTION 2: SUMMARY + LINE CHART
    ═══════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">

        {{-- Summary Card --}}
        <div class="col-lg-4">
            <div class="alfa-stat-card h-100">
                <div>
                    <h6 class="text-white-50 fw-semibold small mb-3">BELUM ABSEN HARI INI</h6>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="alfa-stat-icon">
                            <i class="ti tabler-user-off"></i>
                        </div>
                        <span class="alfa-stat-number">{{ $totalBelumAbsenHariIni }}</span>
                    </div>
                    <span class="alfa-stat-label">dari {{ $totalSiswaAktif }} siswa aktif</span>
                </div>

                <div class="mt-4">
                    <div class="filter-info-bar d-flex align-items-center gap-2">
                        <i class="ti tabler-filter fs-6 text-danger"></i>
                        <span>
                            <strong class="text-white-50">{{ $filterKelas ? ($kelasList->firstWhere('id', $filterKelas)?->nama ?? 'Semua Kelas') : 'Semua Kelas' }}</strong>
                            &nbsp;|&nbsp;
                            {{ \Carbon\Carbon::parse($filterTanggalMulai)->format('d M Y') }}
                            –
                            {{ \Carbon\Carbon::parse($filterTanggalAkhir)->format('d M Y') }}
                        </span>
                    </div>

                    <a href="#detailTableSection"
                       onclick="document.getElementById('detailTableSection').scrollIntoView({behavior: 'smooth'}); return false;"
                       class="btn das-btn --danger w-100 mt-3 d-flex justify-content-between align-items-center">
                        <span>Lihat Detail Daftar Siswa</span>
                        <i class="ti tabler-arrow-down"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Line Chart --}}
        <div class="col-lg-8">
            <div class="das-panel h-100">
                <div class="das-panel__head">
                    <h6 class="das-panel__title">
                        <span class="das-panel__icon-dot --danger"></span>
                        Tren Belum Absen
                        <span class="das-chip --danger ms-2">{{ count($lineChartData) }} Hari</span>
                    </h6>
                </div>
                <div class="das-panel__body">
                    <div class="alfa-chart-wrap">
                        @if(count($lineChartData) == 0)
                            <div class="alfa-empty-chart">
                                <i class="ti tabler-chart-line-off" style="font-size: 2.5rem;"></i>
                                <span class="small">Tidak ada data tren untuk periode ini.</span>
                            </div>
                        @endif
                        <div id="lineChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         SECTION 3: BAR CHART
    ═══════════════════════════════════════════════════════ --}}
    <div class="das-panel mb-4">
        <div class="das-panel__head">
            <h6 class="das-panel__title">
                <span class="das-panel__icon-dot --danger"></span>
                Kelas dengan Jumlah Belum Absen Tertinggi
            </h6>
        </div>
        <div class="das-panel__body">
            <p class="text-white-50 small mb-3">
                <i class="ti tabler-info-circle me-1"></i>
                Grafik batang menampilkan distribusi siswa yang belum absen per kelas.
            </p>
            <div class="alfa-chart-wrap">
                @if(count($barChartData) == 0)
                    <div class="alfa-empty-chart">
                        <i class="ti tabler-chart-bar-off" style="font-size: 2.5rem;"></i>
                        <span class="small">Semua siswa sudah absen. Tidak ada data.</span>
                    </div>
                @endif
                <div id="barChart"></div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         SECTION 4: DATA TABLE
    ═══════════════════════════════════════════════════════ --}}
    <div class="das-panel" id="detailTableSection">
        <div class="das-panel__header border-bottom py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="border-color: rgba(255,255,255,0.08) !important;">
            <h6 class="das-panel__title mb-0 d-flex align-items-center gap-2">
                <i class="ti tabler-list text-danger"></i> Detail Siswa Belum Absen
            </h6>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="filter-info-bar d-flex align-items-center gap-2">
                    <i class="ti tabler-filter fs-6 text-danger"></i>
                    <span>
                        {{ $filterKelas ? ($kelasList->firstWhere('id', $filterKelas)?->nama ?? 'Semua Kelas') : 'Semua Kelas' }}
                        &nbsp;|&nbsp;
                        {{ \Carbon\Carbon::parse($filterTanggalMulai)->format('d M Y') }}
                        –
                        {{ \Carbon\Carbon::parse($filterTanggalAkhir)->format('d M Y') }}
                    </span>
                </div>
                <span class="das-chip --danger">
                    {{ method_exists($detailBelumAbsen, 'total') ? $detailBelumAbsen->total() : $detailBelumAbsen->count() }} Siswa
                </span>
            </div>
        </div>
        <div class="das-panel__body p-0">
            <div class="table-responsive">
                <table class="das-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>No. HP Orang Tua</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailBelumAbsen as $index => $siswa)
                        <tr class="alfa-row-hover">
                            <td>{{ $detailBelumAbsen->firstItem() + $index }}</td>
                            <td class="fw-semibold text-white">{{ $siswa->nis ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="alfa-avatar">
                                        {{ substr($siswa->nama_lengkap ?? 'U', 0, 1) }}
                                    </div>
                                    <span>{{ $siswa->nama_lengkap ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="das-chip --info">
                                    {{ $siswa->kelas->nama ?? '-' }}
                                </span>
                            </td>
                            <td class="text-white-50">
                                {{ $siswa->no_hp_ortu ?? '-' }}
                            </td>
                            <td class="text-center">
                                @php
                                    $noOrtu = preg_replace('/[^0-9]/', '', $siswa->no_hp_ortu ?? '');
                                @endphp
                                @if($noOrtu)
                                <a href="https://wa.me/{{ $noOrtu }}" target="_blank" rel="noopener noreferrer"
                                   class="btn das-btn --success btn-sm d-inline-flex align-items-center gap-1">
                                    <i class="ti tabler-brand-whatsapp"></i>
                                    <span>Hubungi Wali</span>
                                </a>
                                @else
                                <span class="das-chip --secondary">Tidak Ada No. HP</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                    <i class="ti tabler-user-check" style="font-size: 2.5rem; color: rgba(40,199,111,0.3);"></i>
                                    <p class="text-muted small mb-0">Semua siswa sudah absen. Tidak ada yang perlu ditindaklanjuti.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($detailBelumAbsen, 'links') && $detailBelumAbsen->hasPages())
            <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
                {{ $detailBelumAbsen->links() }}
            </div>
            @endif
        </div>
    </div>

@endsection

@section('page-script')
<script type="module">
    $(function() {
        const select2 = $('.select2');
        if (select2.length) {
            select2.each(function () {
                var $this = $(this);
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: 'Semua Kelas',
                    dropdownParent: $this.parent(),
                    width: 'resolve'
                });
                $this.on('change', function() {
                    $(this).closest('form').trigger('submit');
                });
            });
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const barChartLabels = @json($barChartLabels);
        const barChartData   = @json($barChartData);
        const lineChartLabels = @json($lineChartLabels);
        const lineChartData   = @json($lineChartData);

        // ── Bar Chart ──────────────────────────────────────────
        if (document.querySelector('#barChart') && barChartData.length > 0) {
            const barChart = new ApexCharts(document.querySelector('#barChart'), {
                series: [{ name: 'Belum Absen', data: barChartData }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent',
                },
                colors: ['#ea5455'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: '50%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { colors: ['#fff'], fontSize: '11px' }
                },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: barChartLabels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: 'rgba(255,255,255,0.45)', fontSize: '12px' } }
                },
                yaxis: {
                    title: { text: 'Jumlah Siswa', style: { color: 'rgba(255,255,255,0.4)' } },
                    labels: { style: { colors: 'rgba(255,255,255,0.45)' } }
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.06)',
                    strokeDashArray: 4,
                },
                fill: { opacity: 1 },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: val => val + ' Siswa' }
                }
            });
            barChart.render();
        }

        // ── Line Chart ─────────────────────────────────────────
        if (document.querySelector('#lineChart') && lineChartData.length > 0) {
            const lineChart = new ApexCharts(document.querySelector('#lineChart'), {
                series: [{ name: 'Belum Absen', data: lineChartData }],
                chart: {
                    height: 300,
                    type: 'line',
                    zoom: { enabled: false },
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent',
                },
                dataLabels: { enabled: false },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#ea5455']
                },
                grid: {
                    borderColor: 'rgba(255,255,255,0.06)',
                    strokeDashArray: 4,
                },
                xaxis: {
                    categories: lineChartLabels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: 'rgba(255,255,255,0.45)', fontSize: '11px' } }
                },
                yaxis: {
                    labels: { style: { colors: 'rgba(255,255,255,0.45)' } }
                },
                markers: {
                    size: 5,
                    colors: ['#1a1a2e'],
                    strokeColors: '#ea5455',
                    strokeWidth: 2,
                    hover: { size: 7 }
                },
                tooltip: {
                    theme: 'dark',
                    y: { formatter: val => val + ' Siswa' }
                }
            });
            lineChart.render();
        }
    });
</script>
@endsection
