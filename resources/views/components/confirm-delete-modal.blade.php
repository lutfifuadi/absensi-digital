@props([
    'id' => 'deleteConfirmModal',
    'formId' => 'genericDeleteForm',
    'title' => 'Konfirmasi Hapus',
    'message' => 'Apakah Anda yakin ingin menghapus data ini?',
    'warning' => 'Data yang dihapus tidak dapat dikembalikan. Silakan pastikan data yang dipilih sudah benar.',
    'icon' => 'ti tabler-trash'
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true" aria-labelledby="{{ $id }}Label">
  <div class="modal-dialog modal-dialog-centered modal-dialog--confirm">
    <div class="modal-content das-modal das-modal--danger">

      <form id="{{ $formId }}" action="" method="POST">
        @csrf
        @method('DELETE')

        {{-- Header --}}
        <div class="das-modal__head das-modal__head--danger">
          <h5 class="das-modal__title" id="{{ $id }}Label">
            <i class="ti tabler-alert-triangle me-2"></i><span id="{{ $id }}Title">{{ $title }}</span>
          </h5>
        </div>

        {{-- Body --}}
        <div class="das-modal__body">
          {{-- Icon Danger with Animated Pulse Ring --}}
          <div class="dev-confirm-danger-icon">
            <div class="dev-confirm-danger-icon__ring"></div>
            <i class="{{ $icon }} dev-confirm-danger-icon__symbol" id="{{ $id }}Icon"></i>
          </div>

          {{-- Message & Preview Card --}}
          <div class="dev-confirm-message">
            <p class="dev-confirm-message__main" id="{{ $id }}Message">{{ $message }}</p>
            
            {{-- Entity Preview Card --}}
            <div class="dev-confirm-item-card" id="{{ $id }}ItemCard">
              <i class="ti tabler-file-text dev-confirm-item-card__icon" id="{{ $id }}CardIcon"></i>
              <div>
                <div class="dev-confirm-item-card__name" id="{{ $id }}ItemName">—</div>
                <div class="dev-confirm-item-card__sub" id="{{ $id }}ItemDetail">—</div>
              </div>
            </div>

            <p class="dev-confirm-message__warning" id="{{ $id }}Warning">
              <i class="ti tabler-info-circle"></i>
              <span>{{ $warning }}</span>
            </p>
          </div>
        </div>

        {{-- Footer --}}
        <div class="das-modal__foot d-flex gap-2 justify-content-end">
          <button type="button" class="das-btn das-btn--ghost" data-bs-dismiss="modal">
            <i class="ti tabler-x"></i> Tidak, Batal
          </button>
          <button type="submit" class="das-btn das-btn--danger-solid" id="{{ $id }}SubmitBtn">
            <i class="ti tabler-trash"></i> Ya, Hapus
          </button>
        </div>
      </form>

    </div>
  </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.getElementById('{{ $id }}');
  if (!modalEl) return;

  modalEl.addEventListener('show.bs.modal', function (event) {
    const triggerBtn = event.relatedTarget;
    if (!triggerBtn) return;

    const actionUrl  = triggerBtn.getAttribute('data-action') || triggerBtn.getAttribute('action') || '';
    const itemName   = triggerBtn.getAttribute('data-name') || triggerBtn.getAttribute('data-device-name') || triggerBtn.getAttribute('data-title') || triggerBtn.getAttribute('data-item-name') || 'Data Terkait';
    const itemDetail = triggerBtn.getAttribute('data-detail') || triggerBtn.getAttribute('data-device-uuid') || triggerBtn.getAttribute('data-sub') || triggerBtn.getAttribute('data-subtitle') || '';
    const customMsg  = triggerBtn.getAttribute('data-message');
    const customWarn = triggerBtn.getAttribute('data-warning');
    const customIcon = triggerBtn.getAttribute('data-card-icon');

    // Update Form Action
    const form = document.getElementById('{{ $formId }}');
    if (form && actionUrl) {
      form.setAttribute('action', actionUrl);
    }

    // Update Item Name & Detail
    const nameEl   = document.getElementById('{{ $id }}ItemName');
    const detailEl = document.getElementById('{{ $id }}ItemDetail');
    
    if (nameEl) nameEl.textContent = itemName;
    if (detailEl) {
      detailEl.textContent = itemDetail;
      detailEl.style.display = itemDetail ? 'block' : 'none';
    }

    // Legacy fallback IDs
    const legacyName = document.getElementById('deleteModalDeviceName');
    const legacyUuid = document.getElementById('deleteModalDeviceUuid');
    if (legacyName) legacyName.textContent = itemName;
    if (legacyUuid) legacyUuid.textContent = itemDetail;

    // Custom Overrides
    if (customMsg) {
      const msgEl = document.getElementById('{{ $id }}Message');
      if (msgEl) msgEl.textContent = customMsg;
    }
    if (customWarn) {
      const warnEl = document.querySelector('#{{ $id }}Warning span');
      if (warnEl) warnEl.textContent = customWarn;
    }
    if (customIcon) {
      const iconEl = document.getElementById('{{ $id }}CardIcon');
      if (iconEl) iconEl.className = customIcon + ' dev-confirm-item-card__icon';
    }
  });
});
</script>
@endonce
