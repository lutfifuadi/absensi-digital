<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-2 py-3" style="width:46px;">#</th>
        <th class="py-3">NAMA SISWA</th>
        <th class="py-3 text-center">KELAS</th>
        <th class="py-3 text-center sortable cursor-pointer" data-sort-by="tanggal" style="user-select:none;">
          TANGGAL
          @if(($sortBy ?? '') === 'tanggal')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'desc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center">JAM MASUK</th>
        <th class="py-3 text-center">JAM PULANG</th>
        <th class="py-3 text-center sortable cursor-pointer" data-sort-by="status" style="user-select:none;">
          STATUS
          @if(($sortBy ?? '') === 'status')
            <i class="ti tabler-chevron-{{ ($sortDir ?? 'desc') === 'asc' ? 'up' : 'down' }} ms-1"></i>
          @endif
        </th>
        <th class="py-3 text-center">METODE</th>
        @if(!$isWaliKelas)
        <th class="py-3 pe-4 text-end">AKSI</th>
        @endif
      </tr>
    </thead>
    <tbody>
      @forelse($absensi as $item)
        <tr class="absensi-row-hover">
          <td class="ps-4 text-white-50 small">{{ $absensi->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <img src="https://ui-avatars.com/api/?name={{ urlencode($item->siswa->nama_lengkap ?? '-') }}&background=7367f0&color=fff"
                   class="das-avatar" width="28">
              <span class="fw-semibold text-white">{{ $item->siswa->nama_lengkap ?? '-' }}</span>
            </div>
          </td>
          <td class="text-center">
            <span class="das-chip --info">{{ $item->kelas->nama ?? '-' }}</span>
          </td>
          <td class="text-center">{{ $item->tanggal->format('d M Y') }}</td>
          <td class="text-center">
            <span class="fw-bold text-warning">{{ $item->jam_masuk ?? '-' }}</span>
          </td>
          <td class="text-center">
            <span class="fw-bold text-info">{{ $item->jam_pulang ?? '-' }}</span>
          </td>
          <td class="text-center">
            <span class="das-chip --{{ match ($item->status) {
                'hadir' => 'success',
                'sakit' => 'info',
                'izin' => 'warning',
                'alpha' => 'danger',
                'terlambat' => 'primary',
                default => 'dark',
            } }}">
              {{ ucfirst($item->status) }}
            </span>
          </td>
          <td class="text-center">
            <span class="badge bg-label-{{ $item->metode === 'qr' ? 'primary' : 'secondary' }} px-2 py-1">
              {{ strtoupper($item->metode) }}
            </span>
          </td>
          @if(!$isWaliKelas)
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.absensi-siswa.edit', $item) }}" class="absensi-action-btn text-warning"
                 title="Edit" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <button type="button" class="absensi-action-btn text-danger" title="Hapus" data-bs-toggle="tooltip"
                onclick="confirmDelete(
                  '{{ route('admin.absensi-siswa.destroy', $item) }}',
                  '{{ addslashes($item->siswa->nama_lengkap ?? '-') }}',
                  '{{ $item->tanggal->format('d M Y') }}'
                )">
                <i class="ti tabler-trash fs-5"></i>
              </button>
            </div>
          </td>
          @endif
        </tr>
      @empty
        <tr>
          <td colspan="{{ $isWaliKelas ? 8 : 9 }}" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data absensi tercatat.</span>
              @if(!$isWaliKelas)
              <a href="{{ route('admin.absensi-siswa.create') }}" class="btn btn-sm btn-label-info mt-1">
                <i class="ti tabler-plus me-1"></i> Tambah Kehadiran
              </a>
              @endif
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($absensi->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $absensi->appends(request()->query())->links('vendor.pagination.users') }}
  </div>
@endif
