@extends('layouts/layoutMaster')

@section('title', 'Pemutihan Poin Siswa')

@section('page-style')
<link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.2">
<style>
    .pemutihan-siswa-search-results {
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

    .pemutihan-siswa-item {
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.15s ease;
    }

    .pemutihan-siswa-item:hover, .pemutihan-siswa-item.selected-item {
      background: rgba(40, 199, 111, 0.18);
    }

    .selected-siswa-chip-multi-success {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(40, 199, 111, 0.16);
      border: 1px solid rgba(40, 199, 111, 0.35);
      border-radius: 6px;
      padding: 5px 10px;
      color: #fff;
      font-size: 0.82rem;
    }

    .selected-siswa-chip-multi-success .chip-remove-btn {
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

    .selected-siswa-chip-multi-success .chip-remove-btn:hover {
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
                    <i class="ti tabler-sparkles text-success" style="font-size: 2rem;"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-success"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Pemutihan Poin
                </div>
                <h4 class="das-hero__title text-gradient-gold">Pemutihan Poin Pelanggaran</h4>
                <p class="das-hero__subtitle">Prosedur pengurangan poin pelanggaran siswa melalui program kebaikan, prestasi, atau sanksi sosial edukatif.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-success d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEksekusiPemutihan">
                <i class="ti tabler-minus fs-5"></i>
                <span>Eksekusi Pemutihan</span>
            </button>
        </div>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Poin Diputihkan</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['total_poin'] ?? 0) }} Poin</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-arrow-down-left fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Siswa Terbantu</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['siswa_count'] ?? 0) }} Siswa</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-users fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xl-4">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Transaksi Log</span>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['total_log'] ?? 0) }} Log</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-heart fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Table Pemutihan Poin --}}
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="ti tabler-list me-2 text-success"></i>Daftar Pemutihan Poin</h5>
        <form method="GET" action="{{ route('admin.bk-pemutihan.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari siswa..." value="{{ request('search') }}" style="width: 200px;">
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Siswa</th>
                    <th>Poin Dikurangi</th>
                    <th>Keterangan / Alasan</th>
                    <th>Petugas / BK</th>
                    <th class="text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $item)
                    <tr>
                        <td>{{ $item->tanggal_pemutihan ? $item->tanggal_pemutihan->format('d M Y') : '—' }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-label-success fw-bold">-{{ $item->poin_yang_diputihkan }} Poin</span></td>
                        <td><span class="text-white">{{ $item->alasan_pemutihan }}</span></td>
                        <td><span class="text-white-50">{{ $item->diprosesOleh?->nama_lengkap ?? 'Admin BK' }}</span></td>
                        <td class="text-end">
                            <span class="text-success small fw-semibold"><i class="ti tabler-circle-check me-1"></i>Approved</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ti tabler-sparkles-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Pemutihan Poin</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat riwayat pemutihan atau pemotongan poin pelanggaran siswa.</p>
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

{{-- MODAL EKSEKUSI PEMUTIHAN --}}
<div class="modal fade" id="modalEksekusiPemutihan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold text-white"><i class="ti tabler-sparkles text-success me-2"></i>Eksekusi Pemutihan Poin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bk-pemutihan.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 row g-3 text-white">
                    {{-- Multi-Select Live Search Siswa --}}
                    <div class="col-12 position-relative" id="wrapperPemutihanSiswaSearch">
                        <label class="form-label fw-medium text-white">Pilih Siswa <small class="text-white-50">(Bisa pilih beberapa siswa)</small> <span class="text-danger">*</span></label>
                        
                        <div id="hiddenPemutihanSiswaInputsContainer">
                            <input type="hidden" name="siswa_id" value="" required>
                        </div>
                        
                        <div id="selectedPemutihanSiswaChipsContainer" class="d-flex flex-wrap gap-2 mb-2"></div>

                        <div id="pemutihanSiswaSearchBoxContainer">
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="ti tabler-search"></i></span>
                                <input type="text" id="searchPemutihanSiswa" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama / NIS / kelas siswa..." autocomplete="off">
                            </div>
                            <div id="pemutihanSiswaSearchResultsList" class="pemutihan-siswa-search-results d-none"></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Jumlah Pengurangan Poin <span class="text-danger">*</span></label>
                        <input type="number" name="poin_yang_diputihkan" class="form-control bg-dark text-white border-secondary" placeholder="Contoh: 15" min="1" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tanggal Pemutihan <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pemutihan" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Alasan / Program Pemutihan <span class="text-danger">*</span></label>
                        <textarea name="alasan_pemutihan" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Deskripsi kegiatan atau pertimbangan pemutihan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="ti tabler-device-floppy me-1"></i>Eksekusi Pemutihan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $pemutihanSiswaMapData = json_encode($siswas->map(function($s) {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'nis' => $s->nis ?? '',
            'kelas' => $s->kelas?->nama ?? 'Tanpa Kelas'
        ];
    })->values());
