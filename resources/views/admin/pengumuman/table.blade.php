<div class="table-responsive text-nowrap">
  <table class="table table-hover align-middle">
    <thead>
      <tr class="text-white-50">
        <th style="width: 50px;">#</th>
        <th>Judul & Kategori</th>
        <th>Target Penerima</th>
        <th>Periode Tampil</th>
        <th>Status & Pin</th>
        <th>Pembuat</th>
        <th class="text-end" style="width: 120px;">Aksi</th>
      </tr>
    </thead>
    <tbody class="table-border-bottom-0">
      @forelse ($pengumuman as $index => $item)
        <tr class="pengumuman-row-hover {{ $item->is_pinned ? 'bg-primary-subtle bg-opacity-10' : '' }}">
          <td>{{ $pengumuman->firstItem() + $index }}</td>
          <td>
            <div class="d-flex align-items-center gap-2">
              @if($item->is_pinned)
                <span class="badge bg-warning text-dark" title="Disematkan di paling atas">
                  <i class="ti tabler-pin-filled fs-6"></i>
                </span>
              @endif
              <div>
                <a href="{{ route('admin.pengumuman.show', $item->id) }}" class="text-white fw-semibold text-decoration-none btn-detail-pengumuman" data-id="{{ $item->id }}">
                  {{ \Illuminate\Support\Str::limit($item->judul, 45) }}
                </a>
                <div class="mt-1 d-flex align-items-center gap-2">
                  @php
                    $kategoriBadges = [
                      'informasi' => 'bg-info',
                      'penting'   => 'bg-warning text-dark',
                      'kegiatan'  => 'bg-primary',
                      'mendesak'  => 'bg-danger',
                      'libur'     => 'bg-success',
                    ];
                    $badgeClass = $kategoriBadges[$item->kategori] ?? 'bg-secondary';
                  @endphp
                  <span class="badge {{ $badgeClass }} text-uppercase extra-small">{{ $item->kategori }}</span>
                  @if($item->lampiran)
                    <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="text-info extra-small text-decoration-none" title="Unduh Lampiran">
                      <i class="ti tabler-paperclip me-1"></i> Lampiran
                    </a>
                  @endif
                </div>
              </div>
            </div>
          </td>
          <td>
            @php
              $targetLabels = [
                'semua'     => ['label' => 'Semua Pengguna', 'class' => 'bg-label-primary'],
                'guru'      => ['label' => 'Khusus Guru', 'class' => 'bg-label-info'],
                'siswa'     => ['label' => 'Khusus Siswa', 'class' => 'bg-label-warning'],
                'orang_tua' => ['label' => 'Khusus Wali/Ortu', 'class' => 'bg-label-success'],
                'staff'     => ['label' => 'Khusus Staff', 'class' => 'bg-label-secondary'],
                'kelas'     => ['label' => 'Kelas: ' . ($item->targetKelas ? $item->targetKelas->nama : '-'), 'class' => 'bg-label-danger'],
              ];
              $targetInfo = $targetLabels[$item->target] ?? ['label' => ucfirst($item->target), 'class' => 'bg-label-dark'];
            @endphp
            <span class="badge {{ $targetInfo['class'] }}">
              <i class="ti tabler-target me-1"></i> {{ $targetInfo['label'] }}
            </span>
          </td>
          <td>
            <div class="small">
              @if($item->tanggal_mulai || $item->tanggal_selesai)
                <span class="text-white-50"><i class="ti tabler-calendar me-1"></i></span>
                {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y H:i') : 'Awal' }}
                s/d
                {{ $item->tanggal_selesai ? $item->tanggal_selesai->format('d/m/Y H:i') : 'Selamanya' }}
              @else
                <span class="text-white-50"><i class="ti tabler-infinity me-1"></i> Selamanya</span>
              @endif
            </div>
          </td>
          <td>
            <div class="d-flex flex-column gap-1">
              @if($item->is_aktif)
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-20 w-fit">Aktif</span>
              @else
                <span class="badge bg-secondary bg-opacity-20 text-white-50 border border-secondary border-opacity-20 w-fit">Nonaktif</span>
              @endif
            </div>
          </td>
          <td>
            <span class="small text-white-50">{{ $item->creator ? $item->creator->name : 'Sistem' }}</span>
          </td>
          <td class="text-end">
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
