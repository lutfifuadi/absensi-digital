@extends('layouts/layoutMaster')

@section('title', 'Rekap Absensi Kegiatan')

@section('page-style')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.04) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        backdrop-filter: blur(10px);
    }
    .hero-master {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        border-radius: 4px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    }
    .icon-box {
        width: 60px;
        height: 60px;
        border-radius: 12px !important;
        background: rgba(115, 103, 240, 0.3);
        border: 1px solid rgba(115, 103, 240, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table-dark-custom {
        background: rgba(255, 255, 255, 0.02);
    }
    .table-dark-custom thead th {
        background: rgba(115, 103, 240, 0.1);
        color: rgba(255, 255, 255, 0.7);
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 1px;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
</style>
@endsection

@section('content')
{{-- HERO HEADER (Matched with Master Data) --}}
<div class="hero-master text-white mb-4 overflow-hidden">
    <div class="card-body p-4 p-md-5">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 mb-1">
                    <div class="icon-box shadow-sm">
                        <i class="ti tabler-report-analytics text-white fs-2"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 text-white fw-bold" style="letter-spacing: -0.5px;">Rekap Kehadiran</h4>
                        <div class="badge bg-black bg-opacity-25 text-white border border-white border-opacity-10 mt-1"
                            style="font-size: 0.65rem;">
                            Log Aktivitas Kegiatan Khusus
                        </div>
                    </div>
                </div>
                <p class="mb-0 text-white opacity-75 small mt-2">Pantau dan audit seluruh riwayat pemindaian kehadiran siswa pada agenda sekolah.</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('admin.absensi-kegiatan.scan') }}" class="btn btn-primary fw-bold px-4" style="border-radius: 8px;">
                    <i class="ti tabler-camera me-1"></i> Buka Scanner
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 mb-4 shadow-none">
    <div class="card-body">
        <form action="{{ route('admin.absensi-kegiatan.rekap') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label text-white-50 small fw-bold mb-2">FILTER BERDASARKAN AGENDA</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text bg-dark border-secondary text-white-50"><i class="ti tabler-filter"></i></span>
                    <select name="kegiatan_id" class="form-select bg-dark border-secondary text-white" style="border-radius: 0 8px 8px 0;">
                        <option value="">-- Tampilkan Semua Riwayat --</option>
                        @foreach($kegiatans as $k)
                            <option value="{{ $k->id }}" {{ request('kegiatan_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kegiatan }} ({{ $k->tanggal_pelaksanaan->format('d M Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-label-primary w-100 fw-bold" style="border-radius: 8px;">
                    <i class="ti tabler-search me-1"></i> Cari Data
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card glass-card border-0 shadow-none overflow-hidden">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0 table-dark-custom">
            <thead>
                <tr>
                    <th>Identitas Siswa</th>
                    <th>Rombel/Kelas</th>
                    <th>Nama Agenda</th>
                    <th class="text-center">Waktu Scan</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($logs as $log)
                <tr>
                    <td>
                        <div class="fw-bold text-white mb-0" style="font-size: 0.9rem;">{{ $log->siswa->nama }}</div>
                        <small class="text-white-50" style="font-size: 0.75rem;">NIS: {{ $log->siswa->nis }}</small>
                    </td>
                    <td>
                         <span class="text-white-50 small"><i class="ti tabler-door me-1"></i>{{ $log->siswa->kelas->nama }}</span>
                    </td>
                    <td>
                        <div class="badge bg-label-info border border-info border-opacity-10" style="font-size: 0.7rem;">{{ $log->kegiatan->nama_kegiatan }}</div>
                    </td>
                    <td class="text-center">
                        <span class="text-white small"><i class="ti tabler-clock-play me-1 text-success"></i>{{ $log->jam_absen->format('H:i:s') }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success shadow-sm px-3" style="font-size: 0.65rem;">HADIR</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-white-50">
                        <i class="ti tabler-database-x fs-1 d-block mb-3 opacity-25"></i>
                        <div class="small">Tidak ditemukan data yang sesuai dengan kriteria filter.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-transparent border-top border-white border-opacity-10">
        {{ $logs->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
