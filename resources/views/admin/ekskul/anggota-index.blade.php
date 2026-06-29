@extends('layouts/layoutMaster')

@section('title', 'Anggota Ekskul')

@php
  $ekskul = \App\Models\Ekskul::withCount('anggota')->findOrFail($ekskulId);
@endphp

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
    --das-radius: 5px;
    --das-purple: #a855f7;
    --das-purple-soft: rgba(168, 85, 247, 0.12);
  }

  .das-btn { display: inline-flex; align-items: center; gap: 5px; font-size: .75rem; font-weight: 600; padding: .5rem 1rem; border-radius: 5px; border: 1px solid transparent; cursor: pointer; transition: all .18s ease; text-decoration: none; white-space: nowrap; }
  .das-btn--primary { background: var(--das-primary); color: white !important; border-color: var(--das-primary); }
  .das-btn--primary:hover { background: #6259e8; transform: translateY(-2px); }
  .das-btn--ghost { background: transparent; border-color: var(--das-border); color: #999 !important; }
  .das-btn--ghost:hover { background: var(--das-surface-hover); color: white !important; }
  .das-btn--danger { background: transparent; border-color: var(--das-danger); color: var(--das-danger) !important; }
  .das-btn--danger:hover { background: var(--das-danger-soft); }

  .das-panel { background: var(--das-surface); border: 1px solid var(--das-border); border-radius: var(--das-radius); overflow: hidden; backdrop-filter: blur(6px); }
  .das-panel__head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.25rem; border-bottom: 1px solid var(--das-border); }
  .das-panel__title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; display: flex; align-items: center; gap: 8px; color: #ccc; }
  .das-panel__icon-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--das-info); box-shadow: 0 0 6px var(--das-info); }

  .das-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
  .das-table thead th { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #666; padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); background: rgba(255,255,255,.02); }
  .das-table tbody td { padding: .75rem 1rem; border-bottom: 1px solid var(--das-border); color: #ccc; vertical-align: middle; transition: background .2s ease; }
  .das-table tbody tr:hover td { background: var(--das-surface-hover); }

  .das-chip { display: inline-flex; align-items: center; font-size: .65rem; font-weight: 700; padding: 2px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: .5px; }
  .das-chip--info { background: var(--das-info-soft); color: var(--das-info); }
  .das-chip--primary { background: var(--das-primary-soft); color: var(--das-primary); }
  .das-chip--success { background: var(--das-success-soft); color: var(--das-success); }
  .das-chip--warning { background: var(--das-warning-soft); color: var(--das-warning); }
  .das-chip--danger { background: var(--das-danger-soft); color: var(--das-danger); }
  .das-chip--purple { background: var(--das-purple-soft); color: var(--das-purple); }

  .icon-btn { width: 30px; height: 30px; border-radius: 5px; border: 1px solid var(--das-border); background: transparent; color: #888; display: inline-flex; align-items: center; justify-content: center; transition: all .2s; text-decoration: none; cursor: pointer; }
  .icon-btn:hover { background: var(--das-surface-hover); color: white; transform: translateY(-2px); }
  .icon-btn--warning:hover { color: var(--das-warning); border-color: var(--das-warning); }
  .icon-btn--danger:hover { color: var(--das-danger); border-color: var(--das-danger); }

  .das-form-control { background: rgba(255,255,255,.04) !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; color: #e0e0e0 !important; font-size: .85rem !important; transition: border-color .2s, background .2s; }
  .das-form-control:focus { background: rgba(255,255,255,.07) !important; border-color: rgba(115,103,240,.5) !important; outline: none !important; box-shadow: none !important; color: white !important; }
  .das-form-control option { background: #1a1a2e; color: #ccc; }
  .das-form-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; color: #888; margin-bottom: .5rem; display: block; }

  .das-modal { background: #1a1a2e !important; border: 1px solid var(--das-border) !important; border-radius: var(--das-radius) !important; overflow: hidden; }
  .das-modal-head { border-bottom: 1px solid var(--das-border); background: rgba(115,103,240,.05); padding: 1.25rem; }
  .das-modal-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
  .das-modal-body { padding: 1.5rem; }

  @keyframes slideInUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
  .slide-in-up { animation: slideInUp .5s ease-out; }

  .kuota-bar { height: 6px; border-radius: 10px; background: rgba(255,255,255,.08); overflow: hidden; }
  .kuota-bar__fill { height: 100%; border-radius: 10px; background: var(--das-info); transition: width .5s ease; }
  .kuota-bar__fill--warning { background: var(--das-warning); }
  .kuota-bar__fill--danger { background: var(--das-danger); }
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
                style="width:52px;height:52px;border-radius:12px !important;background:rgba(115,103,240,0.2);border:1px solid rgba(115,103,240,0.4);">
                <i class="ti tabler-users text-primary fs-3"></i>
              </div>
              <div>
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.6;">
                    <li class="breadcrumb-item"><a href="{{ route('admin.ekskul.index') }}" class="text-white text-decoration-none">Ekstrakurikuler</a></li>
                    <li class="breadcrumb-item active text-white">Anggota</li>
                  </ol>
                </nav>
                <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">
                  {{ $ekskul->nama }}
                </h4>
              </div>
            </div>
            <div>
              <a href="{{ route('admin.ekskul.index') }}" class="das-btn das-btn--ghost me-2">
                <i class="ti tabler-arrow-left"></i> Kembali
              </a>
              <a href="{{ route('admin.ekskul.absensi.index', $ekskul->id) }}" class="das-btn das-btn--primary">
                <i class="ti tabler-clipboard-check me-1"></i> Absensi
              </a>
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

  {{-- ═══════════ INFO KUOTA ═══════════ --}}
  <div class="das-panel mb-4 slide-in-up">
    <div class="p-3">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="d-flex align-items-center gap-3">
            <i class="ti tabler-users text-info" style="font-size:1.5rem;"></i>
            <div>
              <div class="text-white-50 small">Total Anggota</div>
              <strong class="text-white fs-5">{{ $anggota->count() }}</strong>
              @if($ekskul->kuota)
                <span class="text-white-50">/ {{ $ekskul->kuota }}</span>
              @endif
            </div>
          </div>
        </div>
        <div class="col-md-6 mt-2 mt-md-0">
          @if($ekskul->kuota)
            @php
              $persentase = min(($anggota->count() / $ekskul->kuota) * 100, 100);
              $barClass = $persentase >= 90 ? 'kuota-bar__fill--danger' : ($persentase >= 70 ? 'kuota-bar__fill--warning' : '');
            @endphp
            <div class="d-flex justify-content-between small text-white-50 mb-1">
              <span>Kuota Terisi</span>
              <span>{{ round($persentase) }}%</span>
            </div>
            <div class="kuota-bar">
              <div class="kuota-bar__fill {{ $barClass }}" style="width: {{ $persentase }}%;"></div>
            </div>
          @else
            <div class="text-white-50 small text-md-end">Kuota tidak terbatas</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ═══════════ DAFTAR ANGGOTA ═══════════ --}}
  <div class="das-panel slide-in-up">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <span class="das-panel__icon-dot"></span>
        Daftar Anggota
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="das-chip das-chip--info">{{ $anggota->count() }} Anggota</span>
        <button type="button" class="das-btn das-btn--primary" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa"
          {{ ($ekskul->kuota && $anggota->count() >= $ekskul->kuota) ? 'disabled' : '' }}
          title="{{ ($ekskul->kuota && $anggota->count() >= $ekskul->kuota) ? 'Kuota penuh' : 'Tambah siswa' }}">
          <i class="ti tabler-user-plus me-1"></i> Tambah Siswa
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="das-table">
        <thead>
          <tr>
            <th width="40">#</th>
            <th>Nama Siswa</th>
            <th class="d-none d-md-table-cell">Kelas</th>
            <th>Status</th>
            <th class="d-none d-lg-table-cell">Tanggal Masuk</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($anggota as $a)
            @php
              $statusChip = [
                'aktif'  => 'das-chip--success',
                'cuti'   => 'das-chip--warning',
                'keluar' => 'das-chip--danger',
              ][$a->status] ?? 'das-chip--info';
              $statusLabel = [
                'aktif'  => 'Aktif',
                'cuti'   => 'Cuti',
                'keluar' => 'Keluar',
              ][$a->status] ?? ucfirst($a->status);
            @endphp
            <tr>
              <td class="text-muted small text-center">{{ $loop->iteration }}</td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div style="width:32px;height:32px;border-radius:50%;background:var(--das-primary-soft);border:1px solid rgba(115,103,240,.2);display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--das-primary);flex-shrink:0;">
                    {{ strtoupper(substr($a->siswa->nama_lengkap ?? '-', 0, 1)) }}
                  </div>
                  <div>
                    <div class="fw-medium text-white" style="font-size:.82rem;">{{ $a->siswa->nama_lengkap ?? '-' }}</div>
                    <div class="text-white-50 small" style="font-size:.65rem;">{{ $a->siswa->nis ?? '-' }}</div>
                  </div>
                </div>
              </td>
              <td class="d-none d-md-table-cell text-white-50 small">
                {{ $a->siswa->kelas->nama ?? '-' }}
              </td>
              <td>
                <span class="das-chip {{ $statusChip }}">{{ $statusLabel }}</span>
              </td>
              <td class="d-none d-lg-table-cell text-white-50 small">
                {{ $a->tanggal_masuk?->format('d M Y') ?? '-' }}
              </td>
              <td class="text-end pe-3">
                <div class="d-flex justify-content-end gap-1">
                  {{-- Dropdown Ubah Status --}}
                  <div class="dropdown">
                    <button class="icon-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"
                      title="Ubah Status" style="font-size:.7rem;">
                      <i class="ti tabler-status-change"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end das-modal border-0 shadow-lg" style="min-width:150px;">
                      @foreach(['aktif'=>'Aktif','cuti'=>'Cuti','keluar'=>'Keluar'] as $st => $label)
                        <li>
                          <form action="{{ route('admin.ekskul.anggota.update-status', [$ekskul->id, $a->id]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="{{ $st }}">
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-white-50 py-2 px-3"
                              style="font-size:.78rem;"
                              {{ $a->status == $st ? 'disabled' : '' }}>
                              @if($a->status == $st)
                                <i class="ti tabler-check text-success"></i>
                              @else
                                <i class="ti tabler-arrow-right"></i>
                              @endif
                              {{ $label }}
                            </button>
                          </form>
                        </li>
                      @endforeach
                    </ul>
                  </div>

                  {{-- Hapus --}}
                  <form action="{{ route('admin.ekskul.anggota.destroy', [$ekskul->id, $a->id]) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Yakin ingin mengeluarkan siswa ini dari ekskul?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn icon-btn--danger" title="Hapus Anggota">
                      <i class="ti tabler-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-5 text-center">
                <div class="d-flex flex-column align-items-center gap-2 opacity-40">
                  <i class="ti tabler-users-minus" style="font-size:3rem;"></i>
                  <h6 class="text-white mb-1">Belum Ada Anggota</h6>
                  <p class="text-white-50 small mb-3">Tambahkan siswa untuk bergabung di ekskul ini.</p>
                  <button type="button" class="das-btn das-btn--primary" data-bs-toggle="modal" data-bs-target="#modalTambahSiswa">
                    <i class="ti tabler-user-plus me-1"></i> Tambah Siswa
                  </button>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ═══════════ MODAL TAMBAH SISWA ═══════════ --}}
  <div class="modal fade" id="modalTambahSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content das-modal shadow-lg">
        <div class="das-modal-head d-flex align-items-center justify-content-between">
          <h5 class="das-modal-title">
            <i class="ti tabler-user-plus me-2 text-primary"></i>Tambah Siswa ke {{ $ekskul->nama }}
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.ekskul.anggota.store', $ekskul->id) }}" method="POST">
          @csrf
          <div class="das-modal-body">
            <div x-data="siswaSearch()">
              <div class="mb-3">
                <label class="das-form-label">
                  <i class="ti tabler-search me-1 text-info"></i> Cari Siswa <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control das-form-control" placeholder="Ketik nama atau NIS siswa..."
                  x-model="search" @input.debounce.300ms="filterSiswa">
              </div>

              <div class="mb-3">
                <label class="das-form-label">
                  <i class="ti tabler-door me-1 text-info"></i> Filter Kelas
                </label>
                <select class="form-select das-form-control" x-model="filterKelas" @change="filterSiswa">
                  <option value="">Semua Kelas</option>
                  @foreach($siswaOptions->pluck('kelas.nama', 'kelas_id')->unique()->filter()->sort() as $kelasId => $kelasNama)
                    @if($kelasNama)
                      <option value="{{ $kelasId }}">{{ $kelasNama }}</option>
                    @endif
                  @endforeach
                </select>
              </div>

              @if($ekskul->kuota)
                <div class="alert alert-info mb-0 border-0" style="background:rgba(0,207,232,.08);border-radius:8px;">
                  <div class="d-flex align-items-center gap-2 small">
                    <i class="ti tabler-info-circle"></i>
                    <span>Kuota tersisa: <strong>{{ max(0, $ekskul->kuota - $anggota->count()) }}</strong> dari {{ $ekskul->kuota }} anggota</span>
                  </div>
                </div>
              @endif

              <div class="mt-3" style="max-height:280px;overflow-y:auto;">
                <p class="text-white-50 small mb-2" x-show="filteredSiswa.length === 0 && search.length > 0">
                  <i class="ti tabler-search-off me-1"></i> Tidak ada siswa ditemukan.
                </p>
                <template x-for="siswa in filteredSiswa" :key="siswa.id">
                  <label class="d-flex align-items-center gap-3 p-2 rounded"
                    style="cursor:pointer;transition:background .15s;"
                    :style="selectedId == siswa.id ? 'background:rgba(115,103,240,.15);border:1px solid rgba(115,103,240,.3);' : 'border:1px solid transparent;'"
                    @mouseenter="$el.style.background='rgba(255,255,255,.04)'"
                    @mouseleave="$el.style.background=selectedId == siswa.id ? 'rgba(115,103,240,.15)' : 'transparent'">
                    <input type="radio" name="siswa_id" :value="siswa.id" x-model="selectedId" style="accent-color:var(--das-primary);" required>
                    <div style="width:34px;height:34px;border-radius:50%;background:var(--das-primary-soft);border:1px solid rgba(115,103,240,.2);display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--das-primary);flex-shrink:0;">
                      <span x-text="siswa.nama_lengkap ? siswa.nama_lengkap.charAt(0).toUpperCase() : '?'"></span>
                    </div>
                    <div class="flex-grow-1" style="min-width:0;">
                      <div class="text-white small fw-medium" x-text="siswa.nama_lengkap || '-'" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></div>
                      <div class="text-white-50 small" style="font-size:.65rem;">
                        <span x-text="siswa.nis || '-'"></span>
                        <span class="mx-1">|</span>
                        <span x-text="siswa.kelas?.nama || '-'"></span>
                      </div>
                    </div>
                  </label>
                </template>
                <template x-if="filteredSiswa.length === 0 && search.length === 0">
                  <p class="text-white-50 small text-center py-4">
                    <i class="ti tabler-search me-1"></i> Ketik untuk mencari siswa.
                  </p>
                </template>
              </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="das-btn das-btn--primary px-4" id="btnTambahSiswa">
              <i class="ti tabler-user-plus me-1"></i> Tambahkan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
  });

  function siswaSearch() {
    return {
      search: '',
      filterKelas: '',
      selectedId: null,
      allSiswa: {!! json_encode($siswaOptions->map(function($s) { return ['id' => $s->id, 'nama_lengkap' => $s->nama_lengkap, 'nis' => $s->nis, 'kelas_id' => $s->kelas_id, 'kelas' => $s->kelas ? ['nama' => $s->kelas->nama] : null]; })) !!},
      get filteredSiswa() {
        return this.allSiswa.filter(s => {
          const matchSearch = !this.search || 
            (s.nama_lengkap && s.nama_lengkap.toLowerCase().includes(this.search.toLowerCase())) ||
            (s.nis && s.nis.toLowerCase().includes(this.search.toLowerCase()));
          const matchKelas = !this.filterKelas || s.kelas_id == this.filterKelas;
          return matchSearch && matchKelas;
        });
      },
      filterSiswa() {
        this.selectedId = null;
      }
    }
  }
</script>
@endsection
