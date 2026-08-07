@php
  $user = auth()->user();
  $pengumumanList = \App\Models\Pengumuman::with(['targetKelas'])
    ->aktif()
    ->targetForUser($user)
    ->orderBy('is_pinned', 'desc')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();
@endphp

@if($pengumumanList->count() > 0)
  <div class="card bg-dark text-white border border-secondary mb-4 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header border-bottom border-secondary d-flex align-items-center justify-content-between py-3 px-4" style="background: rgba(115, 103, 240, 0.08);">
      <div class="d-flex align-items-center gap-2">
        <i class="ti tabler-speakerphone fs-4 text-warning"></i>
        <h5 class="card-title text-white mb-0 fs-6 fw-bold">Pengumuman & Informasi Terbaru</h5>
      </div>
      <span class="badge bg-warning text-dark">{{ $pengumumanList->count() }} Pengumuman</span>
    </div>
    <div class="card-body p-0">
      <div class="list-group list-group-flush">
        @foreach($pengumumanList as $item)
          <div class="list-group-item bg-transparent text-white border-bottom border-secondary p-3">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
              <div class="d-flex align-items-center gap-2">
                @if($item->is_pinned)
                  <span class="badge bg-warning text-dark p-1" title="Disematkan"><i class="ti tabler-pin-filled fs-6"></i></span>
                @endif
                <h6 class="text-white mb-0 fw-semibold fs-6">{{ $item->judul }}</h6>
              </div>
              @php
                $kategoriBadges = [
                  'informasi' => 'bg-info',
                  'penting'   => 'bg-warning text-dark',
                  'kegiatan'  => 'bg-primary',
                  'mendesak'  => 'bg-danger',
                  'libur'     => 'bg-success',
                ];
              @endphp
              <span class="badge {{ $kategoriBadges[$item->kategori] ?? 'bg-secondary' }} text-uppercase extra-small" style="font-size: 0.65rem;">{{ $item->kategori }}</span>
            </div>

            <p class="text-white-50 small mb-2" style="line-height: 1.5; white-space: pre-line;">
              {{ Str::limit($item->konten, 150) }}
            </p>

            <div class="d-flex align-items-center justify-content-between text-white-50" style="font-size: 0.75rem;">
              <span><i class="ti tabler-clock me-1"></i> {{ $item->created_at ? $item->created_at->diffForHumans() : '' }}</span>
              @if($item->lampiran)
                <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="text-info text-decoration-none">
                  <i class="ti tabler-paperclip me-1"></i> Unduh Lampiran
                </a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>
@endif
