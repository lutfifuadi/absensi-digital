<div class="table-responsive">
    <table class="das-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>Kelas</th>
                <th>Wali Kelas</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detailBelumAbsen as $index => $siswa)
            <tr class="alfa-row-hover">
                <td>{{ $detailBelumAbsen->firstItem() + $index }}</td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="alfa-avatar">
                            {{ substr($siswa->nama_lengkap ?? 'U', 0, 1) }}
                        </div>
                        <span>{{ $siswa->nama_lengkap ?? '-' }}</span>
                    </div>
                </td>
                <td>
                    <span class="das-chip --info">
                        {{ $siswa->kelas->nama ?? '-' }}
                    </span>
                </td>
                <td class="text-white-50">
                    {{ $siswa->kelas->waliKelas->nama ?? '-' }}
                </td>
                <td class="text-center">
                    @php
                        $noOrtu = preg_replace('/[^0-9]/', '', $siswa->no_hp_ortu ?? '');
                    @endphp
                    @if($noOrtu)
                    <a href="https://wa.me/{{ $noOrtu }}" target="_blank" rel="noopener noreferrer"
                       class="btn das-btn --success btn-sm d-inline-flex align-items-center gap-1">
                        <i class="ti tabler-brand-whatsapp"></i>
                        <span>Hubungi Wali</span>
                    </a>
                    @else
                    <span class="das-chip --secondary">Tidak Ada No. HP</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                        <i class="ti tabler-user-check" style="font-size: 2.5rem; color: rgba(40,199,111,0.3);"></i>
                        <p class="text-muted small mb-0">Semua siswa sudah absen. Tidak ada yang perlu ditindaklanjuti.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if(method_exists($detailBelumAbsen, 'links') && $detailBelumAbsen->hasPages())
<div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
    {{ $detailBelumAbsen->links() }}
</div>
@endif

{{-- Data for dynamic header updates --}}
<span id="ajaxTotalSiswaVal" class="d-none">{{ method_exists($detailBelumAbsen, 'total') ? $detailBelumAbsen->total() : $detailBelumAbsen->count() }}</span>
<span id="ajaxFilterKelasVal" class="d-none">{{ $filterKelas ?? '' }}</span>
<span id="ajaxKelasNamaVal" class="d-none">{{ $filterKelas ? ($kelasList->firstWhere('id', $filterKelas)?->nama ?? 'Semua Kelas') : 'Semua Kelas' }}</span>
