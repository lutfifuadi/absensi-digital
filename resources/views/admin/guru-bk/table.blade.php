<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3 sortable cursor-pointer text-nowrap" data-sort-by="nama_lengkap" style="user-select: none;">
          Informasi Guru BK
          @if(($sortBy ?? '') === 'nama_lengkap')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 d-none d-md-table-cell sortable cursor-pointer text-nowrap" data-sort-by="nip" style="user-select: none;">
          NIP
          @if(($sortBy ?? '') === 'nip')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center sortable cursor-pointer text-nowrap" data-sort-by="status" style="user-select: none;">
          Status
          @if(($sortBy ?? '') === 'status')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 d-none d-lg-table-cell text-nowrap">Hak Akses</th>
        <th class="py-3 pe-4 text-end text-nowrap">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($guruBkUsers as $item)
        @php
          $profile        = $item->guru;
          $displayName    = $profile->nama_lengkap ?? $item->name;
          $displayJabatan = $profile->jabatan ?? 'Guru BK (Bimbingan Konseling)';
          $displayNip     = $profile->nip ?? '-';
          $displayStatus  = $profile->status ?? 'aktif';
          $statusClass    = $displayStatus === 'aktif' ? 'success' : 'secondary';
        @endphp
        <tr class="bk-row-hover">
          <td class="ps-4 text-white-50 small">{{ $guruBkUsers->firstItem() + $loop->index }}</td>
          <td class="text-nowrap">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle bg-label-warning fw-bold" style="font-size:0.85rem;">
                  {{ strtoupper(substr($displayName, 0, 1)) }}{{ strtoupper(substr(strrchr($displayName, ' ') ?: $displayName, 1, 1)) }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0 text-white" style="font-size:0.9rem;">{{ $displayName }}</div>
                <div class="text-white-50 small" style="font-size:0.75rem;">{{ $displayJabatan }} &bull; {{ $item->email }}</div>
              </div>
            </div>
          </td>
          <td class="d-none d-md-table-cell text-white-50 small">
            {{ $displayNip }}
          </td>
          <td class="text-center">
            <span class="badge bg-label-{{ $statusClass }} text-capitalize px-2">{{ $displayStatus }}</span>
          </td>
          <td class="d-none d-lg-table-cell text-capitalize small text-white-50">
            <span class="badge bg-label-warning text-uppercase"><i class="ti tabler-shield-check me-1"></i> Guru BK Lintas Kelas</span>
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('bk.dashboard') }}" class="action-btn text-warning" title="Lihat Dashboard BK" data-bs-toggle="tooltip">
                <i class="ti tabler-dashboard fs-5"></i>
              </a>
              @if ($item->id)
                <button type="button"
                  class="action-btn text-success btn-impersonate-bk"
                  title="Login Sebagai Guru BK"
                  data-bs-toggle="tooltip"
                  data-url="{{ route('admin.impersonate.login-as', $item->id) }}"
                  data-nama="{{ $displayName }}">
                  <i class="ti tabler-login fs-5"></i>
                </button>
              @endif
              @if ($profile)
                <button type="button"
                  class="action-btn text-danger btn-delete-guru-bk"
                  title="Cabut Status Guru BK"
                  data-bs-toggle="tooltip"
                  data-id="{{ $profile->id }}"
                  data-nama="{{ $displayName }}">
                  <i class="ti tabler-user-minus fs-5"></i>
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center py-5 text-white-50">
            <i class="ti tabler-user-search fs-1 d-block mb-2 opacity-50"></i>
            Tidak ada data Guru BK yang ditemukan.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="d-flex justify-content-between align-items-center px-4 py-3 border-top border-white-10">
  <div class="text-white-50 small">
    Menampilkan {{ $guruBkUsers->firstItem() ?? 0 }} sampai {{ $guruBkUsers->lastItem() ?? 0 }} dari {{ $guruBkUsers->total() }} Guru BK
  </div>
  <div>
    {{ $guruBkUsers->links() }}
  </div>
</div>
