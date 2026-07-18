@extends('layouts/layoutMaster')

@section('title', 'Rekap Harian Absensi')

@section('page-style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboards/super-admin.css') }}?v=4.3">
  <style>
    .form-control, .form-select, .btn {
      border-radius: 5px !important;
    }
  </style>
@endsection

@section('content')

  {{-- HERO HEADER --}}
  <div class="das-hero mb-4">
    <div class="das-hero__bg"></div>
    <div class="das-hero__glass"></div>
    <div class="das-hero__grid-lines"></div>

    <div class="das-hero__inner">
      <div class="das-hero__identity">
        <div class="das-hero__logo-wrapper">
          <div class="das-hero__logo-placeholder">
            <i class="ti tabler-calendar-stats text-info"></i>
          </div>
          <div class="das-hero__logo-glow"></div>
        </div>

        <div class="das-hero__meta">
          <div class="das-hero__badge">
            <span class="pulse-dot"></span>
            @if($isWaliKelas)
              Kelas Saya / Laporan Rekap
            @else
              Laporan / Rekap Harian
            @endif
          </div>
          <h4 class="das-hero__title text-gradient-gold">Rekap Harian Kehadiran</h4>
          <p class="das-hero__subtitle">Audit dan monitoring riwayat absensi harian secara efisien.</p>
        </div>
      </div>

      <div class="das-hero__actions">
        <div class="badge bg-black bg-opacity-25 p-2 px-3 border border-white border-opacity-10 text-white">
          <i class="ti tabler-calendar me-1"></i>
          @if($tanggalMulai === $tanggalSelesai)
            {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d F Y') }}
          @else
            {{ \Carbon\Carbon::parse($tanggalMulai)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($tanggalSelesai)->translatedFormat('d M Y') }}
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Filter Card --}}
  <div class="das-panel mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
    <div class="das-panel__body">
      <form method="GET" class="row gy-3 gx-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Mulai
          </label>
          <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
        </div>
        <div class="col-md-3">
          <label class="form-label text-white-50 small fw-bold">
            <i class="ti tabler-calendar-event me-1"></i> Tanggal Selesai
          </label>
          <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
        </div>
        <div class="col-md-4">
          <label class="form-label text-white-50 small fw-bold">
            <i class="ti tabler-door me-1"></i> Filter Kelas
          </label>
          <select name="kelas_id" class="form-select">
            @if($isWaliKelas)
              <!-- Wali kelas dikunci ke kelasnya -->
              @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}" selected>{{ $k->nama }}</option>
              @endforeach
            @else
              <option value="">Semua Kelas</option>
              @foreach ($kelasOptions as $k)
                <option value="{{ $k->id }}" @selected($kelasId == $k->id)>{{ $k->nama }}</option>
              @endforeach
            @endif
          </select>
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn das-btn --primary w-100">
            <i class="ti tabler-search me-1"></i> Tampilkan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Summary Row --}}
  <div class="row gy-3 mb-4">
    @php
      $statsToShow = [
        ['label' => 'Siswa Hadir', 'val' => $summaryHarian['siswa_hadir'], 'color' => 'success', 'icon' => 'tabler-circle-check'],
        ['label' => 'Siswa Sakit', 'val' => $summaryHarian['siswa_sakit'], 'color' => 'info', 'icon' => 'tabler-stethoscope'],
        ['label' => 'Siswa Izin', 'val' => $summaryHarian['siswa_izin'], 'color' => 'warning', 'icon' => 'tabler-file-description'],
        ['label' => 'Siswa Alpha', 'val' => $summaryHarian['siswa_alpha'], 'color' => 'danger', 'icon' => 'tabler-x'],
        ['label' => 'Siswa Telat', 'val' => $summaryHarian['siswa_terlambat'], 'color' => 'primary', 'icon' => 'tabler-clock'],
      ];
      if(!$isWaliKelas) {
        $statsToShow[] = ['label' => 'Guru Hadir', 'val' => $summaryHarian['guru_hadir'], 'color' => 'primary', 'icon' => 'tabler-chalkboard-teacher'];
        $statsToShow[] = ['label' => 'Staff Hadir', 'val' => $summaryHarian['staff_hadir'], 'color' => 'gold', 'icon' => 'tabler-briefcase'];
      }
    @endphp
    @foreach ($statsToShow as $stat)
      <div class="col-6 col-md-4 col-lg">
        <div class="card card-grad-{{ $stat['color'] }} h-100 text-center">
          <div class="card-body py-3">
             <div class="avatar avatar-sm mx-auto mb-2">
                <span class="avatar-initial rounded bg-label-{{ $stat['color'] }}">
                   <i class="ti {{ $stat['icon'] }}"></i>
                </span>
             </div>
             <h4 class="mb-0 text-white fw-bold">{{ $stat['val'] }}</h4>
             <small class="text-white-50 opacity-75" style="font-size:0.7rem;">{{ $stat['label'] }}</small>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- Absensi Siswa --}}
  <div class="das-panel mb-4">
    <div class="das-panel__head">
      <div class="das-panel__title">
        <i class="ti tabler-users text-info"></i> Absensi Siswa
      </div>
    </div>
    <div class="das-table-wrap">
      <table class="das-table align-middle mb-0">
        <thead>
          <tr>
            <th class="ps-4 py-3" width="60">#</th>
            <th class="py-3">Nama Siswa</th>
            <th class="py-3">Kelas</th>
            <th class="py-3 text-center">Tanggal</th>
            <th class="py-3 text-center">Jam Masuk</th>
            <th class="py-3 text-center">Jam Pulang</th>
            <th class="py-3 text-center">Status</th>
            <th class="py-3 text-end pe-4">Metode</th>
          </tr>
        </thead>
        <tbody>
          @forelse($absensiSiswa as $row)
            <tr>
              <td class="ps-4 text-white-50 small">{{ $loop->iteration }}</td>
              <td><span class="fw-bold text-white">{{ $row->siswa->nama_lengkap ?? '-' }}</span></td>
              <td><span class="badge bg-label-secondary">{{ $row->kelas->nama ?? '-' }}</span></td>
              <td class="text-center">{{ $row->tanggal->format('d M Y') }}</td>
              <td class="text-center"><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
              <td class="text-center"><code class="text-info">{{ $row->jam_pulang ? substr($row->jam_pulang, 0, 5) : '-' }}</code></td>
              <td class="text-center">
                @php
                  $scolor = match ($row->status) {
                      'hadir' => 'success',
                      'sakit' => 'info',
                      'izin' => 'warning',
                      'alpha' => 'danger',
                      'terlambat' => 'primary',
                      default => 'secondary',
                  };
                @endphp
                <span class="badge bg-label-{{ $scolor }} px-2 py-1 rounded-pill small">{{ ucfirst($row->status) }}</span>
              </td>
              <td class="text-end pe-4"><span class="small text-white-50">{{ ucfirst($row->metode) }}</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center py-5">
                 <div class="opacity-50">
                    <i class="ti tabler-users-minus fs-1 d-block mb-2"></i>
                    <span class="small">Tidak ada data absensi siswa dalam rentang tanggal ini.</span>
                 </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if(!$isWaliKelas)
  <div class="row">
    {{-- Absensi Guru --}}
    <div class="col-md-6 mb-4">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <i class="ti tabler-chalkboard-teacher text-info"></i> Absensi Guru
          </div>
        </div>
        <div class="das-table-wrap">
          <table class="das-table align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-3 py-3" width="40">#</th>
                <th class="py-3">Nama Guru</th>
                <th class="py-3 text-center">Jam</th>
                <th class="py-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($absensiGuru as $row)
                <tr>
                  <td class="ps-3 text-white-50 small">{{ $loop->iteration }}</td>
                  <td><div class="fw-semibold text-white">{{ $row->guru->nama_lengkap ?? '-' }}</div></td>
                  <td class="text-center"><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
                  <td class="text-center">
                    <span class="badge bg-label-{{ match ($row->status) {
                        'hadir' => 'success',
                        'sakit' => 'info',
                        'izin' => 'warning',
                        'alpha' => 'danger',
                        default => 'secondary',
                    } }} px-2 py-1 small">{{ ucfirst($row->status) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-5 text-white-50 opacity-50 small">Tidak ada data.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- Absensi Staff --}}
    <div class="col-md-6 mb-4">
      <div class="das-panel h-100">
        <div class="das-panel__head">
          <div class="das-panel__title">
            <i class="ti tabler-briefcase text-info"></i> Absensi Staff TU
          </div>
        </div>
        <div class="das-table-wrap">
          <table class="das-table align-middle mb-0">
            <thead>
              <tr>
                <th class="ps-3 py-3" width="40">#</th>
                <th class="py-3">Nama Staff</th>
                <th class="py-3 text-center">Jam</th>
                <th class="py-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($absensiStaff as $row)
                <tr>
                  <td class="ps-3 text-white-50 small">{{ $loop->iteration }}</td>
                  <td><div class="fw-semibold text-white">{{ $row->staff->nama_lengkap ?? '-' }}</div></td>
                  <td class="text-center"><code class="text-info">{{ $row->jam_masuk ? substr($row->jam_masuk, 0, 5) : '-' }}</code></td>
                  <td class="text-center">
                    <span class="badge bg-label-{{ match ($row->status) {
                        'hadir' => 'success',
                        'sakit' => 'info',
                        'izin' => 'warning',
                        'alpha' => 'danger',
                        default => 'secondary',
                    } }} px-2 py-1 small">{{ ucfirst($row->status) }}</span>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center py-5 text-white-50 opacity-50 small">Tidak ada data.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif
@endsection

