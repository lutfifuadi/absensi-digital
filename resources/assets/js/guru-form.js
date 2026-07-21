/**
 * Guru Form – Tagify (Mata Pelajaran) + Select2 (Kelas) + Role Checkbox
 */

'use strict';

(function () {
  // ── 1. Tagify – Mata Pelajaran ──────────────────────────────────────────────
  const mapelInput = document.querySelector('#mapel_tagify');

  if (mapelInput) {
    // whitelist dari data attribute (JSON yang di-render Blade)
    let whitelist = [];
    try {
      whitelist = JSON.parse(mapelInput.dataset.whitelist || '[]');
    } catch (e) {
      console.warn('[GuruForm] Failed to parse tagify whitelist', e);
    }

    const tagify = new Tagify(mapelInput, {
      tagTextProp: 'label',           // tampilkan field "label" sebagai teks tag
      whitelist: whitelist,
      enforceWhitelist: false,        // boleh buat tag baru (custom mapel)
      skipInvalid: false,
      dropdown: {
        maxItems: 50,
        enabled: 0,                   // tampilkan dropdown sejak karakter pertama
        closeOnSelect: false,
        highlightFirst: true,
        searchKeys: ['label', 'value']
      }
    });

    // Sync ke hidden inputs saat tag berubah
    function syncHiddenInputs() {
      // Hapus semua hidden input mapel lama
      mapelInput.parentNode
        .querySelectorAll('input[name="mapel_ids[]"]')
        .forEach(function (el) { el.remove(); });

      tagify.value.forEach(function (item) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'mapel_ids[]';
        // Jika value adalah ID (angka), kirim ID; jika tag baru kirim label/nama
        inp.value = item.value || item.label || item.tag;
        mapelInput.parentNode.insertBefore(inp, mapelInput);
      });
    }

    tagify.on('change', syncHiddenInputs);
    // Inisialisasi awal (sync nilai yang sudah ada dari Blade)
    syncHiddenInputs();
  }

  // ── 2. Select2 – Kelas Wali Kelas + Role Checkbox ──────────────────────────
  $(function () {
    var $kelasWrapper = $('#kelas-wrapper');
    var $waliKelasCheckbox = $('#role_wali_kelas');
    var kelasInited = false;

    function initKelasSelect2() {
      if (kelasInited) return;
      var $select = $kelasWrapper.find('.select2-kelas');
      if ($select.length && !$select.data('select2')) {
        $select.wrap('<div class="position-relative"></div>').select2({
          placeholder: $select.data('placeholder') || 'Pilih kelas...',
          dropdownParent: $select.parent(),
          width: '100%'
        });
        kelasInited = true;
      }
    }

    function destroyKelasSelect2() {
      var $select = $kelasWrapper.find('.select2-kelas');
      if ($select.length && $select.data('select2')) {
        $select.select2('destroy');
        var $wrap = $select.parent('.position-relative');
        if ($wrap.length) $select.unwrap();
        kelasInited = false;
      }
    }

    function toggleKelas(show) {
      if (show) {
        $kelasWrapper.removeClass('d-none');
        setTimeout(initKelasSelect2, 100);
      } else {
        destroyKelasSelect2();
        $kelasWrapper.addClass('d-none');
      }
    }

    if ($waliKelasCheckbox.length) {
      toggleKelas($waliKelasCheckbox.is(':checked'));
      $waliKelasCheckbox.on('change', function () {
        toggleKelas($(this).is(':checked'));
      });
    }

    // ── 3. Role Checkbox – visual card toggle ──────────────────────────────
    $(document).on('change', '.role-checkbox:not(:disabled)', function () {
      var $card = $(this).closest('.role-checkbox-card');
      if ($(this).is(':checked')) {
        $card.addClass('is-checked');
      } else {
        $card.removeClass('is-checked');
      }
    });
  });
})();
