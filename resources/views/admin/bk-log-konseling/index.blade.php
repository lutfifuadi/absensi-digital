@extends('layouts/layoutMaster')

@section('title', 'Jurnal & Log Bimbingan Konseling')

@section('page-style')
<link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.2">
<style>
    .log-siswa-search-results {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      z-index: 1090;
      background: #18182c;
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
      max-height: 230px;
      overflow-y: auto;
      backdrop-filter: blur(16px);
      margin-top: 4px;
    }

    .log-siswa-item {
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.15s ease;
    }

    .log-siswa-item:hover {
      background: rgba(0, 207, 234, 0.18);
    }

    .selected-siswa-chip-info {
      display: inline-flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      width: 100%;
      background: rgba(0, 207, 234, 0.14);
      border: 1px solid rgba(0, 207, 234, 0.35);
      border-radius: 8px;
      padding: 8px 14px;
      color: #fff;
      font-size: 0.88rem;
    }

    .selected-siswa-chip-info .chip-remove {
      color: #ea5455;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.78rem;
      padding: 4px 10px;
      background: rgba(234, 84, 85, 0.15);
      border: 1px solid rgba(234, 84, 85, 0.3);
      border-radius: 4px;
      transition: all 0.2s;
    }

    .selected-siswa-chip-info .chip-remove:hover {
      background: rgba(234, 84, 85, 0.3);
    }
</style>
@endsection

@section('content')
<div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
        <div class="das-hero__identity">
            <div class="das-hero__logo-wrapper">
                <div class="das-hero__logo-placeholder d-flex align-items-center justify-content-center">
                    <i class="ti tabler-notebook text-info" style="font-size: 2rem;"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-info"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Log Konseling
                </div>
                <h4 class="das-hero__title text-gradient-gold">Jurnal Bimbingan Konseling</h4>
                <p class="das-hero__subtitle">Catatan harian sesi konseling siswa, penanganan pribadi, dan evaluasi berkala.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-info d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCatatKonseling">
                <i class="ti tabler-plus fs-5"></i>
                <span>Catat Sesi Konseling</span>
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Sesi Selesai</span>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['total'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-messages fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Log Sifat Privat</span>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['privat'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-lock fs-3 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Log Sifat Publik</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['publik'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-eye fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Bulan Ini</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['bulan_ini'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-calendar-event fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Log Konseling --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-info"></i>Daftar Jurnal Bimbingan</h5>
        <form method="GET" action="{{ route('admin.bk-log-konseling.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari siswa atau topik..." value="{{ request('search') }}" style="width: 220px;">
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal & Waktu</th>
                    <th>Siswa</th>
                    <th>Guru Konselor</th>
                    <th>Topik / Ringkasan</th>
                    <th>Kategori Sesi</th>
                    <th>Aksesibilitas</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $item)
                    <tr>
                        <td>
                            <span class="fw-semibold text-white d-block">{{ $item->tanggal_konseling ? $item->tanggal_konseling->format('d M Y') : '—' }}</span>
                            <small class="text-white-50">{{ $item->waktu_mulai ?? '-' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-info">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-white">{{ $item->konselor?->nama_lengkap ?? '—' }}</span></td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ $item->topik }}</span>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($item->ringkasan_hasil ?? '—', 45) }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ ucfirst($item->jenis_konseling) }}</span></td>
                        <td>
                            @if($item->is_privat)
                                <span class="badge bg-label-danger d-inline-flex align-items-center gap-1">
                                    <i class="ti tabler-lock fs-6"></i> Privat (Hanya BK)
                                </span>
                            @else
                                <span class="badge bg-label-success d-inline-flex align-items-center gap-1">
                                    <i class="ti tabler-world fs-6"></i> Publik / Tim
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="text-white-50 small">-</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-notebook-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Jurnal Konseling</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat catatan sesi bimbingan konseling yang tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($logs->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $logs->links() }}
        </div>
    @endif
</div>

