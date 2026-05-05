@extends('layouts/layoutMaster')

@section('title', 'Riwayat Absensi - ' . $anak->nama_lengkap)

@section('content')
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Riwayat Absensi</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="card-title mb-0">Absensi {{ $anak->nama_lengkap }}</h5>
            
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
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Status</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->translatedFormat('d M Y') }}</td>
                        <td>{{ $row->jam_masuk ?? '-' }}</td>
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
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($row->status) }}</span>
                        </td>
                        <td><small class="text-muted">{{ strtoupper($row->metode ?? '-') }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <i class="ti tabler-calendar-off fs-1 text-muted d-block mb-2"></i>
                            Tidak ada data absensi untuk bulan ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
