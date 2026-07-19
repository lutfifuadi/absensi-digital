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
  document.addEventListener('DOMContentLoaded', function() {
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
