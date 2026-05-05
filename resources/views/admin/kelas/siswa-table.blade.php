<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead
      style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">NISN</th>
        <th class="py-3">Nama Lengkap</th>
        <th class="py-3 text-center">Status</th>
        <th class="py-3 text-center">Pindah Kelas</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($siswa as $s)
        @php
          $statusColor = match ($s->status) {
              'aktif'    => 'success',
              'alumni'   => 'warning',
              default    => 'secondary',
          };
          $avatarColor = ['primary', 'success', 'info', 'warning', 'danger'][($loop->index) % 5];
          $rowNum      = ($siswa->firstItem() ?? 1) + $loop->index;
        @endphp
        <tr class="siswa-row-hover">
          <td class="ps-4 text-white-50">{{ $rowNum }}</td>
          <td>
            <span class="small text-white-75 font-monospace">{{ $s->nisn ?? $s->nis ?? '—' }}</span>
          </td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-xs">
                <span class="avatar-initial rounded-circle bg-label-{{ $avatarColor }}"
                  style="font-size:0.65rem;">
                  {{ strtoupper(substr($s->nama_lengkap, 0, 1)) }}
                </span>
              </div>
              <span class="fw-semibold">{{ $s->nama_lengkap }}</span>
            </div>
          </td>
          <td class="text-center">
            <span class="badge bg-label-{{ $statusColor }}">{{ ucfirst($s->status ?? 'aktif') }}</span>
          </td>
          <td class="text-center">
            <button type="button" class="action-btn bg-label-warning text-warning btn-pindah-siswa"
              data-bs-toggle="modal"
              data-bs-target="#modalPindahSiswa"
              data-id="{{ $s->id }}"
              data-name="{{ $s->nama_lengkap }}">
              <i class="ti tabler-arrows-left-right"></i> Pindah
            </button>
          </td>
          <td class="pe-4 text-end">
            <button type="button" class="action-btn bg-label-danger text-danger"
              onclick="openHapusSiswa({{ $s->id }}, {!! json_encode($s->nama_lengkap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!})">
              <i class="ti tabler-user-minus"></i> Hapus dari Kelas
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-off" style="font-size:2.5rem;"></i>
              <span class="small">Tidak ada siswa yang cocok dengan pencarian.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($siswa->hasPages())
  <div class="px-4 py-3 border-top" style="border-color:rgba(255,255,255,0.08) !important;">
    {{ $siswa->links('vendor.pagination.users') }}
  </div>
@endif
