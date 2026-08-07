@php
  $user = auth()->user();
  $unreadPopup = $user ? \App\Models\Pengumuman::with(['targetKelas', 'creator'])->unreadPopupForUser($user)->first() : null;
@endphp

@if($unreadPopup)
  @php
    $kategoriBadges = [
      'informasi' => ['class' => 'bg-info', 'glow' => 'rgba(0, 207, 234, 0.2)'],
      'penting'   => ['class' => 'bg-warning text-dark', 'glow' => 'rgba(255, 159, 67, 0.2)'],
      'kegiatan'  => ['class' => 'bg-primary', 'glow' => 'rgba(115, 103, 240, 0.2)'],
      'mendesak'  => ['class' => 'bg-danger', 'glow' => 'rgba(234, 84, 85, 0.25)'],
      'libur'     => ['class' => 'bg-success', 'glow' => 'rgba(40, 199, 111, 0.2)'],
    ];
    $badgeConfig = $kategoriBadges[$unreadPopup->kategori] ?? ['class' => 'bg-secondary', 'glow' => 'rgba(255, 255, 255, 0.1)'];
  @endphp

  <div class="modal fade" 
       id="modalPopupPengumuman" 
       tabindex="-1" 
       aria-hidden="true"
       @if($unreadPopup->force_read) data-bs-backdrop="static" data-bs-keyboard="false" @endif>
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content das-modal border-0 shadow-lg" style="box-shadow: 0 0 40px {{ $badgeConfig['glow'] }} !important;">
        
        {{-- Modal Header --}}
        <div class="das-modal-head d-flex align-items-center justify-content-between py-3 px-4">
          <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $badgeConfig['class'] }} text-uppercase" style="border-radius: 4px; font-size: 0.72rem;">
              {{ $unreadPopup->kategori }}
            </span>
            <span class="text-white-50 extra-small ms-1">
              <i class="ti tabler-speakerphone text-warning me-1"></i> Pengumuman Resmi Sekolah
            </span>
          </div>
          
          @if(!$unreadPopup->force_read)
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          @endif
        </div>

        {{-- Modal Body --}}
        <div class="das-modal-body text-white p-4">
          <h4 class="text-white fw-bold mb-3" style="line-height: 1.3;">
            {{ $unreadPopup->judul }}
          </h4>

          <div class="d-flex align-items-center gap-3 extra-small text-white-50 mb-3 pb-3 border-bottom border-secondary">
            <span><i class="ti tabler-calendar me-1 text-info"></i> {{ $unreadPopup->created_at ? $unreadPopup->created_at->translatedFormat('d F Y H:i') : '' }}</span>
            <span><i class="ti tabler-user me-1 text-warning"></i> Oleh: {{ $unreadPopup->creator ? $unreadPopup->creator->name : 'Sistem' }}</span>
          </div>

          <div class="p-3 rounded bg-white bg-opacity-10 text-white mb-3" style="white-space: pre-line; line-height: 1.6; max-height: 350px; overflow-y: auto;">
            {{ $unreadPopup->konten }}
          </div>

          @if($unreadPopup->lampiran)
            <div class="d-flex align-items-center justify-content-between p-3 rounded mb-2" style="background: rgba(0, 207, 234, 0.1); border: 1px solid rgba(0, 207, 234, 0.25);">
              <div class="d-flex align-items-center gap-2">
                <i class="ti tabler-paperclip fs-5 text-info"></i>
                <span class="small text-white font-monospace">{{ basename($unreadPopup->lampiran) }}</span>
              </div>
              <a href="{{ asset('storage/' . $unreadPopup->lampiran) }}" target="_blank" class="btn btn-sm btn-info text-white">
                <i class="ti tabler-download me-1"></i> Unduh Lampiran
              </a>
            </div>
          @endif
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer border-top border-secondary p-3 d-flex align-items-center justify-content-between">
          <span class="extra-small text-white-50">
            @if($unreadPopup->force_read)
              <i class="ti tabler-info-circle text-warning me-1"></i> Wajib dikonfirmasi sebelum melanjutkan.
            @else
              Klik tombol konfirmasi untuk menutup popup.
            @endif
          </span>
          <button type="button" class="btn btn-success fw-bold px-4" id="btnMarkAsReadPengumuman" style="border-radius: 4px;">
            <i class="ti tabler-circle-check me-1"></i> Saya Sudah Membaca & Mengerti
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modalEl = document.getElementById('modalPopupPengumuman');
      if (modalEl) {
        const popupModal = new bootstrap.Modal(modalEl);
        popupModal.show();

        const btnMark = document.getElementById('btnMarkAsReadPengumuman');
        if (btnMark) {
          btnMark.addEventListener('click', function () {
            btnMark.disabled = true;
            btnMark.innerHTML = '<i class="ti tabler-loader spinner me-1"></i> Menyimpan...';

            fetch("{{ route('pengumuman.mark-as-read', $unreadPopup->id) }}", {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: '_token=' + encodeURIComponent('{{ csrf_token() }}')
            })
            .then(res => res.json())
            .then(res => {
              btnMark.disabled = false;
              if (res.success) {
                popupModal.hide();
              }
            })
            .catch(err => {
              btnMark.disabled = false;
              console.error('Mark read error:', err);
              popupModal.hide();
            });
          });
        }
      }
    });
  </script>
@endif
