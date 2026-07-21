<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Informasi Siswa</th>
        <th class="py-3 text-center">Kelas</th>
        <th class="py-3 text-center">Total Poin</th>
        <th class="py-3 text-center">Level SP</th>
        <th class="py-3 text-center">Jumlah Pelanggaran</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($siswa as $item)
        <tr class="siswa-row-hover">
          <td class="ps-4 text-white-50 small">{{ $siswa->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                {{-- Performance Note: Avoid rendering student photos, use initial/generic icon --}}
                <span class="avatar-initial rounded-circle bg-label-{{ $item->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size:0.85rem;">
                  {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($item->nama_lengkap, ' ') ?: $item->nama_lengkap, 1, 1)) }}
                </span>
              </div>
              <div>
                <div class="fw-bold mb-0" style="font-size:0.9rem;">{{ $item->nama_lengkap }}</div>
                <div class="text-white-50 small" style="font-size:0.72rem;">NIS: {{ $item->nis }}</div>
              </div>
            </div>
          </td>
          <td class="text-center">
            <span class="badge bg-label-info px-2 py-1">{{ optional($item->kelas)->nama ?? '-' }}</span>
          </td>
          <td class="text-center fw-bold text-warning">
            {{ (int) $item->pelanggaran_siswa_sum_poin_saat_itu }}
          </td>
          <td class="text-center">
            @php
              $spTerbaru = $item->pelanggaranSp->first();
              $levelSp = $spTerbaru ? $spTerbaru->level_sp : null;
              $spColor = match ($levelSp) {
                  'SP1' => 'warning',
                  'SP2' => 'danger',
                  'SP3' => 'dark',
                  default => 'secondary',
              };
            @endphp
            @if ($levelSp)
              <span class="badge bg-label-{{ $spColor }} px-2 py-1">{{ $levelSp }}</span>
            @else
              <span class="text-white-50">-</span>
            @endif
          </td>
          <td class="text-center">
            {{ $item->pelanggaranSiswa->count() }}
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.pelanggaran-siswa.profil-siswa', $item) }}" class="action-btn text-info" title="Lihat Profil Pelanggaran" data-bs-toggle="tooltip">
                <i class="ti tabler-eye fs-5"></i>
              </a>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-users-minus" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data rekap pelanggaran.</span>
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
