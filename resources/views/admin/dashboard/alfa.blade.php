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
    @if($isHoliday)
    <div class="das-panel mb-4" style="text-align: center; padding: 2.5rem 1rem; border-color: rgba(255, 171, 0, 0.25);">
        <div style="font-size: 3rem; margin-bottom: 0.5rem; opacity: 0.8;" class="text-warning">
            <i class="ti tabler-calendar-off"></i>
        </div>
        <h5 class="text-white fw-bold mb-2">Hari Libur — {{ $holidayName ?? 'Tidak Ada Presensi' }}</h5>
        <p class="text-white-50 mb-0">Tanggal {{ \Carbon\Carbon::parse($filterTanggalAkhir)->translatedFormat('d F Y') }} merupakan hari libur. Siswa tidak dihitung sebagai belum absen.</p>
    </div>
    @endif

    <div class="row g-4 mb-4">

        {{-- Summary Card --}}
        <div class="col-lg-4">
            <div class="alfa-stat-card h-100">
                <div>
                    <h6 class="text-white-50 fw-semibold small mb-3">BELUM ABSEN HARI INI</h6>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="alfa-stat-icon">
                            <i class="ti tabler-user-off"></i>
                        </div>
                        <span class="alfa-stat-number">{{ $totalBelumAbsenHariIni }}</span>
                    </div>

                    {{-- Indikator Tren Perbandingan Before vs After --}}
                    <div class="mb-3">
                        @if($deltaBelumAbsen < 0)
                            <span class="badge bg-label-success d-inline-flex align-items-center gap-1 px-2 py-1.5" style="font-size: 0.78rem;">
                                <i class="ti tabler-trending-down fs-6"></i>
                                <span>Turun {{ abs($deltaBelumAbsen) }} siswa dibanding {{ $prevDateLabel }}</span>
                            </span>
                        @elseif($deltaBelumAbsen > 0)
                            <span class="badge bg-label-danger d-inline-flex align-items-center gap-1 px-2 py-1.5" style="font-size: 0.78rem;">
                                <i class="ti tabler-trending-up fs-6"></i>
                                <span>Naik {{ $deltaBelumAbsen }} siswa dibanding {{ $prevDateLabel }}</span>
                            </span>
                        @else
                            <span class="badge bg-label-secondary d-inline-flex align-items-center gap-1 px-2 py-1.5" style="font-size: 0.78rem;">
                                <i class="ti tabler-minus fs-6"></i>
                                <span>Sama dibanding {{ $prevDateLabel }} ({{ $totalBelumAbsenKemarin }} siswa)</span>
                            </span>
                        @endif
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
         SECTION 3: DUAL BAR CHART (BEFORE VS AFTER + TAB PER TINGKATAN)
    ═══════════════════════════════════════════════════════ --}}
    <div class="das-panel mb-4">
        <div class="das-panel__head d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h6 class="das-panel__title mb-0">
                <span class="das-panel__icon-dot --danger"></span>
                Kelas dengan Jumlah Belum Absen Tertinggi
            </h6>
            
            <div class="d-flex align-items-center gap-3 flex-wrap">
                {{-- Navigasi Tab Per Tingkatan --}}
                <div class="btn-group btn-group-sm" role="group" aria-label="Filter Tingkatan Kelas">
                    <button type="button" class="btn btn-danger ting-tab-btn active" data-tingkat="semua" onclick="switchTingkatTab('semua')">
                        Semua
                    </button>
                    @foreach($tingkatOptions as $tkt)
                    <button type="button" class="btn btn-outline-danger ting-tab-btn" data-tingkat="{{ $tkt }}" onclick="switchTingkatTab('{{ $tkt }}')">
                        Kelas {{ $tkt }}
                    </button>
                    @endforeach
                </div>

                <span class="das-chip --secondary small">
                    <i class="ti tabler-arrows-diff me-1"></i> Perbandingan: {{ $prevDateLabel }} vs {{ $currentDateLabel }}
                </span>
            </div>
        </div>
        <div class="das-panel__body">
            <p class="text-white-50 small mb-3">
                <i class="ti tabler-pointer me-1 text-info"></i>
                Grafik membandingkan siswa yang belum absen per kelas. <strong>Klik pada batang kelas mana saja</strong> untuk langsung menyaring daftar siswa belum absen di tabel bawah.
            </p>
            <div class="alfa-chart-wrap">
                <div id="emptyBarChartNotice" class="alfa-empty-chart d-none">
                    <i class="ti tabler-chart-bar-off" style="font-size: 2.5rem;"></i>
                    <span class="small">Semua siswa pada tingkatan ini sudah absen. Tidak ada data.</span>
                </div>
                <div id="barChart" style="cursor: pointer;"></div>
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
                @if($filterKelas)
                <a href="{{ route('admin.dashboard.belum-absen', array_filter(['start_date' => request('start_date'), 'end_date' => request('end_date')])) }}"
                   class="btn das-btn --warning btn-sm d-inline-flex align-items-center gap-1">
                    <i class="ti tabler-x"></i> Reset Filter Kelas
                </a>
                @endif
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
            <div id="detailTableContainer">
                @include('admin.dashboard.alfa-table')
            </div>
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
        const barChartTingkatData = @json($barChartTingkatData);
        const kelasIdMap            = @json($kelasIdMap);
        const prevDateLabel         = @json($prevDateLabel);
        const currentDateLabel      = @json($currentDateLabel);
        const lineChartLabels       = @json($lineChartLabels);
        const lineChartData         = @json($lineChartData);

        let barChartInstance = null;

        // Auto-scroll ke detail table jika ada hash atau filter kelas aktif
        if (window.location.hash === '#detailTableSection' || '{{ $filterKelas }}' !== '') {
            const detailSec = document.getElementById('detailTableSection');
            if (detailSec && window.location.hash === '#detailTableSection') {
                setTimeout(() => {
                    detailSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 200);
            }
        }

        // Helper filter kelas saat batang grafik diklik
        function loadDetailTable(url) {
            const container = document.getElementById('detailTableContainer');
            if (!container) return;

            // Apply loading opacity
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';

                // Dynamic header updates from helper elements inside partial view
                const ajaxTotalSiswa = document.getElementById('ajaxTotalSiswaVal');
                const ajaxFilterKelas = document.getElementById('ajaxFilterKelasVal');
                const ajaxKelasNama = document.getElementById('ajaxKelasNamaVal');
                
                // Update badge total
                const totalBadge = document.querySelector('#detailTableSection .das-chip.--danger');
                if (totalBadge && ajaxTotalSiswa) {
                    totalBadge.textContent = ajaxTotalSiswa.textContent + ' Siswa';
                }

                // Update filter info text & reset button
                const infoText = document.querySelector('#detailTableSection .filter-info-bar span');
                if (infoText && ajaxKelasNama) {
                    const originalParts = infoText.innerHTML.split('&nbsp;|&nbsp;');
                    const datePart = originalParts.length > 1 ? originalParts[1] : '';
                    infoText.innerHTML = ajaxKelasNama.textContent + (datePart ? '&nbsp;|&nbsp;' + datePart : '');
                }

                // Update reset button visibility/link
                const headerActions = document.querySelector('#detailTableSection .d-flex.align-items-center.gap-2.flex-wrap');
                let resetBtn = document.querySelector('#detailTableSection a.btn.das-btn.--warning');
                
                if (ajaxFilterKelas && ajaxFilterKelas.textContent.trim() !== '') {
                    if (!resetBtn && headerActions) {
                        resetBtn = document.createElement('a');
                        resetBtn.className = 'btn das-btn --warning btn-sm d-inline-flex align-items-center gap-1';
                        resetBtn.id = 'resetFilterKelasBtn';
                        resetBtn.innerHTML = '<i class="ti tabler-x"></i> Reset Filter Kelas';
                        headerActions.insertBefore(resetBtn, headerActions.firstChild);
                        
                        resetBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            const cleanUrl = new URL(window.location.href);
                            cleanUrl.searchParams.delete('kelas_id');
                            window.history.pushState(null, '', cleanUrl.toString());
                            loadDetailTable(cleanUrl.toString());
                        });
                    }
                } else {
                    if (resetBtn) {
                        resetBtn.remove();
                    }
                    const dynamicResetBtn = document.getElementById('resetFilterKelasBtn');
                    if (dynamicResetBtn) {
                        dynamicResetBtn.remove();
                    }
                }
            })
            .catch(err => {
                console.error(err);
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            });
        }

        // Handle pagination links click inside container
        const detailContainer = document.getElementById('detailTableContainer');
        if (detailContainer) {
            detailContainer.addEventListener('click', function (e) {
                const link = e.target.closest('a');
                if (link && (link.classList.contains('page-link') || link.closest('.pagination'))) {
                    e.preventDefault();
                    const href = link.getAttribute('href');
                    if (href && href !== '#') {
                        loadDetailTable(href);
                        document.getElementById('detailTableSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        }

        // Handle existing static reset button if present on page load
        const staticResetBtn = document.querySelector('#detailTableSection a.btn.das-btn.--warning');
        if (staticResetBtn) {
            staticResetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const cleanUrl = new URL(window.location.href);
                cleanUrl.searchParams.delete('kelas_id');
                window.history.pushState(null, '', cleanUrl.toString());
                loadDetailTable(cleanUrl.toString());
            });
        }

        function filterByClassName(className) {
            if (!className) return;
            const classId = kelasIdMap[className];
            if (classId) {
                const url = new URL(window.location.href);
                url.searchParams.set('kelas_id', classId);
                window.history.pushState(null, '', url.toString());
                loadDetailTable(url.toString());
                document.getElementById('detailTableSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // ── Dual Bar Chart (Before vs After + Tab per Tingkatan) ─
        if (document.querySelector('#barChart')) {
            const initialData = barChartTingkatData['semua'] || { labels: [], current: [], prev: [] };

            const emptyNotice = document.getElementById('emptyBarChartNotice');
            if (initialData.labels.length === 0 && emptyNotice) {
                emptyNotice.classList.remove('d-none');
            }

            barChartInstance = new ApexCharts(document.querySelector('#barChart'), {
                series: [
                    { name: 'Sebelumnya (' + prevDateLabel + ')', data: initialData.prev },
                    { name: 'Sekarang (' + currentDateLabel + ')', data: initialData.current }
                ],
                chart: {
                    type: 'bar',
                    height: 340,
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    background: 'transparent',
                    events: {
                        dataPointSelection: function(event, chartContext, config) {
                            const selectedIndex = config.dataPointIndex;
                            const categories = config.w.globals.labels;
                            if (categories && selectedIndex !== undefined && selectedIndex >= 0) {
                                filterByClassName(categories[selectedIndex]);
                            }
                        },
                        click: function(event, chartContext, config) {
                            // Backup click event for category label / bar click
                            if (config && config.dataPointIndex !== undefined && config.dataPointIndex >= 0) {
                                const categories = config.w.globals.labels;
                                if (categories && categories[config.dataPointIndex]) {
                                    filterByClassName(categories[config.dataPointIndex]);
                                }
                            }
                        }
                    }
                },
                colors: ['rgba(255, 255, 255, 0.35)', '#ea5455'],
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: '55%',
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: { colors: ['#fff'], fontSize: '10px' }
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                    labels: { colors: 'rgba(255, 255, 255, 0.7)' }
                },
                stroke: { show: true, width: 2, colors: ['transparent'] },
                xaxis: {
                    categories: initialData.labels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: 'rgba(255,255,255,0.6)', fontSize: '12px' } }
                },
                yaxis: {
                    title: { text: 'Jumlah Siswa Belum Absen', style: { color: 'rgba(255,255,255,0.4)' } },
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
            barChartInstance.render();
        }

        // ── Tab Switcher Function ──────────────────────────────
        window.switchTingkatTab = function(tingkatKey) {
            document.querySelectorAll('.ting-tab-btn').forEach(btn => {
                if (btn.getAttribute('data-tingkat') === tingkatKey) {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger', 'active');
                } else {
                    btn.classList.remove('btn-danger', 'active');
                    btn.classList.add('btn-outline-danger');
                }
            });

            const currentData = barChartTingkatData[tingkatKey] || { labels: [], current: [], prev: [] };
            const emptyNotice = document.getElementById('emptyBarChartNotice');

            if (currentData.labels.length === 0) {
                if (emptyNotice) emptyNotice.classList.remove('d-none');
            } else {
                if (emptyNotice) emptyNotice.classList.add('d-none');
            }

            if (barChartInstance) {
                barChartInstance.updateOptions({
                    xaxis: { categories: currentData.labels }
                });
                barChartInstance.updateSeries([
                    { name: 'Sebelumnya (' + prevDateLabel + ')', data: currentData.prev },
                    { name: 'Sekarang (' + currentDateLabel + ')', data: currentData.current }
                ]);
            }
        };

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
