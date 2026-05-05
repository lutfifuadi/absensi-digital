@extends('layouts/layoutMaster')

@section('title', 'Daftar Izin/Sakit Anak')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Daftar Izin & Sakit</h4>
            <a href="{{ route('ortu.izin-sakit.create') }}" class="btn btn-primary">
                <i class="ti tabler-plus me-1"></i> Ajukan Izin
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
        <span class="alert-icon text-success me-2">
            <i class="ti tabler-check ti-xs"></i>
        </span>
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Anak</th>
                    <th>Jenis</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Tgl Pengajuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izinSakit as $row)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold">{{ $row->user->name }}</span>
                                    <small class="text-muted">{{ $row->user->siswa->kelas->nama ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-capitalize">{{ $row->jenis }}</span>
                        </td>
                        <td>
                            @if($row->tanggal_mulai == $row->tanggal_selesai)
                                {{ \Carbon\Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($row->tanggal_selesai)->translatedFormat('d/m/Y') }}
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($row->status) {
                                    'pending' => 'bg-label-warning',
                                    'disetujui' => 'bg-label-success',
                                    'ditolak' => 'bg-label-danger',
                                    default => 'bg-label-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ strtoupper($row->status) }}</span>
                        </td>
                        <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            Belum ada riwayat pengajuan izin/sakit.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer px-3 pt-3">
        {{ $izinSakit->links() }}
    </div>
</div>
@endsection
