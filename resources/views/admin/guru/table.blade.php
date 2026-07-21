<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-3 py-3" style="width:46px;">
          <input class="form-check-input select-all-guru" type="checkbox" id="selectAllGuru">
        </th>
        <th class="py-3 sortable cursor-pointer" data-sort-by="nama_lengkap" style="user-select: none;">
          Informasi Guru
          @if(($sortBy ?? '') === 'nama_lengkap')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 d-none d-md-table-cell sortable cursor-pointer" data-sort-by="nip" style="user-select: none;">
          NIP
          @if(($sortBy ?? '') === 'nip')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3">Mata Pelajaran</th>
        <th class="py-3 d-none d-lg-table-cell sortable cursor-pointer" data-sort-by="jabatan" style="user-select: none;">
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
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($guruUsers as $item)
        @php
          $displayName = $item->nama_lengkap;
          $displayJabatan = $item->jabatan ?? 'Guru';
          $displayMapel = $item->mata_pelajaran ?: 'Belum diisi';
          $statusColor = $item->status === 'aktif' ? 'success' : 'danger';
          $inisial = strtoupper(substr($displayName, 0, 1)) . strtoupper(substr(strrchr($displayName, ' ') ?: $displayName, 1, 1));
        @endphp
        <tr class="guru-row-hover">
          <td class="ps-3">
            <input class="form-check-input select-guru-cb" type="checkbox" value="{{ $item->id }}" data-nama="{{ $displayName }}">
          </td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                  {{ $inisial }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $displayName }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">{{ $item->no_hp ?? 'No HP -' }}</div>
              </div>
            </div>
          </td>
          <td class="d-none d-md-table-cell text-white-50 small">
            {{ $item->nip ?: '-' }}
          </td>
          <td>
            @if($item->mata_pelajaran)
              <span class="badge bg-label-warning text-capitalize">{{ $displayMapel }}</span>
            @else
              <span class="text-white-50 small">Belum diisi</span>
            @endif
          </td>
          <td class="d-none d-lg-table-cell text-white-50 small">
            {{ $displayJabatan }}
          </td>
          <td class="text-center">
            <span class="badge bg-label-{{ $statusColor }} text-capitalize px-2">{{ $item->status }}</span>
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              @if($item->user_id)
              <button type="button"
                class="action-btn text-success btn-impersonate-guru"
                title="Login Sebagai Guru"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.impersonate.login-as', $item->user_id) }}"
                data-nama="{{ $item->nama_lengkap }}">
                <i class="ti tabler-login fs-5"></i>
              </button>
              @endif
              <a href="{{ route('admin.guru.generate-qr', $item->id) }}" class="action-btn text-info" title="Unduh QR" data-bs-toggle="tooltip">
                <i class="ti tabler-qrcode fs-5"></i>
              </a>
              <a href="{{ route('admin.guru.edit', $item->id) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <button type="button"
                class="action-btn text-danger btn-hapus-guru"
                title="Hapus"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.guru.destroy', $item->id) }}"
                data-nama="{{ $item->nama_lengkap }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data guru.</span>
              <a href="{{ route('admin.guru.create') }}" class="btn btn-sm btn-label-info mt-1">
                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
              </a>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($guruUsers->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $guruUsers->links('vendor.pagination.users') }}
  </div>
@endif
