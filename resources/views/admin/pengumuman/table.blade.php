<div class="table-responsive text-nowrap">
  <table class="table table-hover align-middle mb-0">
    <thead>
      <tr class="text-white-50" style="background: rgba(255, 255, 255, 0.03); border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <th style="width: 50px;" class="py-3 ps-4">#</th>
        <th class="py-3">Judul & Kategori</th>
        <th class="py-3">Target Penerima</th>
        <th class="py-3">Periode Tampil</th>
        <th class="py-3">Status</th>
        <th class="py-3">Pembuat</th>
        <th class="py-3 text-end pe-4" style="width: 120px;">Aksi</th>
      </tr>
    </thead>
    <tbody class="table-border-bottom-0">
      @forelse ($pengumuman as $index => $item)
        <tr class="pengumuman-row-hover {{ $item->is_pinned ? 'row-pinned' : '' }}">
          <td class="ps-4 fw-semibold text-white-50">{{ $pengumuman->firstItem() + $index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2 py-1">
              @if($item->is_pinned)
                <span class="badge bg-warning text-dark p-1" title="Disematkan di paling atas" style="border-radius: 4px;">
                  <i class="ti tabler-pin-filled fs-6"></i>
                </span>
              @endif
              <div>
                <a href="{{ route('admin.pengumuman.show', $item->id) }}" class="text-white fw-semibold text-decoration-none btn-detail-pengumuman fs-6" data-id="{{ $item->id }}">
                  {{ \Illuminate\Support\Str::limit($item->judul, 50) }}
                </a>
                <div class="mt-1 d-flex align-items-center gap-2">
                  <span class="badge-glass --{{ $item->kategori }}">{{ $item->kategori }}</span>
                  @if($item->is_popup)
                    <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 extra-small" title="Pengumuman ini tampil sebagai Popup Modal" style="border-radius: 4px;">
                      <i class="ti tabler-window-maximize me-1"></i> POPUP
                    </span>
                  @endif
                  @if($item->reads_count > 0)
                    <span class="text-white-50 extra-small" title="Jumlah pengguna yang sudah menandai dibaca">
                      <i class="ti tabler-eye me-1 text-info"></i> {{ $item->reads_count }} Dibaca
                    </span>
                  @endif
                  @if($item->lampiran)
                    <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="text-info extra-small text-decoration-none d-inline-flex align-items-center gap-1" title="Unduh Lampiran">
                      <i class="ti tabler-paperclip"></i> Lampiran
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </td>
          <td>
            @php
              $targetLabels = [
                'semua'     => 'Semua Pengguna',
                'guru'      => 'Khusus Guru',
                'siswa'     => 'Khusus Siswa',
                'orang_tua' => 'Khusus Wali/Ortu',
                'staff'     => 'Khusus Staff',
                'kelas'     => 'Kelas: ' . ($item->targetKelas ? $item->targetKelas->nama : '-'),
              ];
              $targetText = $targetLabels[$item->target] ?? ucfirst($item->target);
            @endphp
            <span class="badge-target --{{ $item->target }}">
              <i class="ti tabler-target"></i> {{ $targetText }}
            </span>
          </td>
          <td>
            <div class="small text-white-50">
              @if($item->tanggal_mulai || $item->tanggal_selesai)
                <div><i class="ti tabler-calendar me-1 text-info"></i> {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y H:i') : 'Awal' }}</div>
                <div><i class="ti tabler-arrow-narrow-right me-1 text-white-50"></i> {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y H:i') : 'Selamanya' }}</div>
              @else
                <span class="text-white-50"><i class="ti tabler-infinity me-1"></i> Selamanya</span>
              @endif
            </div>
          </td>
          <td>
            @if($item->is_aktif)
              <span class="badge-status --aktif">
                <i class="ti tabler-circle-check fs-6 me-1"></i> Aktif
              </span>
            @else
              <span class="badge-status --nonaktif">
                <i class="ti tabler-circle-minus fs-6 me-1"></i> Nonaktif
              </span>
            @endif
          </td>
          <td>
            <span class="small text-white-50">{{ $item->creator ? $item->creator->name : 'Sistem' }}</span>
          </td>
          <td class="text-end pe-4">
            <div class="d-inline-flex gap-1">
              <button type="button" 
                      class="action-btn btn-toggle-pin {{ $item->is_pinned ? 'text-warning' : 'text-white-50' }}" 
                      data-id="{{ $item->id }}" 
                      title="{{ $item->is_pinned ? 'Lepas Sematan' : 'Sematkan di Atas' }}">
                <i class="ti tabler-pin"></i>
              </button>
              <button type="button" 
                      class="action-btn text-info btn-edit-pengumuman" 
                      data-data="{{ json_encode($item) }}" 
                      title="Edit Pengumuman">
                <i class="ti tabler-edit"></i>
              </button>
              <button type="button" 
                      class="action-btn text-danger btn-delete-pengumuman" 
                      data-url="{{ route('admin.pengumuman.destroy', $item->id) }}" 
                      data-judul="{{ $item->judul }}" 
                      title="Hapus Pengumuman">
                <i class="ti tabler-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="7" class="text-center py-5">
            <div class="d-flex flex-column align-items-center">
              <i class="ti tabler-speakerphone fs-1 text-white-50 mb-2"></i>
              <h6 class="text-white mb-1">Belum Ada Pengumuman</h6>
              <p class="text-white-50 small mb-0">Klik tombol "Tambah Pengumuman" di atas untuk membuat pengumuman baru.</p>
            </div>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if ($pengumuman->hasPages())
  <div class="card-footer d-flex align-items-center justify-content-between border-top border-secondary py-3 px-4">
    <div class="small text-white-50">
      Menampilkan {{ $pengumuman->firstItem() }} - {{ $pengumuman->lastItem() }} dari {{ $pengumuman->total() }} pengumuman
    </div>
    <div>
      {{ $pengumuman->links('pagination::bootstrap-5') }}
    </div>
  </div>
@endif
