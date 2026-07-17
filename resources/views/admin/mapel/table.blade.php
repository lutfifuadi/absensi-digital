<div class="table-responsive">
  <table class="table table-hover align-middle mb-0" style="color:inherit;">
    <thead style="background:rgba(255,255,255,0.04);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.8px;opacity:0.7;">
      <tr>
        <th class="ps-4 py-3" style="width: 5%">#</th>
        <th class="py-3" style="width: 25%">Kode Mapel</th>
        <th class="py-3" style="width: 40%">Nama Mata Pelajaran</th>
        <th class="py-3" style="width: 15%">Kelompok</th>
        <th class="py-3 text-center" style="width: 10%">Status</th>
        <th class="py-3 pe-4 text-end" style="width: 5%">Aksi</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($mapels as $index => $mapel)
        <tr class="mapel-row-hover">
          <td class="ps-4 text-white-50 small">{{ $mapels->firstItem() + $index }}</td>
          <td>
            <span class="badge bg-label-info fw-bold">{{ $mapel->kode_mapel }}</span>
          </td>
          <td class="text-white fw-semibold">{{ $mapel->nama_mapel }}</td>
          <td>
            @if ($mapel->kelompok === 'umum')
              <span class="badge bg-label-primary">Umum</span>
            @elseif ($mapel->kelompok === 'kejuruan')
              <span class="badge bg-label-warning">Kejuruan</span>
            @else
              <span class="badge bg-label-success">Muatan Lokal</span>
            @endif
          </td>
          <td class="text-center">
            @if ($mapel->status)
              <span class="badge bg-label-success">Aktif</span>
            @else
              <span class="badge bg-label-danger">Nonaktif</span>
            @endif
          </td>
          <td class="pe-4 text-end">
            <div class="d-flex justify-content-end gap-1">
              <a href="{{ route('admin.mapel.edit', $mapel->id) }}" class="action-btn text-warning" title="Ubah" data-bs-toggle="tooltip">
                <i class="ti tabler-pencil fs-5"></i>
              </a>
              <button type="button" 
                class="action-btn text-danger btn-delete-mapel" 
                title="Hapus" 
                data-bs-toggle="tooltip" 
                data-url="{{ route('admin.mapel.destroy', $mapel->id) }}" 
                data-nama="{{ $mapel->nama_mapel }}">
                <i class="ti tabler-trash fs-5"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="text-center py-5">
            <div class="d-flex flex-column align-items-center gap-2 opacity-50">
              <i class="ti tabler-info-circle" style="font-size:2.5rem;"></i>
              <span class="small">Tidak ada data mata pelajaran.</span>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($mapels->hasPages())
  <div class="px-4 py-3 border-top" style="border-color: rgba(255, 255, 255, 0.08) !important;">
    {{ $mapels->links('vendor.pagination.users') }}
  </div>
@endif
<input type="hidden" id="hidden-total-count" value="{{ $mapels->total() }}">