{{-- MODAL CATAT KONSELING --}}
<div class="modal fade" id="modalCatatKonseling" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold text-white"><i class="ti tabler-notebook text-info me-2"></i>Catat Sesi Bimbingan Konseling</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bk-log-konseling.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 row g-3 text-white">
                    {{-- Live Search Siswa --}}
                    <div class="col-12 col-md-6 position-relative" id="wrapperLogSiswaSearch">
                        <label class="form-label fw-medium text-white">Pilih Siswa <span class="text-danger">*</span></label>
                        <input type="hidden" name="siswa_id" id="inputLogSiswaId" required>
                        
                        <div id="selectedLogSiswaChipWrap"></div>

                        <div id="logSiswaSearchBoxContainer">
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="ti tabler-search"></i></span>
                                <input type="text" id="searchLogSiswa" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama / NIS / kelas siswa..." autocomplete="off">
                            </div>
                            <div id="logSiswaSearchResultsList" class="log-siswa-search-results d-none"></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Guru Konselor <span class="text-danger">*</span></label>
                        <select name="guru_bk_id" class="form-select bg-dark text-white border-secondary" required>
                            <option value="">-- Pilih Konselor --</option>
                            @foreach($gurus as $g)
                                <option value="{{ $g->id }}">{{ $g->nama_lengkap }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tanggal Konseling <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_konseling" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Jenis Konseling <span class="text-danger">*</span></label>
                        <select name="jenis_konseling" class="form-select bg-dark text-white border-secondary" required>
                            <option value="individu">Individu</option>
                            <option value="kelompok">Kelompok</option>
                            <option value="karir">Bimbingan Karir</option>
                            <option value="kunjungan_rumah">Kunjungan Rumah (Home Visit)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Topik Pembahasan <span class="text-danger">*</span></label>
                        <input type="text" name="topik" class="form-control bg-dark text-white border-secondary" placeholder="Ringkasan topik konseling..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Ringkasan Hasil & Evaluasi</label>
                        <textarea name="ringkasan_hasil" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Catatan perkembangan atau hasil konseling..."></textarea>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_privat" value="1" id="switchPrivat">
                            <label class="form-check-input-label text-white" for="switchPrivat">Tandai sebagai Catatan Privat (Hanya Guru BK)</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info"><i class="ti tabler-device-floppy me-1"></i>Simpan Jurnal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const _allLogSiswas = @json($siswas->map(function($s) {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'nis' => $s->nis ?? '',
            'kelas' => $s->kelas?->nama ?? 'Tanpa Kelas'
        ];
    }));

    function renderLogSiswaSearchResults(query) {
        const listContainer = document.getElementById('logSiswaSearchResultsList');
        if (!listContainer) return;

        const q = (query || '').toLowerCase().trim();
        const filtered = _allLogSiswas.filter(s => 
            s.nama.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q) || s.kelas.toLowerCase().includes(q)
        );

        if (filtered.length === 0) {
            listContainer.innerHTML = '<div class="p-3 text-center text-white-50 small">Siswa tidak ditemukan.</div>';
        } else {
            let html = '';
            filtered.forEach(s => {
                const safeNama = s.nama.replace(/'/g, "\\'");
                const safeKelas = s.kelas.replace(/'/g, "\\'");
                html += `
                    <div class="log-siswa-item" onclick="selectLogSiswa(${s.id}, '${safeNama}', '${safeKelas}', '${s.nis}')">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center bg-label-info" style="width: 28px; height: 28px;">
                                <i class="ti tabler-user text-info" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <span class="fw-semibold text-white d-block" style="font-size: 0.85rem;">${s.nama}</span>
                                <small class="text-white-50" style="font-size: 0.72rem;">NIS: ${s.nis || '-'}</small>
                            </div>
                        </div>
                        <span class="badge" style="background: rgba(0, 207, 234, 0.18); color: #00cfe8; border: 1px solid rgba(0, 207, 234, 0.35); font-size: 0.72rem;">${s.kelas}</span>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        }
        listContainer.classList.remove('d-none');
    }

    function selectLogSiswa(id, nama, kelas, nis) {
        document.getElementById('inputLogSiswaId').value = id;
        document.getElementById('logSiswaSearchResultsList').classList.add('d-none');
        document.getElementById('logSiswaSearchBoxContainer').classList.add('d-none');

        const chipWrap = document.getElementById('selectedLogSiswaChipWrap');
        chipWrap.innerHTML = `
            <div class="selected-siswa-chip-info">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-user-check text-info fs-5"></i>
                    <div>
                        <span class="fw-bold">${nama}</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">${kelas} • NIS: ${nis || '-'}</small>
                    </div>
                </div>
                <span class="chip-remove" onclick="clearSelectedLogSiswa()" title="Ubah Siswa">
                    <i class="ti tabler-x"></i> Ubah
                </span>
            </div>
        `;
    }

    function clearSelectedLogSiswa() {
        document.getElementById('inputLogSiswaId').value = "";
        document.getElementById('selectedLogSiswaChipWrap').innerHTML = "";
        document.getElementById('logSiswaSearchBoxContainer').classList.remove('d-none');
        document.getElementById('searchLogSiswa').value = "";
        renderLogSiswaSearchResults('');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchLogSiswa');
        if (searchInput) {
            searchInput.addEventListener('focus', function () { renderLogSiswaSearchResults(this.value); });
            searchInput.addEventListener('click', function () { renderLogSiswaSearchResults(this.value); });
            searchInput.addEventListener('input', function () { renderLogSiswaSearchResults(this.value); });
        }
    });

    document.addEventListener('click', function (e) {
        const searchBox = document.getElementById('wrapperLogSiswaSearch');
        if (searchBox && !searchBox.contains(e.target)) {
            const resultsList = document.getElementById('logSiswaSearchResultsList');
            if (resultsList) resultsList.classList.add('d-none');
        }
    });
</script>
@endsection
