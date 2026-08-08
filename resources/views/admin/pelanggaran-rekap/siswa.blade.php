@extends('layouts/layoutMaster')

@section('title', 'Profil Pelanggaran Siswa — ' . $siswa->nama_lengkap)

@section('page-style')
<style>
  .das-panel__head-tabs {
    padding: 0 0.75rem;
    border-bottom: 1px solid var(--das-border, rgba(255, 255, 255, 0.08));
    background: rgba(255, 255, 255, 0.02);
  }
  .nav-tabs-glass {
    border-bottom: none !important;
    margin-bottom: -1px;
  }
  .nav-tabs-glass .nav-link {
    color: rgba(255, 255, 255, 0.6) !important;
    border: none !important;
    border-bottom: 2px solid transparent !important;
    background: transparent !important;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 0.9rem 1.15rem;
    transition: all 0.15s ease;
  }
  .nav-tabs-glass .nav-link:hover {
    color: #fff !important;
  }
  .nav-tabs-glass .nav-link.active {
    color: var(--das-primary, #7367f0) !important;
    border-bottom-color: var(--das-primary, #7367f0) !important;
    background: transparent !important;
  }
</style>
@endsection

@section('content')
<div class="das-hero das-hero--with-stats" style="margin-bottom: 4.5rem;">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        @if($siswa->foto && file_exists(public_path('storage/foto-siswa/' . $siswa->foto)))
          <img src="{{ asset('storage/foto-siswa/' . $siswa->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width:100%; height:100%;">
        @else
          <span class="avatar-initial rounded-circle bg-label-{{ $siswa->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size: 2rem; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
            {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($siswa->nama_lengkap, ' ') ?: $siswa->nama_lengkap, 1, 1)) }}
          </span>
        @endif
        <div class="das-hero__logo-glow"></div>
      </div>
      <div class="das-hero__meta">
        <div class="das-hero__badge">
          <span class="pulse-dot"></span>
          Student ID: {{ $siswa->nis }}
        </div>
        <h4 class="das-hero__title text-gradient-gold">{{ $siswa->nama_lengkap }}</h4>
        <p class="das-hero__subtitle">{{ $siswa->kelas->nama ?? 'Tanpa Kelas' }} • TA {{ $siswa->tahunAkademik->nama ?? '-' }}</p>
      </div>
    </div>

    <div class="das-hero__actions d-flex gap-2">
      <a href="{{ route('admin.pelanggaran-siswa.rekap') }}" class="das-btn das-btn--secondary">
        <i class="ti tabler-arrow-left"></i> Kembali ke Rekap
      </a>
    </div>
  </div>

  <div class="das-stats-row">
    <div class="das-stat-card das-stat-card--danger">
      <div class="das-stat-card__icon"><i class="ti tabler-alert-triangle"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['total_poin'] }}</div>
        <div class="das-stat-card__label">Total Poin</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--warning">
      <div class="das-stat-card__icon"><i class="ti tabler-award"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['level_sp_aktif'] ?: '-' }}</div>
        <div class="das-stat-card__label">SP Aktif</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--info">
      <div class="das-stat-card__icon"><i class="ti tabler-list-details"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['jumlah_pelanggaran'] }}</div>
        <div class="das-stat-card__label">Jumlah Pelanggaran</div>
      </div>
    </div>
    <div class="das-stat-card das-stat-card--primary">
      <div class="das-stat-card__icon"><i class="ti tabler-mail-opened"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['jumlah_sp'] }}</div>
        <div class="das-stat-card__label">Jumlah SP</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-4 col-lg-5">
    <div class="das-panel h-100">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Informasi Personal Siswa
        </div>
      </div>
      <div class="das-panel__body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-white-50 small d-flex align-items-center gap-2">
              <i class="ti tabler-id text-primary fs-5"></i> NISN
            </span>
            <span class="text-white fw-bold font-monospace">{{ $siswa->nisn ?: '-' }}</span>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-white-50 small d-flex align-items-center gap-2">
              <i class="ti tabler-gender-male-female text-primary fs-5"></i> Jenis Kelamin
            </span>
            <span class="text-white fw-semibold">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-white-50 small d-flex align-items-center gap-2">
              <i class="ti tabler-chalkboard-teacher text-primary fs-5"></i> Wali Kelas
            </span>
            <span class="text-white fw-semibold text-end">{{ $siswa->kelas->waliKelas->nama_lengkap ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-white-50 small d-flex align-items-center gap-2">
              <i class="ti tabler-phone text-primary fs-5"></i> Kontak Ortu
            </span>
            <span class="text-white fw-semibold font-monospace">{{ $siswa->no_hp_ortu ?: '-' }}</span>
          </li>
          <li class="d-flex justify-content-between align-items-center">
            <span class="text-white-50 small d-flex align-items-center gap-2">
              <i class="ti tabler-circle-check text-primary fs-5"></i> Status
            </span>
            <span class="das-chip --success">Aktif</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-8 col-lg-7">
    <div class="das-panel h-100">
      <div class="das-panel__head-tabs">
        <ul class="nav nav-tabs nav-tabs-glass" role="tablist">
          <li class="nav-item">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pelanggaran">
              <i class="ti tabler-alert-circle me-1"></i> Riwayat Pelanggaran
            </button>
          </li>
          <li class="nav-item">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-sp">
              <i class="ti tabler-mail me-1"></i> Riwayat Surat Peringatan (SP)
            </button>
          </li>
        </ul>
      </div>
      <div class="das-panel__body p-0">
        <div class="tab-content bg-transparent p-0 border-0">
          <div class="tab-pane fade show active" id="tab-pelanggaran" role="tabpanel">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr>
                    <th>TANGGAL</th>
                    <th>KATEGORI / JENIS</th>
                    <th class="text-center">POIN</th>
                    <th>DICATAT OLEH</th>
                    <th>KETERANGAN</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pelanggaranSiswa as $p)
                     <tr>
                        <td class="text-white-50 small text-nowrap">{{ \Carbon\Carbon::parse($p->tanggal_kejadian)->translatedFormat('d M Y') }}</td>
                        <td>
                          <div class="fw-bold text-white">{{ optional($p->jenisPelanggaran)->kategori->nama ?? '-' }}</div>
                          <div class="small text-white-50">{{ optional($p->jenisPelanggaran)->nama ?? '-' }}</div>
                        </td>
                        <td class="text-center">
                          <span class="das-chip --danger">+{{ $p->poin_saat_itu }} Poin</span>
                        </td>
                        <td class="small text-white-50 text-nowrap">{{ optional($p->pencatat)->name ?? '-' }}</td>
                        <td class="small text-white-50">{{ $p->keterangan ?? '-' }}</td>
                     </tr>
                  @empty
                     <tr>
                       <td colspan="5" class="text-center py-5">
                         <div class="d-flex flex-column align-items-center justify-content-center opacity-50 py-2">
                           <i class="ti tabler-circle-check text-success fs-1 mb-2"></i>
                           <div class="fw-semibold text-white">Belum Ada Riwayat Pelanggaran</div>
                           <div class="small text-white-50">Siswa ini belum pernah tercatat melakukan pelanggaran.</div>
                         </div>
                       </td>
                     </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          
          <div class="tab-pane fade" id="tab-sp" role="tabpanel">
            <div class="table-responsive">
              <table class="das-table">
                <thead>
                  <tr>
                    <th>TANGGAL</th>
                    <th>LEVEL SP</th>
                    <th class="text-center">POIN SAAT SP</th>
                    <th>DITERBITKAN OLEH</th>
                    <th>CATATAN</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pelanggaranSp as $sp)
                     <tr>
                        <td class="text-white-50 small text-nowrap">{{ \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') }}</td>
                        <td>
                          @php
                            $spColor = match ($sp->level_sp) {
                                'SP1' => 'warning',
                                'SP2' => 'danger',
                                'SP3' => 'dark',
                                default => 'info',
                            };
                          @endphp
                          <span class="das-chip --{{ $spColor }}">{{ $sp->level_sp }}</span>
                        </td>
                        <td class="text-center">
                          <span class="das-chip --danger">{{ $sp->total_poin_saat_sp }} Poin</span>
                        </td>
                        <td class="small text-white-50 text-nowrap">{{ optional($sp->penerbit)->name ?? '-' }}</td>
                        <td class="small text-white-50">{{ $sp->catatan_tambahan ?? '-' }}</td>
                     </tr>
                  @empty
                     <tr>
                       <td colspan="5" class="text-center py-5">
                         <div class="d-flex flex-column align-items-center justify-content-center opacity-50 py-2">
                           <i class="ti tabler-shield-check text-info fs-1 mb-2"></i>
                           <div class="fw-semibold text-white">Belum Ada Surat Peringatan</div>
                           <div class="small text-white-50">Siswa ini belum memiliki akumulasi poin yang memicu penerbitan SP.</div>
                         </div>
                       </td>
                     </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
