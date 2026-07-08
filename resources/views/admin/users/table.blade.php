<div class="table-responsive">
  <table class="das-table">
    <thead>
      <tr>
        <th class="text-center" width="60">#</th>
        <th class="sortable cursor-pointer" data-sort="name">
          <div class="d-flex align-items-center gap-1">
            Informasi User
            @if (request('sort_by', 'name') === 'name')
              <i class="ti tabler-chevron-{{ request('sort_direction', 'asc') === 'asc' ? 'up' : 'down' }} fs-6 text-primary"></i>
            @else
              <i class="ti tabler-selector text-muted fs-6"></i>
            @endif
          </div>
        </th>
        <th class="text-center">Hak Akses (Role)</th>
        <th class="text-center d-none d-md-table-cell sortable cursor-pointer" data-sort="created_at">
          <div class="d-flex align-items-center justify-content-center gap-1">
            Tanggal Join
            @if (request('sort_by') === 'created_at')
              <i class="ti tabler-chevron-{{ request('sort_direction', 'asc') === 'asc' ? 'up' : 'down' }} fs-6 text-primary"></i>
            @else
              <i class="ti tabler-selector text-muted fs-6"></i>
            @endif
          </div>
        </th>
        <th class="text-end px-4">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr>
          <td class="text-center text-muted font-monospace small">
            {{ $users->firstItem() + $loop->index }}
          </td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="das-avatar-circle">
                @php
                  $initials = strtoupper(substr($user->name, 0, 1)) . (strpos($user->name, ' ') !== false ? strtoupper(substr(strrchr($user->name, ' '), 1, 1)) : '');
                @endphp
                {{ $initials ?: strtoupper(substr($user->name, 0, 2)) }}
              </div>
              <div>
                <div class="fw-bold mb-0 text-white" style="font-size:0.85rem;">{{ $user->name }}</div>
                <div class="text-muted small" style="font-size:0.72rem;">{{ $user->email }}</div>
              </div>
            </div>
          </td>
          <td class="text-center">
            @php
              $userRoles = array_unique(array_filter(array_merge([$user->role], $user->roles ?? [])));
            @endphp
            @if (count($userRoles) > 0)
              <div class="d-flex flex-wrap justify-content-center gap-1">
                @foreach ($userRoles as $role)
                  @php
                    $roleClass = match ($role) {
                        'super_admin' => 'das-chip--danger',
                        'admin_sekolah' => 'das-chip--warning',
                        'guru' => 'das-chip--info',
                        'wali_kelas' => 'das-chip--success',
                        'staff_tu' => 'das-chip--secondary',
                        default => 'das-chip--primary',
                    };
                  @endphp
                  <span class="das-chip {{ $roleClass }} text-capitalize">{{ str_replace('_', ' ', ucfirst($role)) }}</span>
                @endforeach
              </div>
            @else
              <span class="das-chip das-chip--secondary">-</span>
            @endif
          </td>
          <td class="text-center d-none d-md-table-cell text-muted font-monospace small">
            {{ $user->created_at->format('d/m/Y') }}
          </td>
          <td class="px-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              @if (auth()->user()->role === 'super_admin' && $user->id !== auth()->id() && $user->role !== 'super_admin')
                <button type="button" class="das-table-btn das-table-btn--warning" title="Login Sebagai User"
                  data-bs-toggle="modal" data-bs-target="#impersonateModal" data-name="{{ $user->name }}"
                  data-role="{{ $user->role }}" data-url="{{ route('admin.impersonate.login-as', $user->id) }}">
                  <i class="ti tabler-login fs-5"></i>
                </button>
              @endif
              
              <a href="{{ route('admin.users.edit', $user) }}" class="das-table-btn das-table-btn--info" 
                 title="Edit Data" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>

              @if ($user->id !== auth()->id())
                <button type="button" class="das-table-btn das-table-btn--danger" title="Hapus User"
                  data-bs-toggle="modal" data-bs-target="#deleteModal" data-name="{{ $user->name }}"
                  data-url="{{ route('admin.users.destroy', $user) }}">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="py-5 text-center">
            <div class="d-flex flex-column align-items-center gap-2 opacity-30">
              <i class="ti tabler-users-minus" style="font-size:3rem;"></i>
              <span class="small font-monospace uppercase letter-spacing-1">Belum ada data user</span>
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

<input type="hidden" id="ajaxTotalCount" value="{{ $users->total() }}">
