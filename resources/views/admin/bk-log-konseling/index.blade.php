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

    .log-siswa-item:hover, .log-siswa-item.selected-item {
      background: rgba(0, 207, 234, 0.18);
    }

    .selected-siswa-chip-multi-info {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(0, 207, 234, 0.16);
      border: 1px solid rgba(0, 207, 234, 0.35);
      border-radius: 6px;
      padding: 5px 10px;
      color: #fff;
      font-size: 0.82rem;
    }

    .selected-siswa-chip-multi-info .chip-remove-btn {
      color: #ea5455;
      cursor: pointer;
      font-weight: 700;
      font-size: 0.75rem;
      padding: 1px 5px;
      background: rgba(234, 84, 85, 0.15);
      border-radius: 3px;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
    }

    .selected-siswa-chip-multi-info .chip-remove-btn:hover {
      background: rgba(234, 84, 85, 0.35);
      color: #fff;
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
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Konseling Individu</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['individu'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-user fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Konseling Kelompok</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['kelompok'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-users fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Bulan Ini</span>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['bulan_ini'] ?? 0) }} Sesi</h4>
                </div>
                <div class="avatar avatar-md bg-label-warning rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-calendar-event fs-3 text-warning"></i>
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
                    <th>Ringkasan Masalah</th>
                    <th>Kategori Sesi</th>
                    <th>Status Tindak Lanjut</th>
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
                        <td><span class="text-white">{{ $item->konselor?->nama_lengkap ?? 'Guru BK' }}</span></td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ \Illuminate\Support\Str::limit($item->ringkasan_masalah ?? '—', 45) }}</span>
                            <small class="text-white-50">{{ \Illuminate\Support\Str::limit($item->hasil_konseling ?? '—', 45) }}</small>
                        </td>
                        <td><span class="badge bg-label-info">{{ ucfirst(str_replace('_', ' ', $item->jenis_konseling)) }}</span></td>
                        <td>
                            @php
                                $statusBadge = match($item->status_tindak_lanjut) {
                                    'selesai' => 'success',
                                    'proses' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-label-{{ $statusBadge }}">
                                {{ ucfirst($item->status_tindak_lanjut ?? 'belum') }}
                            </span>
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
                    {{-- Multi-Select Live Search Siswa --}}
                    <div class="col-12 col-md-6 position-relative" id="wrapperLogSiswaSearch">
                        <label class="form-label fw-medium text-white">Pilih Siswa <span class="text-danger">*</span></label>
                        
                        <div id="hiddenLogSiswaInputsContainer">
                            <input type="hidden" name="siswa_id" value="" required>
                        </div>

                        <div id="logSiswaSearchBoxContainer">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti tabler-search"></i></span>
                                <input type="text" id="searchLogSiswa" class="form-control" placeholder="Cari & pilih siswa (bisa lebih dari 1)..." autocomplete="off">
                            </div>
                            <div id="logSiswaSearchResultsList" class="log-siswa-search-results d-none"></div>
                        </div>
                        <div id="selectedLogSiswaChipsContainer" class="d-flex flex-wrap gap-2 mt-2"></div>
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
                        <label class="form-label fw-medium text-white">Ringkasan Masalah <span class="text-danger">*</span></label>
                        <input type="text" name="ringkasan_masalah" class="form-control bg-dark text-white border-secondary" placeholder="Topik atau ringkasan masalah konseling..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Hasil & Evaluasi Konseling</label>
                        <textarea name="hasil_konseling" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Catatan perkembangan atau hasil konseling..."></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Rencana Tindak Lanjut</label>
                        <input type="text" name="rencana_tindak_lanjut" class="form-control bg-dark text-white border-secondary" placeholder="Langkah penanganan berikutnya...">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Status Tindak Lanjut</label>
                        <select name="status_tindak_lanjut" class="form-select bg-dark text-white border-secondary">
                            <option value="belum">Belum</option>
                            <option value="proses">Dalam Proses</option>
                            <option value="selesai">Selesai</option>
                        </select>
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

@php
    $logSiswaMapData = json_encode($siswas->map(function($s) {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'nis' => $s->nis ?? '',
            'kelas' => $s->kelas?->nama ?? 'Tanpa Kelas'
        ];
    })->values());
@endphp

<script>
    const _allLogSiswas = {!! $logSiswaMapData !!};
    let _selectedLogSiswaMap = new Map();

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
                const isSelected = _selectedLogSiswaMap.has(s.id);
                const safeNama = s.nama.replace(/'/g, "\\'");
                const safeKelas = s.kelas.replace(/'/g, "\\'");
                
                html += `
                    <div class="log-siswa-item ${isSelected ? 'selected-item' : ''}" onclick="toggleSelectLogSiswa(${s.id}, '${safeNama}', '${safeKelas}', '${s.nis}')">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center ${isSelected ? 'bg-label-success' : 'bg-label-info'}" style="width: 28px; height: 28px;">
                                <i class="ti ${isSelected ? 'tabler-check text-success' : 'tabler-user text-info'}" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <span class="fw-semibold text-white d-block" style="font-size: 0.85rem;">${s.nama}</span>
                                <small class="text-white-50" style="font-size: 0.72rem;">NIS: ${s.nis || '-'}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: rgba(0, 207, 234, 0.18); color: #00cfe8; border: 1px solid rgba(0, 207, 234, 0.35); font-size: 0.72rem;">${s.kelas}</span>
                            ${isSelected ? '<span class="badge bg-success" style="font-size: 0.65rem;">Terpilih</span>' : ''}
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        }
        listContainer.classList.remove('d-none');
    }

    function toggleSelectLogSiswa(id, nama, kelas, nis) {
        if (_selectedLogSiswaMap.has(id)) {
            _selectedLogSiswaMap.delete(id);
        } else {
            _selectedLogSiswaMap.set(id, { id, nama, kelas, nis });
        }
        updateSelectedLogSiswaUI();
        renderLogSiswaSearchResults(document.getElementById('searchLogSiswa').value);
    }

    function removeSelectedLogSiswa(id) {
        _selectedLogSiswaMap.delete(id);
        updateSelectedLogSiswaUI();
        renderLogSiswaSearchResults(document.getElementById('searchLogSiswa').value);
    }

    function updateSelectedLogSiswaUI() {
        const chipsContainer = document.getElementById('selectedLogSiswaChipsContainer');
        const inputsContainer = document.getElementById('hiddenLogSiswaInputsContainer');
        
        let chipsHtml = '';
        let inputsHtml = '';

        _selectedLogSiswaMap.forEach((s) => {
            inputsHtml += `<input type="hidden" name="siswa_id[]" value="${s.id}">`;
            chipsHtml += `
                <div class="selected-siswa-chip-multi-info">
                    <i class="ti tabler-user text-info"></i>
                    <span class="fw-semibold">${s.nama}</span>
                    <small class="text-white-50">(${s.kelas})</small>
                    <span class="chip-remove-btn" onclick="event.stopPropagation(); removeSelectedLogSiswa(${s.id})" title="Hapus Siswa">
                        <i class="ti tabler-x"></i>
                    </span>
                </div>
            `;
        });

        if (_selectedLogSiswaMap.size === 0) {
            inputsHtml = `<input type="hidden" name="siswa_id" value="" required>`;
        }

        inputsContainer.innerHTML = inputsHtml;
        chipsContainer.innerHTML = chipsHtml;
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
