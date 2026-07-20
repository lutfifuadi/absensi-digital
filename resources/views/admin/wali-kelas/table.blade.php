<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3 sortable cursor-pointer" data-sort-by="nama_lengkap" style="user-select: none;">
          Informasi Wali Kelas
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
        <th class="py-3 sortable cursor-pointer" data-sort-by="mata_pelajaran" style="user-select: none;">
          Mata Pelajaran
          @if(($sortBy ?? '') === 'mata_pelajaran')
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
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($waliKelasUsers as $item)
        @php
          $profile        = $item->guru;
          $displayName    = $profile->nama_lengkap ?? $item->name;
          $displayJabatan = $profile->jabatan ?? 'Wali Kelas';
          $displayNip     = $profile->nip ?? '-';
          $displayMapel   = $profile ? ($profile->mata_pelajaran ?: 'Belum diisi') : 'Belum diisi';
          $displayStatus  = $profile->status ?? 'belum lengkap';
          $statusClass    = $profile ? ($profile->status === 'aktif' ? 'success' : 'danger') : 'secondary';
        @endphp
        <tr class="wk-row-hover">
          <td class="ps-4 text-white-50 small">{{ $waliKelasUsers->firstItem() + $loop->index }}</td>
          <td class="text-nowrap">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle bg-label-info" style="font-size:0.85rem;">
                  {{ strtoupper(substr($displayName, 0, 1)) }}{{ strtoupper(substr(strrchr($displayName, ' ') ?: $displayName, 1, 1)) }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $displayName }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">{{ $displayJabatan }}</div>
              </div>
            </div>
          </td>
          <td class="d-none d-md-table-cell text-white-50 small">
            {{ $displayNip }}
          </td>
          <td>
            @if ($profile && $profile->mata_pelajaran)
              <span class="badge bg-label-warning text-capitalize">{{ $displayMapel }}</span>
            @else
              <span class="text-white-50 small">Belum diisi</span>
            @endif
          </td>
          <td class="text-center">
            <span class="badge bg-label-{{ $statusClass }} text-capitalize px-2">{{ $displayStatus }}</span>
          </td>
          <td class="d-none d-lg-table-cell text-capitalize small text-white-50">
            @php
              $userRoles = [];
              if ($item->role) {
                $userRoles[] = $item->role;
              }
              $userRoles = array_unique(array_filter(array_merge($userRoles, $item->roles ?? [])));
            @endphp
            @if (count($userRoles) > 0)
              {{ implode(', ', array_map(fn ($role) => str_replace('_', ' ', ucfirst($role)), $userRoles)) }}
            @else
              -
            @endif
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              @if ($profile)
                <a href="{{ route('admin.wali-kelas.generate-qr', $profile->id) }}" class="action-btn text-info"
                  title="Unduh QR" data-bs-toggle="tooltip">
                  <i class="ti tabler-qrcode fs-5"></i>
                </a>
                <a href="{{ route('admin.wali-kelas.edit', $profile->id) }}" class="action-btn text-warning"
                  title="Ubah" data-bs-toggle="tooltip">
                  <i class="ti tabler-pencil fs-5"></i>
                </a>
                <button type="button" class="action-btn text-danger btn-hapus-wali-kelas" title="Hapus"
                  data-bs-toggle="tooltip" data-url="{{ route('admin.wali-kelas.destroy', $profile->id) }}"
                  data-nama="{{ $displayName }}">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              @else
                <a href="{{ route('admin.wali-kelas.create', ['user_id' => $item->id]) }}"
                  class="action-btn text-warning" title="Lengkapi Profil Wali Kelas" data-bs-toggle="tooltip">
                  <i class="ti tabler-pencil fs-5"></i>
                </a>
                <button type="button" class="action-btn text-danger btn-hapus-user-wali-kelas" title="Hapus Akun"
                  data-bs-toggle="tooltip" data-url="{{ route('admin.wali-kelas.destroy-user', $item->id) }}"
                  data-nama="{{ $displayName }}">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data wali kelas.</span>
              <a href="{{ route('admin.wali-kelas.create') }}" class="btn btn-sm btn-label-info mt-1">
                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
              </a>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($waliKelasUsers->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
    {{ $waliKelasUsers->links('vendor.pagination.users') }}
  </div>
@endif
