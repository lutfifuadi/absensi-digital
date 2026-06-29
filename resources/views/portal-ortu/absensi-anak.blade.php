@extends('layouts/layoutMaster')

@section('title', 'Riwayat Absensi - ' . $anak->nama_lengkap)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 text-white overflow-hidden shadow-sm"
            style="background: linear-gradient(135deg, #7367f0 0%, #4338ca 100%); border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded d-flex align-items-center justify-content-center shadow-sm"
                            style="width:52px;height:52px;border-radius:10px !important;background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);">
                            <i class="ti tabler-calendar-stats text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                            class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Riwayat Absensi</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Riwayat Absensi Anak</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header border-bottom py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="card-title mb-0 fw-bold text-white"><i class="ti tabler-list-check me-2 text-primary"></i>Absensi {{ $anak->nama_lengkap }}</h5>
            
            <form action="{{ route('ortu.anak.absensi', $anak->id) }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2000, $m, 1)->translatedFormat('F') }}</option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm">
                    @for($y=now()->year; $y>=now()->year-2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">Filter</button>
            </form>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3">Tanggal</th>
                    <th class="py-3">Jam Masuk</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $row)
                    <tr>
                        <td class="fw-semibold text-white py-3">{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
                        <td class="text-white">{{ $row->jam_masuk ?? '-' }}</td>
                        <td>
                            @php
                                $badgeClass = match($row->status) {
                                    'hadir' => 'bg-label-success',
                                    'terlambat' => 'bg-label-warning',
                                    'sakit' => 'bg-label-info',
                                    'izin' => 'bg-label-primary',
                                    'alpha' => 'bg-label-danger',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2.5 py-1 text-uppercase" style="font-size: 0.75rem;">{{ $row->status }}</span>
                        </td>
                        <td><small class="text-muted">{{ strtoupper($row->metode ?? '-') }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2 opacity-50 text-white">
                                <i class="ti tabler-calendar-off" style="font-size: 2.5rem;"></i>
                                <span class="small">Tidak ada data absensi untuk bulan ini.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
