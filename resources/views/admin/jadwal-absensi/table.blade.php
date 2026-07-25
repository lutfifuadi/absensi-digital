<div class="table-responsive" data-total="{{ $kelas->total() }}">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead
      style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Nama Kelas</th>
        <th class="py-3 text-center">Tingkat</th>
        <th class="py-3">Jurusan</th>
        <th class="py-3 text-center">Status Jadwal</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($kelas as $item)
        <tr class="kelas-row-hover">
          <td class="ps-4 text-white-50">{{ $kelas->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-xs">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-door" style="font-size:0.8rem;"></i>
                </span>
              </div>
              <span class="fw-semibold">{{ $item->nama }}</span>
            </div>
          </td>
          <td class="text-center">
            @php
              $tingkatColor = match ($item->tingkat) {
                'X' => 'primary',
                'XI' => 'warning',
                'XII' => 'danger',
                default => 'secondary',
              };
            @endphp
            <span class="badge bg-label-{{ $tingkatColor }}">{{ $item->tingkat }}</span>
          </td>
          <td class="text-white-50 small">{{ $item->jurusan?->nama ?? '—' }}</td>
          <td class="text-center">
            @php
              $jadwalCount = $item->jadwalAbsensi->count();
              $activeDays = $item->jadwalAbsensi->where('is_libur', false)->count();
            @endphp
            @if($jadwalCount > 0)
              <span class="badge bg-label-success">
                <i class="ti tabler-check me-1"></i> Aktif ({{ $activeDays }} hari)
              </span>
            @else
              <span class="badge bg-label-warning">
                <i class="ti tabler-alert-triangle me-1"></i> Belum Diatur
              </span>
            @endif
          </td>
          <td class="pe-4 text-end text-nowrap">
            <button type="button" class="action-btn bg-label-info text-info"
              onclick="openJadwalModal({{ $item->id }}, '{{ addslashes($item->nama) }}')">
              <i class="ti tabler-clock-edit"></i> Kelola Jadwal
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-clock-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data kelas.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($kelas->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $kelas->links('vendor.pagination.users') }}
  </div>
@endif
