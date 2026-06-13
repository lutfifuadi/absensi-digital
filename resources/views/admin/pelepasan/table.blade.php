<div class="table-responsive">
    <table class="das-table">
        <thead>
            <tr>
                <th width="40">#</th>
                <th>Siswa / NISN</th>
                <th>Kelas</th>
                <th>Kehadiran</th>
                <th>Waktu Masuk</th>
                <th>Kontak Wali</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswaList as $idx => $s)
                @php
                    $log = $s->absensiKegiatan->first();
                @endphp
                <tr>
                    <td class="text-muted small text-center">{{ ($siswaList->currentPage()-1) * $siswaList->perPage() + $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-xs">
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold text-white mb-0" style="font-size:.85rem;">{{ $s->nama_lengkap }}</div>
                                <div class="text-muted small" style="font-size:.7rem;">NISN: {{ $s->nisn }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $s->kelas->nama ?? '-' }}</td>
                    <td>
                        @if($log)
                            <span class="das-chip das-chip--success">Hadir</span>
                        @else
                            <span class="das-chip das-chip--danger">Belum Hadir</span>
                        @endif
                    </td>
                    <td>
                        <span class="font-monospace small">{{ $log ? \Carbon\Carbon::parse($log->jam_absen)->format('H:i:s') : '-' }}</span>
                    </td>
                    <td>
                        <span class="small text-muted">{{ $s->no_hp_ortu ?: '-' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-5 text-center">
                        <div class="d-flex flex-column align-items-center gap-2 opacity-30">
                            <i class="ti tabler-users-minus" style="font-size:3rem;"></i>
                            <span class="small font-monospace">Data siswa kelas XII tidak ditemukan</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($siswaList->hasPages())
    <div class="px-4 py-3 border-top" style="border-color:var(--das-border)!important;">
        {{ $siswaList->links('vendor.pagination.users') }}
    </div>
@endif
