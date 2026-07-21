<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead
      style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Staff</th>
        <th class="py-3 text-center">Tanggal</th>
        <th class="py-3 text-center">Status</th>
        <th class="py-3 text-center d-none d-md-table-cell">Metode</th>
        <th class="py-3 text-center d-none d-lg-table-cell">Keterangan</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($absensi as $item)
        <tr class="staff-row-hover">
          <td class="ps-4 text-white-50 small">{{ ($absensi->currentPage() - 1) * $absensi->perPage() + $loop->iteration }}</td>
          <td>
            <div class="fw-medium">{{ $item->staff->nama_lengkap ?? '-' }}</div>
            @if(!empty($item->staff->nip))
              <div class="text-white-50 extra-small" style="font-size:0.7rem;">NIP: {{ $item->staff->nip }}</div>
            @endif
          </td>
          <td class="text-center">{{ $item->tanggal->format('d M Y') }}</td>
          <td class="text-center">
            <span
              class="badge bg-label-{{ match ($item->status) {
                  'hadir' => 'success',
                  'sakit' => 'info',
                  'izin' => 'warning',
                  'alpha' => 'danger',
                  'terlambat' => 'secondary',
                  default => 'dark',
              } }} text-capitalize px-2">{{ ucfirst($item->status) }}</span>
          </td>
          <td class="text-center d-none d-md-table-cell">
            <span class="badge bg-label-{{ $item->metode === 'qr' ? 'primary' : 'secondary' }} px-2 py-1">
              {{ strtoupper($item->metode) }}
            </span>
          </td>
          <td class="text-center d-none d-lg-table-cell">{{ $item->keterangan ?: '–' }}</td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.absensi-staff.edit', $item) }}" class="staff-action-btn text-warning"
                title="Edit" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <form action="{{ route('admin.absensi-staff.destroy', $item) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Yakin ingin menghapus absensi staff ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="staff-action-btn text-danger" title="Hapus" data-bs-toggle="tooltip">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-calendar-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data absensi staff.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($absensi->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
    {{ $absensi->links('vendor.pagination.users') }}
  </div>
@endif