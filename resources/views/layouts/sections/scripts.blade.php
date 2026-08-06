<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js'])

@if ($configData['hasCustomizer'])
  @vite('resources/assets/vendor/libs/pickr/pickr.js')
@endif

@vite(['resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/js/menu.js'])

@yield('vendor-script')
<!-- END: Page Vendor JS-->

<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])
<!-- END: Theme JS-->

<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->

<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- app JS -->
@vite(['resources/js/app.js'])
<!-- END: app JS-->

@stack('modals')
@livewireScripts

<!-- Notification Bell JS -->
<script>
  function notifPost(url, data, callback) {
    var params = new URLSearchParams(data);
    fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: params.toString()
    }).then(function(res) {
      return res.json();
    }).then(callback);
  }

  function updateBadge(delta, clear) {
    var badge = document.querySelector('.badge-notifications');
    if (!badge) return;
    if (clear) {
      badge.remove();
      return;
    }
    var cur = parseInt(badge.textContent) || 0;
    var newVal = cur + delta;
    if (newVal <= 0) {
      badge.remove();
    } else {
      badge.textContent = newVal > 9 ? '9+' : newVal;
    }
  }

  // Mark single notification as read
  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-mark-read');
    if (!btn) return;
    var id = btn.dataset.notifId;
    var item = btn.closest('.list-group-item');
    notifPost('{{ route('admin.notifications.mark-read') }}', {
      _token: '{{ csrf_token() }}',
      id: id
    }, function(r) {
      if (r.success) {
        item.style.transition = 'opacity 0.3s';
        item.style.opacity = '0';
        setTimeout(function() {
          item.remove();
          updateBadge(-1);
        }, 300);
      }
    });
  });

  // Mark all as read
  document.addEventListener('click', function(e) {
    if (!e.target.closest('#btn-mark-all-read')) return;
    notifPost('{{ route('admin.notifications.mark-read') }}', {
      _token: '{{ csrf_token() }}',
      all: 1
    }, function(r) {
      if (r.success) {
        document.querySelectorAll('.dropdown-notifications-list .list-group-item').forEach(function(el) {
          el.remove();
        });
        updateBadge(0, true);
        var allSection = document.querySelector('.dropdown-notifications-all');
        if (allSection) allSection.remove();
      }
    });
  });
</script>
<!-- / Notification Bell JS -->

@push('scripts')
@endpush
@stack('scripts')

{{-- Impersonation Banner: auto-adjust body padding --}}
@if(session('impersonator_id') || session('impersonated_by'))
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var banner = document.getElementById('impersonation-banner');
    if (banner) {
      var computedStyle = window.getComputedStyle(banner);
      var isBottom = computedStyle.bottom === '0px' || banner.style.bottom === '0px';
      
      if (isBottom) {
        document.body.style.paddingBottom = banner.offsetHeight + 'px';
        document.body.style.paddingTop = '';
      } else {
        document.body.style.paddingTop = banner.offsetHeight + 'px';
        document.body.style.paddingBottom = '';
      }
    }
  });
</script>
@endif

{{-- Auto-attach PerfectScrollbar to Select2 dropdowns --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof $ !== 'undefined') {
      $(document).on('select2:open', function () {
        setTimeout(function () {
          var container = document.querySelector('.select2-results__options');
          if (container && typeof PerfectScrollbar !== 'undefined' && !container._ps) {
            container._ps = new PerfectScrollbar(container, {
              wheelPropagation: false
            });
          } else if (container && container._ps) {
            container._ps.update();
          }
        }, 50);
      });
    }
  });
</script>

{{-- Global Keep-Alive & CSRF Session Protection --}}
<script>
  (function() {
    'use strict';

    // 1. Keep-Alive ping every 10 minutes to prevent session expiration on active tabs
    setInterval(function() {
      fetch('{{ route("keep-alive") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data && data.csrf_token) {
          document.querySelectorAll('input[name="_token"]').forEach(function(el) {
            el.value = data.csrf_token;
          });
          var metaCsrf = document.querySelector('meta[name="csrf-token"]');
          if (metaCsrf) {
            metaCsrf.setAttribute('content', data.csrf_token);
          }
        }
      })
      .catch(function() {});
    }, 10 * 60 * 1000);

    // 2. Intercept 419 Page Expired errors globally for fetch API
    var originalFetch = window.fetch;
    window.fetch = function() {
      return originalFetch.apply(this, arguments).then(function(response) {
        if (response.status === 419) {
          if (window.Swal) {
            Swal.fire({
              icon: 'info',
              title: 'Sesi Halaman Kedaluwarsa',
              text: 'Sesi Anda telah berakhir. Halaman akan diperbarui otomatis untuk menyegarkan data.',
              timer: 2000,
              showConfirmButton: false
            }).then(function() {
              window.location.reload();
            });
          } else {
            alert('Sesi Anda telah berakhir. Halaman akan diperbarui otomatis.');
            window.location.reload();
          }
        }
        return response;
      });
    };
  })();
</script>

