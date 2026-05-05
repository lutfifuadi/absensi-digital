@extends('layouts/layoutFront')

@section('title', 'Kebijakan Privasi — ' . ($info['nama_lembaga'] ?? 'Sistem Absensi'))

@section('content')
<style>
.pub-hero {
  background: linear-gradient(135deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
  padding: 5rem 0 3rem;
}
.policy-section { padding: 3rem 0; }
.policy-card {
  background: #fff;
  border-radius: 16px;
  padding: 2.5rem;
  box-shadow: 0 2px 20px rgba(0,0,0,.07);
  margin-bottom: 1.5rem;
}
.policy-card h4 {
  display: flex; align-items: center; gap: 0.75rem;
  color: #111827; margin-bottom: 1rem;
}
.policy-card h4 .icon {
  width: 40px; height: 40px;
  background: linear-gradient(135deg, #696cff, #9397ff);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.1rem; flex-shrink: 0;
}
.policy-card p, .policy-card li { color: #6b7280; line-height: 1.8; }
</style>

<section class="pub-hero text-white text-center">
  <div class="container">
    <div class="badge bg-primary bg-opacity-25 text-primary-emphasis px-3 py-2 mb-3 fs-6">
      <i class="ti tabler-shield-check me-1"></i> Kebijakan Privasi
    </div>
    <h1 class="fw-bold display-5 mb-3">Kebijakan Privasi</h1>
    <p class="fs-5 text-white-50 mb-0">Terakhir diperbarui: {{ date('d F Y') }}</p>
    <p class="text-white-50 small">{{ $info['nama_lembaga'] }}</p>
  </div>
</section>

<section class="policy-section">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-9">

        <div class="alert alert-info mb-4" style="border-radius:12px;">
          <i class="ti tabler-info-circle me-2"></i>
          <strong>Ringkasan:</strong> Kami mengumpulkan data yang diperlukan untuk operasional sistem presensi, 
          melindunginya dengan standar keamanan tinggi, dan tidak menjualnya kepada pihak ketiga.
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-file-description"></i></span>1. Pendahuluan</h4>
          <p>
            Kebijakan Privasi ini menjelaskan bagaimana {{ $info['nama_lembaga'] }} ("kami", "Lembaga") 
            mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pribadi Anda saat menggunakan 
            Sistem Presensi Digital kami. Dengan menggunakan sistem ini, Anda menyetujui praktik yang 
            dijelaskan dalam kebijakan ini.
          </p>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-database"></i></span>2. Data yang Kami Kumpulkan</h4>
          <p>Kami mengumpulkan informasi berikut untuk keperluan operasional sistem presensi:</p>
          <ul>
            <li><strong>Data Identitas:</strong> Nama lengkap, NIS/NISN, nomor induk pegawai</li>
            <li><strong>Data Kontak:</strong> Nomor telepon, nomor WhatsApp orang tua, alamat</li>
            <li><strong>Data Presensi:</strong> Waktu masuk, waktu pulang, status kehadiran, lokasi (jika diaktifkan)</li>
            <li><strong>Data Akun:</strong> Username, password (tersimpan dalam bentuk terenkripsi)</li>
            <li><strong>Data Perangkat:</strong> Fingerprint perangkat untuk otorisasi scan QR</li>
            <li><strong>Log Aktivitas:</strong> Aktivitas sistem untuk audit keamanan</li>
          </ul>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-eye"></i></span>3. Penggunaan Data</h4>
          <p>Data yang dikumpulkan digunakan untuk:</p>
          <ul>
            <li>Mencatat dan memverifikasi kehadiran siswa, guru, dan staff</li>
            <li>Mengirimkan notifikasi WhatsApp kepada orang tua terkait kehadiran anak</li>
            <li>Menghasilkan laporan kehadiran untuk keperluan administrasi</li>
            <li>Memastikan keamanan sistem dari akses tidak sah</li>
            <li>Meningkatkan kualitas layanan sistem</li>
          </ul>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-brand-whatsapp"></i></span>4. Penggunaan Nomor WhatsApp</h4>
          <p>
            Nomor WhatsApp orang tua digunakan <strong>khusus</strong> untuk mengirimkan notifikasi 
            absensi secara otomatis. Kami menerapkan validasi nomor untuk memastikan pesan hanya 
            dikirim ke nomor yang aktif di WhatsApp. Nomor WA tidak akan digunakan untuk keperluan 
            komersial, promosi, atau dibagikan kepada pihak ketiga.
          </p>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-shield-lock"></i></span>5. Keamanan Data</h4>
          <p>Kami menerapkan langkah-langkah keamanan berikut:</p>
          <ul>
            <li>Enkripsi password menggunakan algoritma bcrypt</li>
            <li>HTTPS untuk semua komunikasi data</li>
            <li>Pembatasan akses berbasis peran (Role-Based Access Control)</li>
            <li>Log aktivitas untuk deteksi akses mencurigakan</li>
            <li>Validasi perangkat untuk fitur scan QR</li>
          </ul>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-users-group"></i></span>6. Berbagi Data</h4>
          <p>
            Kami <strong>tidak menjual, menyewakan, atau memperdagangkan</strong> data pribadi Anda 
            kepada pihak ketiga. Data hanya dapat dibagikan kepada:
          </p>
          <ul>
            <li>Pihak internal lembaga yang memiliki otoritas (kepala sekolah, wali kelas)</li>
            <li>Layanan WhatsApp Gateway yang digunakan untuk pengiriman notifikasi</li>
            <li>Pihak berwenang jika diwajibkan oleh hukum yang berlaku</li>
          </ul>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-user-check"></i></span>7. Hak Pengguna</h4>
          <p>Anda memiliki hak untuk:</p>
          <ul>
            <li>Mengakses data pribadi Anda yang tersimpan dalam sistem</li>
            <li>Meminta koreksi data yang tidak akurat</li>
            <li>Meminta penghapusan data (dengan pertimbangan keperluan operasional)</li>
            <li>Mengajukan keberatan atas penggunaan data tertentu</li>
          </ul>
          <p>
            Untuk menggunakan hak-hak tersebut, silakan hubungi admin lembaga melalui kontak yang tersedia.
          </p>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-clock"></i></span>8. Retensi Data</h4>
          <p>
            Data presensi disimpan selama periode tahun akademik dan dapat diakses oleh pihak 
            berwenang lembaga sesuai kebijakan arsip sekolah. Data akun disimpan selama pengguna 
            masih aktif sebagai bagian dari komunitas lembaga.
          </p>
        </div>

        <div class="policy-card">
          <h4><span class="icon"><i class="ti tabler-refresh"></i></span>9. Perubahan Kebijakan</h4>
          <p>
            Kami dapat memperbarui Kebijakan Privasi ini sewaktu-waktu. Perubahan signifikan akan 
            diinformasikan melalui pengumuman di sistem. Penggunaan sistem setelah perubahan 
            dianggap sebagai persetujuan atas kebijakan yang diperbarui.
          </p>
        </div>

        <div class="policy-card" style="background:#f8fafc;">
          <h4><span class="icon"><i class="ti tabler-mail"></i></span>10. Kontak</h4>
          <p>Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami:</p>
          <ul class="list-unstyled">
            @if($info['nama_lembaga'])<li><strong>Lembaga:</strong> {{ $info['nama_lembaga'] }}</li>@endif
            @if($info['alamat_lembaga'])<li><strong>Alamat:</strong> {{ $info['alamat_lembaga'] }}</li>@endif
            @if($info['email_lembaga'])<li><strong>Email:</strong> {{ $info['email_lembaga'] }}</li>@endif
            @if($info['kontak_lembaga'])<li><strong>Telepon:</strong> {{ $info['kontak_lembaga'] }}</li>@endif
          </ul>
        </div>

      </div>
    </div>
  </div>
</section>
@endsection
