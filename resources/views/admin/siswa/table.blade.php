<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-3 py-3" style="width:40px;">
          <input class="form-check-input select-all-siswa-checkbox" type="checkbox" id="selectAllSiswa">
        </th>
        <th class="ps-2 py-3" style="width:46px;">#</th>
        <th class="py-3 sortable cursor-pointer text-nowrap" data-sort-by="nama_lengkap" style="user-select: none;">
          Informasi Siswa
          @if(($sortBy ?? '') === 'nama_lengkap')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 d-none d-md-table-cell text-center sortable cursor-pointer text-nowrap" data-sort-by="nisn" style="user-select: none;">
          NISN
          @if(($sortBy ?? '') === 'nisn')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center sortable cursor-pointer text-nowrap" data-sort-by="kelas_id" style="user-select: none;">
          Kelas
          @if(($sortBy ?? '') === 'kelas_id')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center text-nowrap">Tahun Lulus</th>
        <th class="py-3 d-none d-xl-table-cell text-center text-nowrap">Tahun Akademik</th>
        <th class="py-3 text-center sortable cursor-pointer text-nowrap" data-sort-by="status" style="user-select: none;">
          Status
          @if(($sortBy ?? '') === 'status')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'asc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 pe-4 text-end text-nowrap">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($siswa as $item)
        <tr class="siswa-row-hover">
          <td class="ps-3">
            <input class="form-check-input siswa-checkbox" type="checkbox" value="{{ $item->id }}">
          </td>
          <td class="ps-4 text-white-50 small">{{ $siswa->firstItem() + $loop->index }}</td>
          <td class="text-nowrap">
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle bg-label-{{ $item->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size:0.85rem;">
                  {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($item->nama_lengkap, ' ') ?: $item->nama_lengkap, 1, 1)) }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $item->nama_lengkap }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">NIS: {{ $item->nis ?: '-' }}</div>
              </div>
            </div>
          </td>
          <td class="d-none d-md-table-cell text-center text-nowrap">
            <div class="small fw-medium text-white-50">{{ $item->nisn ?: '-' }}</div>
          </td>
          <td class="text-center text-nowrap">
            <span class="badge bg-label-info px-2 py-1">{{ optional($item->kelas)->nama ?? '-' }}</span>
          </td>
          <td class="text-center text-nowrap">
            @php
              $tahunLulus = \App\Helpers\JenjangHelper::getTahunLulusSiswa($item);
            @endphp
            <span class="badge px-2 py-1 fw-bold" style="background-color: #000000 !important; color: #ff9f43 !important; border: 1px solid #ff9f43 !important; opacity: 1 !important;">{{ $tahunLulus ?? '-' }}</span>
          </td>
          <td class="d-none d-xl-table-cell text-center text-nowrap">
            <div class="small text-white-50">{{ optional($item->tahunAkademik)->nama ?? '-' }}</div>
          </td>
          <td class="text-center">
            @php
              $statusColor = match ($item->status) {
                  'aktif' => 'success',
                  'nonaktif' => 'danger',
                  'alumni' => 'warning',
                  default => 'secondary',
              };
            @endphp
            <span class="badge bg-label-{{ $statusColor }} text-capitalize px-2">{{ $item->status }}</span>
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              @if(!$isWaliKelas && $item->user_id && auth()->user()->hasAnyRole(['super_admin', 'admin_sekolah']))
              <button type="button"
                class="action-btn text-success btn-impersonate-siswa"
                title="Login As Siswa"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.siswa.impersonate', $item) }}"
                data-nama="{{ $item->nama_lengkap }}">
                <i class="ti tabler-user-share fs-5"></i>
              </button>
              @endif
              <a href="{{ route('admin.siswa.profil', $item) }}" class="action-btn text-info" title="Lihat Profil" data-bs-toggle="tooltip">
                <i class="ti tabler-eye fs-5"></i>
              </a>
              @if(!$isWaliKelas)
              <a href="{{ route('admin.siswa.edit', $item) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <button type="button"
                class="action-btn text-danger btn-hapus-siswa"
                title="Hapus"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.siswa.destroy', $item) }}"
                data-nama="{{ $item->nama_lengkap }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data siswa.</span>
              <a href="{{ route('admin.siswa.create') }}" class="btn btn-sm btn-label-info mt-1">
                <i class="ti tabler-plus me-1"></i> Tambah Sekarang
              </a>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($siswa->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $siswa->links('vendor.pagination.users') }}
  </div>
@endif
