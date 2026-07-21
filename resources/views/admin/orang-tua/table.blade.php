<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="color:inherit;">
        <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
            <tr>
                <th class="ps-4 py-3" style="width:46px;">#</th>
                <th class="py-3 sortable cursor-pointer" data-sort-by="name" style="user-select: none;">
                    Nama Orang Tua
                    @if(($sortBy ?? 'name') === 'name')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 sortable cursor-pointer" data-sort-by="email" style="user-select: none;">
                    Akun
                    @if(($sortBy ?? '') === 'email')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 text-center">Menghubungkan Siswa</th>
                <th class="py-3 text-center sortable cursor-pointer" data-sort-by="status" style="user-select: none;">
                    Status
                    @if(($sortBy ?? '') === 'status')
                        <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </th>
                <th class="py-3 pe-4 text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orangTua as $item)
                <tr class="ortu-row-hover">
                    <td class="ps-4 text-white-50 small">{{ $orangTua->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md">
                                <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                                    {{ strtoupper(substr($item->name, 0, 1)) }}{{ strtoupper(substr(strrchr($item->name, ' ') ?: $item->name, 1, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $item->name }}</div>
                                <div class="text-white-50 small" style="font-size:0.72rem;">ID: #{{ $item->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="small fw-medium">Username: {{ $item->username }}</div>
                        <div class="text-white-50 small" style="font-size:0.72rem;">Pass Default: NISN Anak / password123</div>
                    </td>
                    <td class="text-center">
                        @if($item->children->count() > 0)
                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                @foreach($item->children as $child)
                                    <span class="badge bg-label-info px-2 py-1" style="font-size:0.7rem;">
                                        {{ $child->nama_lengkap }} ({{ optional($child->kelas)->nama ?? '-' }})
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-white-50 small">- Belum ada siswa -</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-label-{{ $item->status === 'aktif' ? 'success' : 'danger' }} text-capitalize px-2">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="pe-4 text-end">
                        <div class="d-flex justify-content-end gap-1">
                            @if(auth()->user()->isSuperAdmin() && auth()->id() !== $item->id)
                                <button type="button"
                                    class="action-btn text-success btn-impersonate-ortu"
                                    title="Login Sebagai Orang Tua"
                                    data-bs-toggle="tooltip"
                                    data-url="{{ route('admin.impersonate.login-as', $item->id) }}"
                                    data-nama="{{ $item->name }}">
                                    <i class="ti tabler-login fs-5"></i>
                                </button>
                            @endif
                            <a href="{{ route('admin.orang-tua.show', $item) }}" class="action-btn text-info" title="Detail" data-bs-toggle="tooltip">
                                <i class="ti tabler-eye fs-5"></i>
                            </a>
                            <button type="button"
                                class="action-btn text-purple btn-reset-password-ortu"
                                title="Reset Password"
                                data-bs-toggle="tooltip"
                                data-url="{{ route('admin.orang-tua.reset-password', $item) }}"
                                data-nama="{{ $item->name }}">
                                <i class="ti tabler-key fs-5"></i>
                            </button>
                            <a href="{{ route('admin.orang-tua.edit', $item) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                                <i class="ti tabler-pencil fs-5"></i>
                            </a>
                            <button type="button"
                                class="action-btn text-danger btn-hapus-ortu"
                                title="Hapus"
                                data-bs-toggle="tooltip"
                                data-url="{{ route('admin.orang-tua.destroy', $item) }}"
                                data-nama="{{ $item->name }}">
                                <i class="ti tabler-trash fs-5"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2 opacity-50">
                            <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
                            <span class="small">Belum ada data orang tua.</span>
                            <a href="{{ route('admin.orang-tua.create') }}" class="btn btn-sm btn-label-info mt-1">
                                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($orangTua->hasPages())
    <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
        {{ $orangTua->links('vendor.pagination.users') }}
    </div>
@endif
