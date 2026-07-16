<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="color:inherit;">
        <thead
            style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
                <th class="ps-4 py-3" style="width:50px;">#</th>
                <th class="py-3">Kode Unik</th>
                <th class="py-3">Nama Pelapor</th>
                <th class="py-3 d-none d-md-table-cell">Status Pelapor</th>
                <th class="py-3">Status</th>
                <th class="py-3 d-none d-lg-table-cell">Kategori</th>
                <th class="py-3 d-none d-lg-table-cell">Tanggal</th>
                <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengaduan as $item)
                <tr class="pengaduan-row-hover">
                    <td class="ps-4 text-white-50 small">{{ $loop->iteration + ($pengaduan->currentPage() - 1) * $pengaduan->perPage() }}</td>
                    <td class="text-nowrap">
                        <a href="{{ route('admin.pengaduan.show', $item->id) }}" class="text-info fw-semibold text-decoration-none" style="font-size:0.9rem;">
                            {{ $item->kode_unik }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold" style="font-size:0.9rem;">{{ $item->nama_lengkap }}</div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        @if($item->status_pelapor === 'siswa')
                            <span class="badge bg-label-info text-capitalize px-2">
                                <i class="ti tabler-user me-1" style="font-size:0.65rem;"></i>Siswa
                            </span>
                        @else
                            <span class="badge bg-label-warning text-capitalize px-2">
                                <i class="ti tabler-user-heart me-1" style="font-size:0.65rem;"></i>Orang Tua
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-status-{{ $item->status }} px-3 py-1 fw-semibold text-capitalize">
                            {{ $item->status_label }}
                        </span>
                    </td>
                    <td class="d-none d-lg-table-cell text-white-50 small">
                        {{ $item->kategori }}
                    </td>
                    <td class="d-none d-lg-table-cell text-white-50 small">
                        {{ $item->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="pe-4 text-end">
                        <a href="{{ route('admin.pengaduan.show', $item->id) }}"
                            class="action-btn text-info" title="Detail Pengaduan" data-bs-toggle="tooltip"
                            aria-label="Detail {{ $item->kode_unik }}">
                            <i class="ti tabler-eye fs-5"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                            <i class="ti tabler-report" style="font-size:2.5rem;"></i>
                            <span class="small">Belum ada pengaduan.</span>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if(method_exists($pengaduan, 'links'))
    <div class="d-flex justify-content-between align-items-center px-4 py-3 flex-wrap gap-2"
        style="border-top: 1px solid rgba(255,255,255,0.06);">
        <small class="text-white-50">
            Menampilkan {{ $pengaduan->firstItem() ?? 0 }} - {{ $pengaduan->lastItem() ?? 0 }}
            dari {{ $pengaduan->total() }} pengaduan
        </small>
        <div class="d-flex align-items-center gap-2">
            @php
                $currentPage = $pengaduan->currentPage();
                $lastPage = $pengaduan->lastPage();
                $startPage = max(1, $currentPage - 2);
                $endPage = min($lastPage, $currentPage + 2);
            @endphp

            @if($currentPage > 1)
                <a href="javascript:void(0);" class="das-page-btn" data-page="1" title="Halaman pertama">
                    <i class="ti tabler-chevrons-left" style="font-size:0.8rem;"></i>
                </a>
                <a href="javascript:void(0);" class="das-page-btn" data-page="{{ $currentPage - 1 }}" title="Sebelumnya">
                    <i class="ti tabler-chevron-left" style="font-size:0.8rem;"></i>
                </a>
            @endif

            @if($startPage > 1)
                <span class="das-page-btn das-page-dots">...</span>
            @endif

            @for($page = $startPage; $page <= $endPage; $page++)
                <a href="javascript:void(0);"
                    class="das-page-btn {{ $page == $currentPage ? 'das-page-active' : '' }}"
                    data-page="{{ $page }}">{{ $page }}</a>
            @endfor

            @if($endPage < $lastPage)
                <span class="das-page-btn das-page-dots">...</span>
            @endif

            @if($currentPage < $lastPage)
                <a href="javascript:void(0);" class="das-page-btn" data-page="{{ $currentPage + 1 }}" title="Selanjutnya">
                    <i class="ti tabler-chevron-right" style="font-size:0.8rem;"></i>
                </a>
                <a href="javascript:void(0);" class="das-page-btn" data-page="{{ $lastPage }}" title="Halaman terakhir">
                    <i class="ti tabler-chevrons-right" style="font-size:0.8rem;"></i>
                </a>
            @endif
        </div>
    </div>
@endif

<script>
// Re-init tooltips after AJAX load — using immediate invocation
try {
    const tt = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tt.map(function(el) { return new bootstrap.Tooltip(el); });
} catch(e) { /* tooltip init may fail silently */ }
</script>
