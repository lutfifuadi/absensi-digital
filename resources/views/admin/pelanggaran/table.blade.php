<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Siswa</th>
        <th class="py-3 text-center">Kelas</th>
        <th class="py-3">Pelanggaran / Kategori</th>
        <th class="py-3 text-center">Tanggal Kejadian</th>
        <th class="py-3 text-center">Poin</th>
        <th class="py-3">Pencatat</th>
        <th class="py-3 pe-4 text-end" style="width: 120px;">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($pelanggarans as $index => $p)
        <tr class="pelanggaran-row-hover">
          <td class="ps-4 text-white-50 small">
            {{ ($pelanggarans->currentPage() - 1) * $pelanggarans->perPage() + $index + 1 }}
          </td>
          <td>
            <div class="d-flex align-items-center gap-3">
              <div class="avatar avatar-md">
                @if($p->siswa->foto && file_exists(public_path('storage/foto-siswa/' . $p->siswa->foto)))
                  <img src="{{ asset('storage/foto-siswa/' . $p->siswa->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width:100%; height:100%;">
                @else
                  <span class="avatar-initial rounded-circle bg-label-{{ $p->siswa->jenis_kelamin === 'L' ? 'info' : 'danger' }}" style="font-size:0.85rem;">
                    {{ strtoupper(substr($p->siswa->nama_lengkap, 0, 1)) }}{{ strtoupper(substr(strrchr($p->siswa->nama_lengkap, ' ') ?: $p->siswa->nama_lengkap, 1, 1)) }}
                  </span>
                @endif
              </div>
              <div>
                <span class="fw-bold mb-0" style="font-size:0.9rem; color:#fff;">{{ $p->siswa->nama_lengkap }}</span>
                <span class="text-white-50 small d-block" style="font-size:0.72rem;">NIS: {{ $p->siswa->nis }}</span>
              </div>
            </div>
          </td>
          <td class="text-center">
            <span class="badge bg-label-info px-2 py-1">{{ $p->siswa->kelas?->nama ?: 'Tidak Ada Kelas' }}</span>
          </td>
          <td>
            <div>
              <span class="fw-medium text-white d-block text-wrap" style="max-width: 250px; font-size:0.9rem;">{{ $p->jenisPelanggaran?->nama }}</span>
              <span class="badge bg-label-primary mt-1" style="font-size:0.7rem;">{{ $p->jenisPelanggaran?->kategori?->nama }}</span>
            </div>
          </td>
          <td class="text-center text-white-50 small">
            {{ $p->tanggal_kejadian->format('d-m-Y') }}
          </td>
          <td class="text-center">
            <span class="badge bg-label-danger px-2 py-1 fw-bold">+{{ $p->poin_saat_itu }}</span>
          </td>
          <td>
            <span class="small text-white-50">{{ $p->pencatat?->name ?: 'System' }}</span>
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <!-- Detail Pelanggaran -->
              <a href="{{ route('admin.pelanggaran.show', $p->id) }}" 
                 class="action-btn text-info" 
                 data-bs-toggle="tooltip" 
                 data-bs-placement="top" 
                 title="Detail Pelanggaran">
                <i class="ti tabler-eye fs-5"></i>
              </a>

              <!-- Edit Pelanggaran -->
              @can('update', $p)
                <a href="{{ route('admin.pelanggaran.edit', $p->id) }}" 
                   class="action-btn text-warning" 
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   title="Edit Pelanggaran">
                  <i class="ti tabler-pencil fs-5"></i>
                </a>
              @endcan

              <!-- Delete Pelanggaran -->
              @can('delete', $p)
                <button type="button" 
                        onclick="confirmDelete('{{ route('admin.pelanggaran.destroy', $p->id) }}')" 
                        class="action-btn text-danger" 
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        title="Hapus Pelanggaran">
                  <i class="ti tabler-trash fs-5"></i>
                </button>
              @endcan
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-alert-triangle text-warning" style="font-size:2.5rem;"></i>
              <span class="small">Tidak ada riwayat catatan pelanggaran siswa yang ditemukan.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($pelanggarans->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: rgba(255,255,255,0.08) !important;">
    {{ $pelanggarans->links('vendor.pagination.users') }}
  </div>
@endif
