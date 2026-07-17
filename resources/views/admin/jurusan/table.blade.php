<div class="table-responsive" data-total="{{ method_exists($jurusan, 'total') ? $jurusan->total() : count($jurusan) }}">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead
      style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width:46px;">#</th>
        <th class="py-3">Kode Jurusan</th>
        <th class="py-3">Nama Jurusan</th>
        <th class="py-3 text-center">Jumlah Kelas</th>
        <th class="py-3 pe-4 text-end">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse($jurusan as $item)
        <tr class="jurusan-row-hover">
          <td class="ps-4 text-white-50">{{ $jurusan->firstItem() + $loop->index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div class="avatar avatar-xs">
                <span class="avatar-initial rounded bg-label-info">
                  <i class="ti tabler-books" style="font-size:0.8rem;"></i>
                </span>
              </div>
              <div>
                <span class="fw-semibold text-white">{{ $item->kode }}</span>
              </div>
            </div>
          </td>
          <td>
            <span class="text-white-75">{{ $item->nama }}</span>
          </td>
          <td class="text-center">
            <span class="badge bg-label-primary px-2">{{ $item->kelas()->count() }}</span>
          </td>
          <td class="pe-4 text-end">
            <button type="button" class="action-btn bg-label-warning text-warning me-1"
              onclick="openEditJurusan({
                id: {{ $item->id }},
                kode: '{{ addslashes($item->kode) }}',
                nama: '{{ addslashes($item->nama) }}'
              })">
              <i class="ti tabler-pencil"></i> Ubah
            </button>
            <button type="button" class="action-btn bg-label-danger text-danger"
              onclick="openHapusJurusan({{ $item->id }}, '{{ addslashes($item->nama) }}')">
              <i class="ti tabler-trash"></i> Hapus
            </button>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-books-off" style="font-size:2.5rem;"></i>
              <span class="small">Belum ada data jurusan.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($jurusan->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: var(--das-border) !important;">
    {{ $jurusan->links('vendor.pagination.users') }}
  </div>
@endif