@endphp

<script>
    const _allPemutihanSiswas = {!! $pemutihanSiswaMapData !!};
    let _selectedPemutihanSiswaMap = new Map();

    function renderPemutihanSiswaSearchResults(query) {
        const listContainer = document.getElementById('pemutihanSiswaSearchResultsList');
        if (!listContainer) return;

        const q = (query || '').toLowerCase().trim();
        const filtered = _allPemutihanSiswas.filter(s => 
            s.nama.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q) || s.kelas.toLowerCase().includes(q)
        );

        if (filtered.length === 0) {
            listContainer.innerHTML = '<div class="p-3 text-center text-white-50 small">Siswa tidak ditemukan.</div>';
        } else {
            let html = '';
            filtered.forEach(s => {
                const isSelected = _selectedPemutihanSiswaMap.has(s.id);
                const safeNama = s.nama.replace(/'/g, "\\'");
                const safeKelas = s.kelas.replace(/'/g, "\\'");
                
                html += `
                    <div class="pemutihan-siswa-item ${isSelected ? 'selected-item' : ''}" onclick="toggleSelectPemutihanSiswa(${s.id}, '${safeNama}', '${safeKelas}', '${s.nis}')">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center ${isSelected ? 'bg-label-success' : 'bg-label-success'}" style="width: 28px; height: 28px;">
                                <i class="ti ${isSelected ? 'tabler-check text-success' : 'tabler-user text-success'}" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <span class="fw-semibold text-white d-block" style="font-size: 0.85rem;">${s.nama}</span>
                                <small class="text-white-50" style="font-size: 0.72rem;">NIS: ${s.nis || '-'}</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" style="background: rgba(40, 199, 111, 0.18); color: #28c76f; border: 1px solid rgba(40, 199, 111, 0.35); font-size: 0.72rem;">${s.kelas}</span>
                            ${isSelected ? '<span class="badge bg-success" style="font-size: 0.65rem;">Terpilih</span>' : ''}
                        </div>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        }
        listContainer.classList.remove('d-none');
    }

    function toggleSelectPemutihanSiswa(id, nama, kelas, nis) {
        if (_selectedPemutihanSiswaMap.has(id)) {
            _selectedPemutihanSiswaMap.delete(id);
        } else {
            _selectedPemutihanSiswaMap.set(id, { id, nama, kelas, nis });
        }
        updateSelectedPemutihanSiswaUI();
        renderPemutihanSiswaSearchResults(document.getElementById('searchPemutihanSiswa').value);
    }

    function removeSelectedPemutihanSiswa(id) {
        _selectedPemutihanSiswaMap.delete(id);
        updateSelectedPemutihanSiswaUI();
        renderPemutihanSiswaSearchResults(document.getElementById('searchPemutihanSiswa').value);
    }

    function updateSelectedPemutihanSiswaUI() {
        const chipsContainer = document.getElementById('selectedPemutihanSiswaChipsContainer');
        const inputsContainer = document.getElementById('hiddenPemutihanSiswaInputsContainer');
        
        let chipsHtml = '';
        let inputsHtml = '';

        _selectedPemutihanSiswaMap.forEach((s) => {
            inputsHtml += `<input type="hidden" name="siswa_id[]" value="${s.id}">`;
            chipsHtml += `
                <div class="selected-siswa-chip-multi-success">
                    <i class="ti tabler-user text-success"></i>
                    <span class="fw-semibold">${s.nama}</span>
                    <small class="text-white-50">(${s.kelas})</small>
                    <span class="chip-remove-btn" onclick="event.stopPropagation(); removeSelectedPemutihanSiswa(${s.id})" title="Hapus Siswa">
                        <i class="ti tabler-x"></i>
                    </span>
                </div>
            `;
        });

        if (_selectedPemutihanSiswaMap.size === 0) {
            inputsHtml = `<input type="hidden" name="siswa_id" value="" required>`;
        }

        inputsContainer.innerHTML = inputsHtml;
        chipsContainer.innerHTML = chipsHtml;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchPemutihanSiswa');
        if (searchInput) {
            searchInput.addEventListener('focus', function () { renderPemutihanSiswaSearchResults(this.value); });
            searchInput.addEventListener('click', function () { renderPemutihanSiswaSearchResults(this.value); });
            searchInput.addEventListener('input', function () { renderPemutihanSiswaSearchResults(this.value); });
        }
    });

    document.addEventListener('click', function (e) {
        const searchBox = document.getElementById('wrapperPemutihanSiswaSearch');
        if (searchBox && !searchBox.contains(e.target)) {
            const resultsList = document.getElementById('pemutihanSiswaSearchResultsList');
            if (resultsList) resultsList.classList.add('d-none');
        }
    });
</script>
@endsection
