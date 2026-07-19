<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-2 py-3" style="width:46px;">#</th>
        <th class="py-3 sortable cursor-pointer" data-sort-by="name" style="user-select: none;">
          Informasi User
          @if(($sortBy ?? '') === 'name')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center">Hak Akses (Role)</th>
        <th class="py-3 text-center d-none d-md-table-cell sortable cursor-pointer" data-sort-by="created_at" style="user-select: none;">
          Tanggal Join
          @if(($sortBy ?? '') === 'created_at')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $item)
        @php
          $displayName = $item->name;
          $displayEmail = $item->email;
          $inisial = strtoupper(substr($displayName, 0, 1)) . strtoupper(substr(strrchr($displayName, ' ') ?: $displayName, 1, 1));
          if (empty(trim($inisial))) {
              $inisial = strtoupper(substr($displayName, 0, 2));
          }

          // Get all roles for this user
          $userRoles = array_unique(array_filter(array_merge([$item->role], $item->roles ?? [])));

          // Determine primary role for avatar color
          $primaryRole = !empty($userRoles) ? reset($userRoles) : 'unknown';

          $avatarRoleClass = match ($primaryRole) {
              'super_admin' => 'bg-label-danger',
              'admin_sekolah' => 'bg-label-warning',
              'operator' => 'bg-label-info',
              'guru' => 'bg-label-primary',
              'wali_kelas' => 'bg-label-success',
              'staff_tu' => 'bg-label-secondary',
              'siswa' => 'bg-label-dark',
              'piket' => 'bg-label-warning',
              'orang_tua' => 'bg-label-info',
              default => 'bg-label-secondary',
          };

          $isSuperAdmin = auth()->user()->role === 'super_admin';
          $isSelf = $item->id === auth()->id();
          $canImpersonate = $isSuperAdmin && !$isSelf && $item->role !== 'super_admin';
          $canDelete = !$isSelf;
        @endphp
        <tr class="user-row-hover">
          <td class="ps-4 text-white-50 small">{{ $users->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle {{ $avatarRoleClass }}" style="font-size:0.85rem;">
                  {{ $inisial }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $displayName }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">{{ $displayEmail }}</div>
              </div>
            </div>
          </td>
          <td class="text-center">
            @if (count($userRoles) > 0)
              <div class="d-flex flex-wrap justify-content-center gap-1">
                @foreach ($userRoles as $role)
                  @php
                    $badgeClass = match ($role) {
                        'super_admin' => 'bg-label-danger',
                        'admin_sekolah' => 'bg-label-warning',
                        'operator' => 'bg-label-info',
                        'guru' => 'bg-label-primary',
                        'wali_kelas' => 'bg-label-success',
                        'staff_tu' => 'bg-label-secondary',
                        'siswa' => 'bg-label-dark',
                        'piket' => 'bg-label-warning',
                        'orang_tua' => 'bg-label-info',
                        default => 'bg-label-secondary',
                    };
                  @endphp
                  <span class="badge {{ $badgeClass }} text-capitalize">{{ str_replace('_', ' ', $role) }}</span>
                @endforeach
              </div>
            @else
              <span class="badge bg-label-secondary">-</span>
            @endif
          </td>
          <td class="text-center d-none d-md-table-cell text-white-50 small">
            {{ $item->created_at instanceof \Carbon\Carbon ? $item->created_at->format('d/m/Y') : \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              @if ($canImpersonate)
              <button type="button"
                class="action-btn text-success btn-impersonate-user"
                title="Login Sebagai User"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.impersonate.login-as', $item->id) }}"
                data-nama="{{ $item->name }}">
                <i class="ti tabler-login fs-5"></i>
              </button>
              @endif
              <a href="{{ route('admin.users.edit', $item->id) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              @if ($canDelete)
              <button type="button"
                class="action-btn text-danger btn-hapus-user"
                title="Hapus"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.users.destroy', $item->id) }}"
                data-nama="{{ $item->name }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data user.</span>
              <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-label-info mt-1">
                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
              </a>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($users->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $users->links('vendor.pagination.users') }}
  </div>
@endif
