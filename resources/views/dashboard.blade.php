@extends('layouts/layoutMaster')

@section('title', 'Dashboard')

@section('content')
  <div class="row">
    <div class="col-12">
      <h1 class="mb-3">Dashboard</h1>
    </div>
  </div>

  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Selamat datang, {{ auth()->user()->name }}!</h5>
          <p class="card-text">Peran Anda: <strong>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</strong></p>
          <p class="card-text">Gunakan panel ini untuk mengakses fitur sesuai peran Anda.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="row gy-4">
    @php
      $role = auth()->user()->role;
      $cards = [];

      if (in_array($role, ['super_admin', 'admin_sekolah'], true)) {
          $cards = [
              [
                  'title' => 'Master Data',
                  'description' => 'Kelola data tahun akademik, kelas, siswa, guru, dan staff.',
                  'route' => route('admin.master-data'),
                  'button' => 'Buka Master Data',
              ],
              [
                  'title' => 'Absensi',
                  'description' => 'Akses absensi siswa, guru, dan staff.',
                  'route' => route('admin.absensi-siswa.index'),
                  'button' => 'Buka Absensi',
              ],
              [
                  'title' => 'Laporan',
                  'description' => 'Lihat rekap dan export laporan absensi.',
                  'route' => route('admin.laporan.index'),
                  'button' => 'Buka Laporan',
              ],
              [
                  'title' => 'Izin & Sakit',
                  'description' => 'Kelola pengajuan izin dan sakit untuk seluruh pengguna.',
                  'route' => route('admin.izin-sakit.index'),
                  'button' => 'Buka Izin/Sakit',
              ],
          ];
      } elseif ($role === 'guru') {
          $cards = [
              [
                  'title' => 'Absensi Guru',
                  'description' => 'Lihat dan kelola jam kehadiran guru.',
                  'route' => route('admin.absensi-guru.index'),
                  'button' => 'Buka Absensi Guru',
              ],
              [
                  'title' => 'Izin & Sakit',
                  'description' => 'Ajukan atau pantau status izin dan sakit.',
                  'route' => route('admin.izin-sakit.index'),
                  'button' => 'Buka Izin/Sakit',
              ],
          ];
      } elseif ($role === 'wali_kelas') {
          $cards = [
              [
                  'title' => 'Absensi Siswa',
                  'description' => 'Monitor dan catat absensi siswa per kelas.',
                  'route' => route('admin.absensi-siswa.index'),
                  'button' => 'Buka Absensi Siswa',
              ],
              [
                  'title' => 'Izin & Sakit',
                  'description' => 'Pantau pengajuan izin/sakit untuk siswa Anda.',
                  'route' => route('admin.izin-sakit.index'),
                  'button' => 'Buka Izin/Sakit',
              ],
          ];
      } elseif ($role === 'staff_tu') {
          $cards = [
              [
                  'title' => 'Absensi Staff',
                  'description' => 'Kelola absensi staff Tata Usaha.',
                  'route' => route('admin.absensi-staff.index'),
                  'button' => 'Buka Absensi Staff',
              ],
              [
                  'title' => 'Izin & Sakit',
                  'description' => 'Lihat status izin dan sakit staff.',
                  'route' => route('admin.izin-sakit.index'),
                  'button' => 'Buka Izin/Sakit',
              ],
          ];
      } else {
          $cards = [
              [
                  'title' => 'Izin & Sakit',
                  'description' => 'Ajukan izin atau sakit jika diperlukan.',
                  'route' => route('admin.izin-sakit.index'),
                  'button' => 'Buka Izin/Sakit',
              ],
              [
                  'title' => 'Profil',
                  'description' => 'Perbarui informasi profil Anda.',
                  'route' => route('profile.show'),
                  'button' => 'Buka Profil',
              ],
          ];

          // Jika siswa kelas XII, tambahkan card kartu pelepasan
          if ($role === 'siswa') {
              $siswa = \App\Models\Siswa::with('kelas')->where('user_id', auth()->id())->first();
              // Dump untuk debugging jika diperlukan (hapus jika sudah jalan)
              // dd($siswa->kelas->tingkat); 
              
              if ($siswa && $siswa->kelas) {
                  $tingkat = trim($siswa->kelas->tingkat);
                  if ($tingkat === 'XII' || $tingkat === '12') {
                      $cards[] = [
                          'title' => 'Kartu Pelepasan',
                          'description' => 'Unduh kartu peserta khusus untuk acara pelepasan/wisuda.',
                          'route' => route('siswa.download-kartu-pelepasan'),
                          'button' => 'Download Kartu',
                      ];
                  }
              }
          }
      }
    @endphp

    @foreach ($cards as $card)
      <div class="col-6 col-md-6 col-xl-4">
        <div class="card h-100">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title">{{ $card['title'] }}</h5>
            <p class="card-text">{{ $card['description'] }}</p>
            <div class="mt-auto">
              <a href="{{ $card['route'] }}" class="btn btn-primary">{{ $card['button'] }}</a>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection
