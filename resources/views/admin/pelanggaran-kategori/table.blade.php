<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width: 5%">#</th>
        <th class="py-3" style="width: 10%">Warna</th>
        <th class="py-3" style="width: 30%">Nama Kategori</th>
        <th class="py-3" style="width: 30%">Deskripsi</th>
        <th class="py-3 text-center" style="width: 10%">Urutan</th>
        <th class="py-3 text-center" style="width: 10%">Status</th>
        <th class="py-3 pe-4 text-end" style="width: 5%">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($categories as $index => $item)
        <tr class="kategori-row-hover">
          <td class="ps-4 text-white-50 small">{{ $categories->firstItem() + $index }}</td>
          <td>
            <span class="color-preview" style="background-color: {{ $item->warna ?? '#ef4444' }};"></span>
            <code class="ms-1 text-white-50 extra-small">{{ $item->warna ?? '#ef4444' }}</code>
          </td>
          <td>
            <span class="text-white fw-semibold">{{ $item->nama }}</span>
            <span class="badge bg-label-info ms-2" title="Jumlah Jenis Pelanggaran" data-bs-toggle="tooltip">{{ $item->jenis_pelanggaran_count }} jenis</span>
          </td>
          <td class="text-white-50 small text-truncate" style="max-width: 250px;">{{ $item->deskripsi ?? '-' }}</td>
          <td class="text-center text-white-50">{{ $item->urutan }}</td>
          <td class="text-center">
            @if ($item->is_aktif)
              <span class="badge bg-label-success">Aktif</span>
            @else
              <span class="badge bg-label-danger">Nonaktif</span>
            @endif
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <button type="button" 
                class="action-btn text-warning" 
                title="Ubah" 
                data-bs-toggle="tooltip" 
                onclick="openEditModal({
                  id: {{ $item->id }},
                  nama: '{{ addslashes($item->nama) }}',
                  deskripsi: '{{ addslashes($item->deskripsi ?? '') }}',
                  warna: '{{ $item->warna }}',
                  urutan: {{ $item->urutan }},
                  is_aktif: {{ $item->is_aktif ? 'true' : 'false' }}
                })">
                <i class="ti tabler-pencil fs-5"></i>
              </button>
              <button type="button" 
                class="action-btn text-danger btn-delete-kategori" 
                title="Hapus" 
                data-bs-toggle="tooltip" 
                data-url="{{ route('admin.pelanggaran-kategori.destroy', $item->id) }}" 
                data-nama="{{ $item->nama }}">
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
              <span class="small">Tidak ada data kategori pelanggaran.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($categories->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
    {{ $categories->links('vendor.pagination.users') }}
  </div>
@endif
<input type="hidden" id="hidden-total-count" value="{{ $categories->total() }}">
