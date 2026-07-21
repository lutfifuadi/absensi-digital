<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="color:inherit;">
        <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
                <th class="ps-2 py-3" style="width:40px;"><input type="checkbox" id="checkAllStaff" class="form-check-input"></th>
                <th class="ps-2 py-3" style="width:46px;">#</th>
                <th class="py-3 sortable cursor-pointer" data-sort-by="nama_lengkap" style="user-select: none;">
                    Informasi Staff
                    @if(($sortBy ?? 'nama_lengkap') === 'nama_lengkap')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 d-none d-md-table-cell sortable cursor-pointer" data-sort-by="nip" style="user-select: none;">
                    NIP
                    @if(($sortBy ?? '') === 'nip')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 sortable cursor-pointer" data-sort-by="jabatan" style="user-select: none;">
                    Jabatan
                    @if(($sortBy ?? '') === 'jabatan')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 text-center sortable cursor-pointer" data-sort-by="status" style="user-select: none;">
                    Status
                    @if(($sortBy ?? '') === 'status')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 d-none d-lg-table-cell">Role</th>
                <th class="py-3 d-none d-xl-table-cell">Email Login</th>
                <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $item)
                <tr class="staff-row-hover">
                    <td class="ps-2"><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input staff-checkbox"></td>
                    <td class="ps-4 text-white-50 small">{{ $staff->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md">
                                <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                                    {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($item->nama_lengkap, ' ') ?: $item->nama_lengkap, 1, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $item->nama_lengkap }}</div>
                                <div class="text-white-50 small" style="font-size:0.72rem;">{{ $item->no_hp ?? 'Internal' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell text-white-50 small">
                        {{ $item->nip }}
                    </td>
                    <td>
                        <span class="badge bg-label-secondary px-2">{{ $item->jabatan ?? 'Staff' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-{{ $item->status === 'aktif' ? 'success' : 'danger' }} text-capitalize px-2">{{ $item->status }}</span>
                    </td>
                    <td class="d-none d-lg-table-cell text-capitalize small text-white-50">
                        @php $role = optional($item->user)->role; @endphp
                        {{ $role ? str_replace('_', ' ', ucfirst($role)) : '-' }}
                    </td>
                    <td class="d-none d-xl-table-cell small text-white-50">
                        {{ optional($item->user)->email ?? '-' }}
                    </td>
                    <td class="pe-4 text-end">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('admin.staff-tata-usaha.generate-qr', $item) }}" class="action-btn text-info"
                                title="Unduh QR" data-bs-toggle="tooltip">
                                <i class="ti tabler-qrcode fs-5"></i>
                            </a>
                            <a href="{{ route('admin.staff-tata-usaha.edit', $item) }}" class="action-btn text-warning"
                                title="Ubah" data-bs-toggle="tooltip">
                                <i class="ti tabler-pencil fs-5"></i>
                            </a>
                            <button type="button"
                                class="action-btn text-danger btn-hapus-staff"
                                title="Hapus"
                                data-bs-toggle="tooltip"
                                data-url="{{ route('admin.staff-tata-usaha.destroy', $item) }}"
                                data-nama="{{ $item->nama_lengkap }}">
                                <i class="ti tabler-trash fs-5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                            <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                            <span class="small">Belum ada data staff TU.</span>
                            <a href="{{ route('admin.staff-tata-usaha.create') }}" class="btn btn-sm btn-label-info mt-1">
                                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($staff->hasPages())
    <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
        {{ $staff->links('vendor.pagination.users') }}
    </div>
@endif
