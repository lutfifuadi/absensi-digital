@extends('layouts/layoutMaster')

@section('title', 'Daftar Izin/Sakit Anak')

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
                            <i class="ti tabler-file-text text-white fs-3"></i>
                        </div>
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-1" style="font-size:0.72rem;opacity:0.8;">
                                    <li class="breadcrumb-item"><a href="{{ route('ortu.dashboard') }}"
                                            class="text-white text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">Daftar Izin</li>
                                </ol>
                            </nav>
                            <h4 class="mb-0 text-white fw-bold" style="letter-spacing:-0.5px;">Riwayat Izin & Sakit</h4>
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('ortu.izin-sakit.create') }}" class="btn bg-white text-primary fw-semibold d-flex align-items-center gap-1 shadow-sm">
                            <i class="ti tabler-plus fs-5"></i> Ajukan Izin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
        <i class="ti tabler-circle-check fs-5"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
        <i class="ti tabler-alert-triangle fs-5"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="py-3">Anak</th>
                    <th class="py-3">Jenis</th>
                    <th class="py-3">Periode</th>
                    <th class="py-3">Status</th>
                    <th class="py-3">Tgl Pengajuan</th>
                    <th class="py-3 pe-4 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($izinSakit as $row)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center gap-2">
                                <div class="avatar avatar-sm">
                                    <span class="avatar-initial rounded-circle bg-label-info">{{ substr($row->user->name, 0, 2) }}</span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-white">{{ $row->user->name }}</span>
                                    <small class="text-muted" style="font-size: 0.72rem;">Kelas: {{ $row->user->siswa->kelas->nama ?? '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $row->jenis === 'sakit' ? 'info' : 'primary' }} text-capitalize px-2.5 py-1" style="font-size: 0.75rem;">
                                {{ $row->jenis }}
                            </span>
                        </td>
                        <td>
                            <div class="fw-semibold text-white">
                                @if($row->tanggal_mulai == $row->tanggal_selesai)
                                    {{ \Carbon\Carbon::parse($row->tanggal_mulai)->translatedFormat('d M Y') }}
                                @else
                                    {{ \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m') }} - {{ \Carbon\Carbon::parse($row->tanggal_selesai)->translatedFormat('d/m/Y') }}
                                @endif
                            </div>
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
                            <span class="badge {{ $badgeClass }} px-2.5 py-1 text-uppercase" style="font-size: 0.75rem;">{{ $row->status }}</span>
                        </td>
                        <td><small class="text-muted">{{ $row->created_at->format('d/m/Y H:i') }}</small></td>
                        <td class="pe-4 text-end">
                            @if($row->status === 'pending')
                                <form action="{{ route('ortu.izin-sakit.destroy', $row->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-label-danger fw-semibold">
                                        <i class="ti tabler-trash ti-xs me-1"></i> Batalkan
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center gap-2 opacity-50 text-white">
                                <i class="ti tabler-calendar-off" style="font-size: 2.5rem;"></i>
                                <span class="small">Belum ada riwayat pengajuan izin/sakit.</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($izinSakit->hasPages())
        <div class="card-footer px-4 py-3 border-top">
            {{ $izinSakit->links() }}
        </div>
    @endif
</div>
@endsection
