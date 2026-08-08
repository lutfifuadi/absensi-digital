@extends('layouts/layoutMaster')

@section('title', 'Manajemen Kasus BK & Eskalasi Komdis')

@section('page-style')
<link rel="stylesheet" href="{{ asset('css/dashboards/guru-bk.css') }}?v=1.2">
<style>
    .kasus-siswa-search-results {
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

    .kasus-siswa-item {
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: pointer;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      transition: background 0.15s ease;
    }

    .kasus-siswa-item:hover {
      background: rgba(115, 103, 240, 0.18);
    }

    .selected-siswa-chip {
      display: inline-flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      width: 100%;
      background: rgba(115, 103, 240, 0.14);
      border: 1px solid rgba(115, 103, 240, 0.35);
      border-radius: 8px;
      padding: 8px 14px;
      color: #fff;
      font-size: 0.88rem;
    }

    .selected-siswa-chip .chip-remove {
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

    .selected-siswa-chip .chip-remove:hover {
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
                    <i class="ti tabler-briefcase text-warning" style="font-size: 2rem;"></i>
                </div>
                <div class="das-hero__logo-glow"></div>
            </div>

            <div class="das-hero__meta">
                <div class="das-hero__badge">
                    <span class="pulse-dot bg-warning"></span>
                    <a href="javascript:void(0)" class="text-white text-decoration-none">BK & Komdis</a> / Kasus Siswa
                </div>
                <h4 class="das-hero__title text-gradient-gold">Daftar Kasus Bimbingan Konseling</h4>
                <p class="das-hero__subtitle">Pencatatan, penanganan kasus siswa, serta penanganan eskalasi ke Komisi Disiplin (Komdis).</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-warning d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKasus">
                <i class="ti tabler-plus fs-5"></i>
                <span>Tambah Kasus Baru</span>
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
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Total Kasus</span>
                    <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['total'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-folder fs-3 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Proses Penanganan</span>
                    <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['proses'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-progress fs-3 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Eskalasi Komdis</span>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['eskalasi'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-danger rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-gavel fs-3 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-body">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50 d-block mb-1 fs-6 fw-medium">Selesai / Closed</span>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['selesai'] ?? 0) }}</h4>
                </div>
                <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="ti tabler-circle-check fs-3 text-success"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Card --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.bk-kasus.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label fw-medium">Cari Siswa / Kasus</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="ti tabler-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Nama, NIS, atau judul kasus..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-medium">Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="">Semua Kategori</option>
                    <option value="disiplin" {{ request('kategori') === 'disiplin' ? 'selected' : '' }}>Kedisiplinan</option>
                    <option value="akademik" {{ request('kategori') === 'akademik' ? 'selected' : '' }}>Akademik</option>
                    <option value="sosial" {{ request('kategori') === 'sosial' ? 'selected' : '' }}>Sosial Emosional</option>
                    <option value="pribadi" {{ request('kategori') === 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label class="form-label fw-medium">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="terbuka" {{ request('status') === 'terbuka' ? 'selected' : '' }}>Terbuka (Open)</option>
                    <option value="dalam_proses" {{ request('status') === 'dalam_proses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="eskalasi_komdis" {{ request('status') === 'eskalasi_komdis' ? 'selected' : '' }}>Eskalasi Komdis</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
</div>

{{-- Table Kasus BK --}}
<div class="card border-0 shadow-sm">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Reg</th>
                    <th>Siswa</th>
                    <th>Judul Kasus & Kategori</th>
                    <th>Tanggal Pelaporan</th>
                    <th>Tingkat Keparahan</th>
                    <th>Status Kasus</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kasusList as $item)
                    <tr>
                        <td><span class="fw-bold text-primary">#BK-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2 d-flex align-items-center justify-content-center">
                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                        {{ strtoupper(substr($item->siswa?->nama_lengkap ?? 'S', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-truncate text-white">{{ $item->siswa?->nama_lengkap ?? '—' }}</h6>
                                    <small class="text-white-50">{{ $item->siswa?->kelas?->nama ?? '—' }} • NIS: {{ $item->siswa?->nis ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-white d-block">{{ $item->judul_kasus }}</span>
                            <small class="badge bg-label-warning">{{ ucfirst($item->kategori) }}</small>
                        </td>
                        <td>{{ $item->tanggal_lapor ? $item->tanggal_lapor->format('d M Y') : '—' }}</td>
                        <td>
                            @php
                                $keparahanBadge = match($item->tingkat_keparahan) {
                                    'tinggi' => 'danger',
                                    'sedang' => 'warning',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge bg-{{ $keparahanBadge }}">{{ ucfirst($item->tingkat_keparahan) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $item->status === 'eskalasi_komdis' ? 'danger' : ($item->status === 'selesai' ? 'success' : 'info') }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
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
                                <i class="ti tabler-folder-off fs-1 text-white-50 mb-2"></i>
                                <h6 class="text-white mb-1">Belum Ada Kasus Terdaftar</h6>
                                <p class="text-white-50 small mb-0">Belum terdapat data kasus BK yang tercatat di sistem.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($kasusList->hasPages())
        <div class="card-footer py-3 border-top border-secondary">
            {{ $kasusList->links() }}
        </div>
    @endif
</div>

{{-- MODAL TAMBAH KASUS --}}
<div class="modal fade" id="modalTambahKasus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-bottom p-4">
                <h5 class="modal-title fw-bold text-white"><i class="ti tabler-plus text-warning me-2"></i>Tambah Kasus BK Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.bk-kasus.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4 row g-3 text-white">
                    {{-- Live Search Siswa --}}
                    <div class="col-12 col-md-6 position-relative" id="wrapperKasusSiswaSearch">
                        <label class="form-label fw-medium text-white">Pilih Siswa <span class="text-danger">*</span></label>
                        <input type="hidden" name="siswa_id" id="inputKasusSiswaId" required>
                        
                        <div id="selectedSiswaChipWrap"></div>

                        <div id="siswaSearchBoxContainer">
                            <div class="input-group">
                                <span class="input-group-text bg-dark border-secondary text-white-50"><i class="ti tabler-search"></i></span>
                                <input type="text" id="searchKasusSiswa" class="form-control bg-dark text-white border-secondary" placeholder="Cari nama / NIS / kelas siswa..." autocomplete="off">
                            </div>
                            <div id="siswaSearchResultsList" class="kasus-siswa-search-results d-none"></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Kategori Kasus <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select bg-dark text-white border-secondary" required>
                            <option value="disiplin">Kedisiplinan</option>
                            <option value="akademik">Akademik</option>
                            <option value="sosial">Sosial Emosional</option>
                            <option value="pribadi">Pribadi</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Judul Kasus <span class="text-danger">*</span></label>
                        <input type="text" name="judul_kasus" class="form-control bg-dark text-white border-secondary" placeholder="Ringkasan singkat masalah..." required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tingkat Keparahan <span class="text-danger">*</span></label>
                        <select name="tingkat_keparahan" class="form-select bg-dark text-white border-secondary" required>
                            <option value="rendah">Rendah (Low)</option>
                            <option value="sedang">Sedang (Medium)</option>
                            <option value="tinggi">Tinggi (High)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-medium text-white">Tanggal Kejadian <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_lapor" class="form-control bg-dark text-white border-secondary" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-white">Deskripsi & Kronologi Kasus</label>
                        <textarea name="deskripsi" class="form-control bg-dark text-white border-secondary" rows="3" placeholder="Detail kronologi atau laporan awal kasus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top p-3">
                    <button type="button" class="btn btn-outline-secondary text-white" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="ti tabler-device-floppy me-1"></i>Simpan Kasus</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $siswaMapData = json_encode($siswas->map(function($s) {
        return [
            'id' => $s->id,
            'nama' => $s->nama_lengkap,
            'nis' => $s->nis ?? '',
            'kelas' => $s->kelas?->nama ?? 'Tanpa Kelas'
        ];
    })->values());
@endphp

<script>
    const _allSiswas = {!! $siswaMapData !!};

    function renderSiswaSearchResults(query) {
        const listContainer = document.getElementById('siswaSearchResultsList');
        if (!listContainer) return;

        const q = (query || '').toLowerCase().trim();
        const filtered = _allSiswas.filter(s => 
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
                    <div class="kasus-siswa-item" onclick="selectKasusSiswa(${s.id}, '${safeNama}', '${safeKelas}', '${s.nis}')">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar avatar-xs rounded-circle d-flex align-items-center justify-content-center bg-label-warning" style="width: 28px; height: 28px;">
                                <i class="ti tabler-user text-warning" style="font-size: 0.85rem;"></i>
                            </div>
                            <div>
                                <span class="fw-semibold text-white d-block" style="font-size: 0.85rem;">${s.nama}</span>
                                <small class="text-white-50" style="font-size: 0.72rem;">NIS: ${s.nis || '-'}</small>
                            </div>
                        </div>
                        <span class="badge" style="background: rgba(115, 103, 240, 0.18); color: #a5a2f7; border: 1px solid rgba(115, 103, 240, 0.35); font-size: 0.72rem;">${s.kelas}</span>
                    </div>
                `;
            });
            listContainer.innerHTML = html;
        }
        listContainer.classList.remove('d-none');
    }

    function selectKasusSiswa(id, nama, kelas, nis) {
        document.getElementById('inputKasusSiswaId').value = id;
        document.getElementById('siswaSearchResultsList').classList.add('d-none');
        document.getElementById('siswaSearchBoxContainer').classList.add('d-none');

        const chipWrap = document.getElementById('selectedSiswaChipWrap');
        chipWrap.innerHTML = `
            <div class="selected-siswa-chip">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti tabler-user-check text-warning fs-5"></i>
                    <div>
                        <span class="fw-bold">${nama}</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">${kelas} • NIS: ${nis || '-'}</small>
                    </div>
                </div>
                <span class="chip-remove" onclick="clearSelectedKasusSiswa()" title="Ubah Siswa">
                    <i class="ti tabler-x"></i> Ubah
                </span>
            </div>
        `;
    }

    function clearSelectedKasusSiswa() {
        document.getElementById('inputKasusSiswaId').value = "";
        document.getElementById('selectedSiswaChipWrap').innerHTML = "";
        document.getElementById('siswaSearchBoxContainer').classList.remove('d-none');
        document.getElementById('searchKasusSiswa').value = "";
        renderSiswaSearchResults('');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchKasusSiswa');
        if (searchInput) {
            searchInput.addEventListener('focus', function () { renderSiswaSearchResults(this.value); });
            searchInput.addEventListener('click', function () { renderSiswaSearchResults(this.value); });
            searchInput.addEventListener('input', function () { renderSiswaSearchResults(this.value); });
        }
    });

    document.addEventListener('click', function (e) {
        const searchBox = document.getElementById('wrapperKasusSiswaSearch');
        if (searchBox && !searchBox.contains(e.target)) {
            const resultsList = document.getElementById('siswaSearchResultsList');
            if (resultsList) resultsList.classList.add('d-none');
        }
    });
</script>
@endsection
