<div class="table-responsive text-nowrap">
  <table class="table table-hover border-top table-striped-dark">
    <thead class="table-dark">
      <tr>
        <th class="text-center" style="width: 50px;">No</th>
        <th>Siswa</th>
        <th>Kelas</th>
        <th>Pelanggaran / Kategori</th>
        <th class="text-center">Tanggal Kejadian</th>
        <th class="text-center">Poin</th>
        <th>Pencatat</th>
        <th class="text-center" style="width: 120px;">Aksi</th>
      </tr>
    </thead>
    <tbody class="table-border-bottom-0 text-light">
      @forelse($pelanggarans as $index => $p)
        <tr class="pelanggaran-row-hover">
          <td class="text-center fw-semibold">
            {{ ($pelanggarans->currentPage() - 1) * $pelanggarans->perPage() + $index + 1 }}
          </td>
          <td>
            <div class="d-flex align-items-center">
              <img src="{{ $p->siswa->foto ? asset('storage/foto-siswa/' . $p->siswa->foto) : asset('assets/img/avatars/1.png') }}" 
                   alt="Avatar" 
                   class="rounded-circle me-3" 
                   width="32" 
                   height="32" 
                   style="object-fit: cover;">
              <div>
                <span class="fw-semibold text-white d-block">{{ $p->siswa->nama_lengkap }}</span>
                <span class="small text-muted">NIS: {{ $p->siswa->nis }}</span>
              </div>
            </div>
          </td>
          <td>
            <span class="badge bg-secondary">{{ $p->siswa->kelas?->nama ?: 'Tidak Ada Kelas' }}</span>
          </td>
          <td>
            <div>
              <span class="fw-medium text-white d-block text-wrap" style="max-width: 250px;">{{ $p->jenisPelanggaran?->nama }}</span>
              <span class="badge bg-label-info mt-1">{{ $p->jenisPelanggaran?->kategori?->nama }}</span>
            </div>
          </td>
          <td class="text-center">
            {{ $p->tanggal_kejadian->format('d-m-Y') }}
          </td>
          <td class="text-center">
            <span class="badge bg-danger rounded-pill fw-bold">+{{ $p->poin_saat_itu }}</span>
          </td>
          <td>
            <span class="small text-muted">{{ $p->pencatat?->name ?: 'System' }}</span>
          </td>
          <td class="text-center">
            <div class="d-flex align-items-center justify-content-center gap-2">
              <!-- Detail Pelanggaran -->
              <a href="{{ route('admin.pelanggaran.show', $p->id) }}" 
                 class="action-btn text-info" 
                 data-bs-toggle="tooltip" 
                 data-bs-placement="top" 
                 title="Detail Pelanggaran">
                <i class="ti ti-eye"></i>
              </a>

              <!-- Edit Pelanggaran -->
              @can('update', $p)
                <a href="{{ route('admin.pelanggaran.edit', $p->id) }}" 
                   class="action-btn text-warning" 
                   data-bs-toggle="tooltip" 
                   data-bs-placement="top" 
                   title="Edit Pelanggaran">
                  <i class="ti ti-edit"></i>
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
                  <i class="ti ti-trash"></i>
                </button>
              @endcan
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="8" class="text-center py-5 text-muted">
            <div class="mb-3">
              <i class="ti ti-alert-triangle fs-1 text-warning"></i>
            </div>
            Tidak ada riwayat catatan pelanggaran siswa yang ditemukan.
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($pelanggarans->hasPages())
  <div class="card-footer border-top-0 d-flex justify-content-between align-items-center py-3">
    <div class="small text-muted">
      Menampilkan {{ $pelanggarans->firstItem() }} s/d {{ $pelanggarans->lastItem() }} dari {{ $pelanggarans->total() }} data
    </div>
    <div class="pagination-container">
      {{ $pelanggarans->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endif
