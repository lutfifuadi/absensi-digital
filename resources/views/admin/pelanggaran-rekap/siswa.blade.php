@extends('layouts/layoutMaster')

@section('title', 'Profil Pelanggaran Siswa — ' . $siswa->nama_lengkap)

@section('content')
<div class="das-hero das-hero--with-stats mb-4">
  <div class="das-hero__bg"></div>
  <div class="das-hero__glass"></div>
  <div class="das-hero__grid-lines"></div>

  <div class="das-hero__inner">
    <div class="das-hero__identity">
      <div class="das-hero__logo-wrapper">
        <span class="avatar-initial rounded-circle bg-label-{{ $siswa->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size: 2.5rem; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
          {{ strtoupper(substr($siswa->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($siswa->nama_lengkap, ' ') ?: $siswa->nama_lengkap, 1, 1)) }}
        </span>
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
        <div class="das-stat-card__val">{{ $stats['level_sp_aktif'] }}</div>
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
    <div class="das-stat-card das-stat-card--dark">
      <div class="das-stat-card__icon"><i class="ti tabler-mail-opened"></i></div>
      <div class="das-stat-card__body">
        <div class="das-stat-card__val">{{ $stats['jumlah_sp'] }}</div>
        <div class="das-stat-card__label">Jumlah SP</div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-xl-4 col-lg-5">
    <div class="das-panel mb-4">
      <div class="das-panel__head">
        <div class="das-panel__title">
          <span class="das-panel__icon-dot --primary"></span>
          Informasi Personal Siswa
        </div>
      </div>
      <div class="das-panel__body">
        <ul class="list-unstyled mb-0">
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">NISN</span>
            <span class="text-white fw-bold">{{ $siswa->nisn }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Jenis Kelamin</span>
            <span class="text-white fw-bold">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Wali Kelas</span>
            <span class="text-white fw-bold">{{ $siswa->kelas->waliKelas->nama_lengkap ?? '-' }}</span>
          </li>
          <li class="d-flex justify-content-between mb-3 pb-2 border-bottom border-secondary border-opacity-10">
            <span class="text-muted small">Kontak Ortu</span>
            <span class="text-white fw-bold">{{ $siswa->no_hp_ortu }}</span>
          </li>
          <li class="d-flex justify-content-between">
            <span class="text-muted small">Status</span>
            <span class="das-chip --success">Aktif</span>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div class="col-xl-8 col-lg-7">
    <div class="das-panel mb-4">
       <div class="nav-align-top">
          <ul class="nav nav-tabs das-panel__head border-0" role="tablist">
            <li class="nav-item">
              <button type="button" class="nav-link active py-3 bg-transparent border-0 text-white" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pelanggaran">
                <i class="ti tabler-alert-circle me-1"></i> Riwayat Pelanggaran
              </button>
            </li>
            <li class="nav-item">
              <button type="button" class="nav-link py-3 bg-transparent border-0 text-white" role="tab" data-bs-toggle="tab" data-bs-target="#tab-sp">
                <i class="ti tabler-mail me-1"></i> Riwayat Surat Peringatan (SP)
              </button>
            </li>
          </ul>
          <div class="tab-content bg-transparent p-0 border-0">
            <div class="tab-pane fade show active" id="tab-pelanggaran" role="tabpanel">
              <div class="table-responsive">
                <table class="das-table">
                  <thead>
                    <tr>
                      <th>TANGGAL</th>
                      <th>KATEGORI / JENIS</th>
                      <th>POIN</th>
                      <th>DICATAT OLEH</th>
                      <th>KETERANGAN</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($pelanggaranSiswa as $p)
                       <tr>
                          <td>{{ \Carbon\Carbon::parse($p->tanggal_kejadian)->translatedFormat('d M Y') }}</td>
                          <td>
                            <div class="fw-bold">{{ optional($p->jenisPelanggaran)->kategori->nama ?? '-' }}</div>
                            <div class="small text-muted">{{ optional($p->jenisPelanggaran)->nama ?? '-' }}</div>
                          </td>
                          <td class="fw-bold text-danger">{{ $p->poin_saat_itu }}</td>
                          <td class="small">{{ optional($p->pencatat)->name ?? '-' }}</td>
                          <td class="small text-white-50">{{ $p->keterangan ?? '-' }}</td>
                       </tr>
                    @empty
                       <tr>
                         <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat pelanggaran.</td>
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
                      <th>POIN SAAT SP</th>
                      <th>DITERBITKAN OLEH</th>
                      <th>CATATAN</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($pelanggaranSp as $sp)
                       <tr>
                          <td>{{ \Carbon\Carbon::parse($sp->tanggal_sp)->translatedFormat('d M Y') }}</td>
                          <td>
                            @php
                              $spColor = match ($sp->level_sp) {
                                  'SP1' => 'warning',
                                  'SP2' => 'danger',
                                  'SP3' => 'dark',
                                  default => 'secondary',
                              };
                            @endphp
                            <span class="badge bg-label-{{ $spColor }}">{{ $sp->level_sp }}</span>
                          </td>
                          <td class="fw-bold text-warning">{{ $sp->total_poin_saat_sp }}</td>
                          <td class="small">{{ optional($sp->penerbit)->name ?? '-' }}</td>
                          <td class="small text-white-50">{{ $sp->catatan_tambahan ?? '-' }}</td>
                       </tr>
                    @empty
                       <tr>
                         <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat Surat Peringatan (SP).</td>
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
