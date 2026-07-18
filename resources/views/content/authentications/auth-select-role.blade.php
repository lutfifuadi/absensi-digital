@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutMaster')

@section('title', 'Pilih Peran - Portal Presensi')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
<style>
  .role-card {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    border-radius: 5px !important;
  }
  .role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
  }
  .card, .avatar, .avatar-initial, .rounded, .alert {
    border-radius: 5px !important;
  }
</style>
@endsection

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6" style="max-width: 800px;">
      <div class="card">
        <div class="card-body">
          <!-- Logo -->
          <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link">
              <span class="app-brand-logo demo">@include('_partials.macros')</span>
              <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName', 'Presensi') }}</span>
            </a>
          </div>
          <!-- /Logo -->
          
          <h4 class="mb-2 text-center">Selamat Datang, {{ auth()->user()->name }}! 👋</h4>
          <p class="mb-6 text-center text-muted">Anda memiliki beberapa peran dalam sistem ini. Silakan pilih peran yang ingin Anda gunakan sekarang:</p>

          <form id="roleSelectForm" action="{{ route('role.select.post') }}" method="POST">
            @csrf
            <input type="hidden" id="selectedRole" name="role" value="">
            
            <div class="row g-4 justify-content-center">
              @php
                $roleDetails = [
                  'super_admin' => [
                    'label' => 'Super Administrator',
                    'icon' => '👑',
                    'description' => 'Akses penuh ke seluruh konfigurasi sistem, database, dan audit log.',
                    'color' => 'danger'
                  ],
                  'admin_sekolah' => [
                    'label' => 'Admin Sekolah',
                    'icon' => '🏫',
                    'description' => 'Manajemen data master sekolah, jadwal, libur, dan verifikasi perizinan.',
                    'color' => 'primary'
                  ],
                  'operator' => [
                    'label' => 'Operator',
                    'icon' => '🖥️',
                    'description' => 'Entri data harian, update siswa/guru, dan pemantauan status sistem.',
                    'color' => 'info'
                  ],
                  'guru' => [
                    'label' => 'Guru / Pendidik',
                    'icon' => '📚',
                    'description' => 'Akses portal mengajar, penugasan mandiri, dan absensi kehadiran guru.',
                    'color' => 'success'
                  ],
                  'wali_kelas' => [
                    'label' => 'Wali Kelas',
                    'icon' => '🏠',
                    'description' => 'Monitoring kehadiran kelas binaan, persetujuan izin/sakit, dan rekap bulanan.',
                    'color' => 'warning'
                  ],
                  'staff_tu' => [
                    'label' => 'Staff TU',
                    'icon' => '📋',
                    'description' => 'Administrasi sekolah, absensi staff, dan rekap presensi guru/staff.',
                    'color' => 'secondary'
                  ],
                  'siswa' => [
                    'label' => 'Siswa',
                    'icon' => '🎓',
                    'description' => 'Akses kartu pelajar digital, riwayat kehadiran, dan pengajuan izin/sakit mandiri.',
                    'color' => 'dark'
                  ],
                  'orang_tua' => [
                    'label' => 'Orang Tua',
                    'icon' => '👨‍👩‍👧',
                    'description' => 'Monitoring kehadiran putra/putri secara real-time dan pengajuan izin/sakit anak.',
                    'color' => 'danger'
                  ],
                  'piket' => [
                    'label' => 'Guru Piket',
                    'icon' => '🚪',
                    'description' => 'Scan QR presensi siswa masuk/pulang, catat keterlambatan, dan rekap harian.',
                    'color' => 'warning'
                  ]
                ];
              @endphp

              @foreach($availableRoles as $role)
                @php
                  $details = $roleDetails[$role] ?? [
                    'label' => ucwords(str_replace('_', ' ', $role)),
                    'icon' => '👤',
                    'description' => 'Akses dashboard untuk peran ' . $role,
                    'color' => 'secondary'
                  ];
                @endphp
                <div class="col-md-6">
                  <div class="card h-100 border role-card text-center p-4" onclick="selectRole('{{ $role }}')">
                    <div class="avatar avatar-lg mx-auto mb-4 bg-label-{{ $details['color'] }} rounded">
                      <span class="fs-2">{{ $details['icon'] }}</span>
                    </div>
                    <h5 class="card-title mb-2">{{ $details['label'] }}</h5>
                    <p class="card-text text-muted small mb-0">{{ $details['description'] }}</p>
                  </div>
                </div>
              @endforeach
            </div>
            
            @if($errors->has('role'))
              <div class="alert alert-danger mt-4 text-center">
                {{ $errors->first('role') }}
              </div>
            @endif
          </form>

          <div class="text-center mt-6">
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-link text-muted">
                <i class="ti tabler-logout me-1"></i> Keluar / Logout
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function selectRole(role) {
    document.getElementById('selectedRole').value = role;
    document.getElementById('roleSelectForm').submit();
  }
</script>
@endsection
