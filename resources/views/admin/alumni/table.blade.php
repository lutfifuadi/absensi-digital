<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Informasi Alumni</th>
        <th class="py-3 d-none d-md-table-cell text-center">NIS / NISN</th>
        <th class="py-3 text-center">Kelas Terakhir</th>
        <th class="py-3 d-none d-xl-table-cell text-center">Tahun Lulus</th>
        <th class="py-3 text-center">Status</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($siswa as $item)
        <tr class="siswa-row-hover">
          <td class="ps-4 text-white-50 small">
            {{ method_exists($siswa, 'firstItem') ? ($siswa->firstItem() + $loop->index) : ($loop->index + 1) }}
          </td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                <span class="avatar-initial rounded-circle bg-label-{{ $item->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size:0.85rem;">
                  {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($item->nama_lengkap, ' ') ?: $item->nama_lengkap, 1, 1)) }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $item->nama_lengkap }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">
                  {{ $item->no_hp ?: ($item->no_hp_ortu ?: 'No HP -') }}
                </div>
              </div>
            </div>
          </td>
          <td class="d-none d-md-table-cell text-center">
            <div class="small fw-medium">{{ $item->nis ?? '-' }}</div>
            <div class="text-white-50" style="font-size:0.65rem;">{{ $item->nisn ?? '-' }}</div>
          </td>
          <td class="text-center">
            @php
              $riwayatAlumni = $item->riwayatKenaikanKelas->where('status_akhir', 'alumni')->first();
              $kelasTerakhir = $riwayatAlumni->kelasAsal->nama ?? '-';
            @endphp
            <span class="badge bg-label-info px-2 py-1">{{ $kelasTerakhir }}</span>
          </td>
          <td class="d-none d-xl-table-cell text-center">
            @php
              $tahunLulusNama = $riwayatAlumni->tahunAkademikAsal->nama ?? '-';
              $tahunLulusSemester = isset($riwayatAlumni->tahunAkademikAsal->semester) ? ucfirst($riwayatAlumni->tahunAkademikAsal->semester) : '';
            @endphp
            <div class="small text-white-50">{{ $tahunLulusNama }} {{ $tahunLulusSemester }}</div>
          </td>
          <td class="text-center">
            <span class="badge bg-label-warning text-capitalize px-2">Alumni</span>
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.alumni.show', $item) }}" class="action-btn text-info" title="Lihat Profil" data-bs-toggle="tooltip">
                <i class="ti tabler-eye fs-5"></i>
              </a>
              <button type="button"
                class="action-btn text-danger btn-hapus-alumni"
                title="Hapus Permanen"
                data-bs-toggle="tooltip"
                data-url="{{ route('admin.alumni.destroy', $item) }}"
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
              <span class="small">Belum ada data alumni.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if (method_exists($siswa, 'hasPages') && $siswa->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $siswa->links('vendor.pagination.users') }}
  </div>
@endif
