@php
$containerFooter =
isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
? 'container-xxl'
: 'container-fluid';
$namaSekolah = \App\Models\Pengaturan::where('key', 'nama_lembaga')->value('value')
  ?? \App\Models\Pengaturan::where('key', 'nama_sekolah')->value('value')
  ?? config('variables.templateName');
@endphp

<!-- Footer-->
<footer class="content-footer footer bg-footer-theme">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
      <div class="text-body mb-2 mb-md-0">
        &#169; {{ date('Y') }}, {{ $namaSekolah }} &mdash; Digital Attendance System
      </div>
      <div class="d-none d-lg-inline-block">
        <a href="{{ route('dashboard') }}" class="footer-link me-4">Dashboard</a>
        <a href="javascript:void(0)" class="footer-link me-4">Panduan</a>
        <a href="javascript:void(0)" class="footer-link me-4">Kebijakan Privasi</a>
        <a href="javascript:void(0)" class="footer-link">Bantuan</a>
      </div>
    </div>
  </div>
</footer>
<!-- / Footer -->
