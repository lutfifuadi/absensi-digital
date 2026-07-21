<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width: 5%">#</th>
        <th class="py-3" style="width: 20%">Kategori</th>
        <th class="py-3" style="width: 35%">Nama Pelanggaran</th>
        <th class="py-3" style="width: 15%">Deskripsi</th>
        <th class="py-3 text-center" style="width: 10%">Bobot Poin</th>
        <th class="py-3 text-center" style="width: 10%">Status</th>
        <th class="py-3 pe-4 text-end" style="width: 5%">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($jenisPelanggarans as $index => $item)
        <tr class="jenis-row-hover">
          <td class="ps-4 text-white-50 small">{{ $jenisPelanggarans->firstItem() + $index }}</td>
          <td>
            <span class="badge text-white" style="background-color: {{ $item->kategori->warna ?? '#7367f0' }};">
              {{ $item->kategori->nama ?? 'Umum' }}
            </span>
          </td>
          <td>
            <span class="text-white fw-semibold">{{ $item->nama }}</span>
            @if($item->pelanggaran_siswa_count > 0)
              <span class="badge bg-label-info ms-2" title="Jumlah Siswa yang Melanggar" data-bs-toggle="tooltip">
                {{ $item->pelanggaran_siswa_count }}x dilakukan
              </span>
            @endif
          </td>
          <td class="text-white-50 small text-truncate" style="max-width: 200px;">{{ $item->deskripsi ?? '-' }}</td>
          <td class="text-center">
            <span class="badge bg-label-danger fw-bold fs-6">{{ $item->bobot_poin }}</span>
          </td>
          <td class="text-center">
            @if ($item->is_aktif)
              <span class="badge bg-label-success">Aktif</span>
            @else
              <span class="badge bg-label-danger">Nonaktif</span>
            @endif
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.pelanggaran-jenis.edit', $item->id) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <button type="button" 
                class="action-btn text-danger btn-delete-jenis" 
                title="Hapus" 
                data-bs-toggle="tooltip" 
                data-url="{{ route('admin.pelanggaran-jenis.destroy', $item->id) }}" 
                data-nama="{{ $item->nama }}"
                data-count="{{ $item->pelanggaran_siswa_count }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-info-circle" style="font-size:2.5rem;"></i>
              <span class="small">Tidak ada data jenis pelanggaran.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($jenisPelanggarans->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $jenisPelanggarans->links('vendor.pagination.users') }}
  </div>
@endif
<input type="hidden" id="hidden-total-count" value="{{ $jenisPelanggarans->total() }}">
