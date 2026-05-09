# Changelog

## [1.0.7] - 2026-05-09
### Added
- **Fungsi Export Siswa**: Menambahkan fitur untuk mengunduh data siswa ke dalam format **Excel (.xlsx)** dan **CSV (.csv)** langsung dari halaman daftar siswa.
- **Filter Pintar pada Export**: Sistem export secara otomatis menyesuaikan data yang diunduh dengan filter pencarian dan periode Tahun Ajaran yang sedang aktif di layar pengguna.
- **Antarmuka Premium**: Penambahan tombol dropdown Export yang elegan dengan desain glassmorphism, memberikan pengalaman pengguna yang lebih premium dan fungsional.

### Fixed
- Optimasi struktur export menggunakan library `maatwebsite/excel` untuk memastikan performa yang cepat dan penggunaan memori yang efisien saat menangani data dalam jumlah besar.

## [1.0.6] - 2026-05-09
### 🌟 Peningkatan Pengalaman Pengguna
- **Penyelarasan Istilah**: Kami telah mengubah sebutan "Tahun Akademik" menjadi **"Tahun Ajaran"** di seluruh bagian aplikasi agar lebih familiar dan sesuai dengan kebiasaan di lingkungan sekolah.
- **Navigasi yang Lebih Konsisten**: Menu sidebar dan judul halaman kini lebih seragam menggunakan istilah "Tahun Ajaran", memudahkan Bapak/Ibu dalam mengelola periode belajar.
- **Penyempurnaan Tampilan**: Label tombol dan pesan notifikasi diperbarui agar lebih informatif dan nyaman dipandang.

### 🛠️ Stabilitas Sistem
- Optimalisasi sistem pemilihan periode belajar untuk memastikan transisi antar tahun ajaran berjalan lebih lancar tanpa kendala sesi.
- Perbaikan minor pada tampilan tanggal agar lebih mudah dibaca pada daftar riwayat.

## [1.0.5] - 2026-05-09
### Added
- **Menu Tahun Akademik**: Menambahkan menu "Tahun Akademik" ke dalam menu vertikal (sidebar) admin tepat di atas menu "Kelas" untuk memudahkan akses manajemen periode akademik.

### Changed
- **Posisi Menu Sistem & Pembaruan**: Memindahkan blok menu "Sistem & Pembaruan" (termasuk Update Sistem, Manajemen Lisensi, dan Pembelian & Distribusi) ke posisi paling bawah pada sidebar admin untuk memberikan prioritas visual pada fitur operasional sekolah.

### Fixed
- **Error 500 Tahun Akademik**: Memperbaiki error `Call to a member function format() on string` pada halaman `/admin/tahun-akademik`. Perbaikan dilakukan dengan melakukan parsing Carbon secara eksplisit pada view untuk memastikan kompatibilitas format tanggal.
- **Sinkronisasi Data Role**: Memperbaiki ketidaksinkronan jumlah user pada halaman Manajemen Role. Penghitungan kini mencakup user yang memiliki role di kolom utama `role` maupun di kolom JSON `roles` (multiple roles). Serta memastikan semua role sistem terdaftar di database.

## [1.0.4] - 2026-05-08
### Added
- **Premium Success Modal**: Mengganti notifikasi toast sukses sinkronisasi menjadi Modal UI yang premium dengan animasi *ring-pulse* dan desain yang elegan.

### Changed
- **Brand Name Update**: Mengubah nama aplikasi pada bagian atas menu vertikal (sidebar) menjadi **'E-Absensi'**. Perubahan ini bersifat statis untuk memperkuat identitas aplikasi pada menu utama.
- **UI Notifikasi Update**: Mengimplementasikan mode ultra-compact untuk notifikasi update. Menggunakan padding minimal, font size yang dioptimalkan, dan elemen visual yang lebih ringkas agar tidak mengganggu area kerja utama namun tetap terlihat elegan dengan skema warna orange gradient.

### Fixed
- **Responsivitas Dashboard Utama** (`/dashboard`):
  - Konsolidasi media queries untuk optimasi tampilan di smartphone dan tablet.
  - Perbaikan positioning pada elemen `.das-stats-row` (kartu statistik) untuk mencegah overlapping dengan konten hero di layar kecil.
  - Penyesuaian layout kartu statistik menjadi **2 kolom** pada perangkat mobile (smartphone) agar lebih proporsional.
  - Penyesuaian padding, margin, dan font size pada berbagai breakpoint (1199px, 991px, 767px, 575px, 400px).
  - Optimasi grid "Quick Access" dan "Status Hari Ini" untuk tampilan portrait smartphone.
  - File yang diubah: `resources/views/dashboards/super-admin.blade.php`

- **Hapus Siswa Tanpa Reload Halaman** (`/admin/siswa`):
  - **Bug route ordering**: Route `DELETE admin/siswa/delete-all` dipindahkan ke SEBELUM `Route::resource('siswa')` di `routes/web.php` — sebelumnya Laravel mencocokkan string `delete-all` sebagai parameter `{siswa}` sehingga menghasilkan 404.
  - **Individual delete AJAX**: Tombol hapus per baris siswa dikonversi dari form submit biasa ke AJAX fetch menggunakan event delegation di container — tabel kini refresh via `fetchData()` tanpa reload halaman.
  - **Controller AJAX support**: Method `destroy()` di `SiswaController` kini mengembalikan JSON response untuk AJAX request, fallback ke redirect untuk form submission biasa.
  - **Security**: CSRF token diambil dari meta tag `<meta name="csrf-token">` (standar Laravel) bukan dari inline `data-csrf` attribute.
  - File yang diubah: `routes/web.php`, `app/Http/Controllers/Admin/SiswaController.php`, `resources/views/admin/siswa/table.blade.php`, `resources/views/admin/siswa/index.blade.php`

### Removed
- **Validasi Username Minimal 6 Karakter**: Menghilangkan batasan minimal 6 karakter pada field username di halaman Login dan Registrasi.
  - Memungkinkan login dengan username pendek (misal: `admin`).
  - Perubahan dilakukan pada file Javascript: `pages-auth.js`, `pages-auth-multisteps.js`, `form-validation.js`, dan `form-wizard-validation.js`.

- **Lock & Masking Field Pembaruan GitHub**: Mengunci field `github_repo_owner`, `github_repo_name`, dan `app_version` di halaman pengaturan admin.
  - Menggunakan `type="password"` untuk menyembunyikan data sensitif (masking).
  - Menggunakan atribut `disabled` untuk mencegah perubahan data oleh user secara tidak sengaja dan memastikan data tidak dikirim ulang saat penyimpanan form.
  - Meningkatkan keamanan informasi konfigurasi repositori.
- **Field Input Nama Sekolah/Lembaga di Step 2 Installer**: Menambahkan field input wajib "Nama Sekolah/Lembaga" pada form aktivasi produk (Step 2).
  - Memungkinkan user menginput nama instansi secara manual jika tidak terdeteksi otomatis dari database pusat.
  - Validasi field ditambahkan di `InstallerController::step2Submit()`.
  - Prioritas data: API Pusat (jika ada) > Input User.
  - Memastikan log `Nama sekolah terdaftar` selalu terisi dan tidak lagi menunjukkan "(tidak tersedia)".
- **Show/Hide Password di Step 3 Installer**: Menambahkan fitur toggle icon mata pada field password database.
  - Memudahkan pengguna memastikan input password database sudah benar sebelum melanjutkan ke pengecekan koneksi.
  - Implementasi menggunakan logic JavaScript/jQuery di `resources/views/installer/step3.blade.php`.
- **Show/Hide Password di Step 5 Installer**: Menambahkan fitur toggle icon mata pada field password administrator utama.
  - Memastikan pengguna tidak salah mengetik password admin saat proses finalisasi instalasi.
  - Optimasi pemuatan script dengan memindahkan logic ke `@section('scripts')` mengikuti standar layout terbaru.

## [1.0.2] - 2026-05-08
### Added
- **GitHub Release Integration**: Berhasil melakukan pengetesan sinkronisasi update dengan GitHub Release v1.0.2.
- **Update Connection**: Memastikan sistem dapat mendeteksi versi terbaru dari repositori `lutfifuadi/absensi-digital`.

## [Unreleased] - 2026-05-07
### Added
- **Nama Sekolah di Step 2 Installer**: Setelah verifikasi lisensi berhasil, nama sekolah yang terdaftar di database pusat kini ditampilkan sebagai field read-only di halaman `/install/step2`. Data ini otomatis terisi di Step 4 (Profil Sekolah) melalui session `install_school_name`.
  - `InstallerController::step2Submit()` sekarang mengekstrak `school_name` dari API response dan menyimpannya ke session dan `.env SCHOOL_NAME`
  - DEV bypass (`DEV-MASTER-KEY`) juga mengisi session dengan nilai `"Development School"`
  - Field read-only hanya ditampilkan jika nilai tersedia (conditional `@if(session('install_school_name'))`)
- **Sistem Distribusi Lisensi ZIP**: Modul baru untuk mengelola pembelian & distribusi aplikasi pasca-pembayaran.
  - Admin panel `Pembelian & Distribusi` di menu Sistem & Pembaruan (khusus super_admin)
  - Generate license key otomatis format `PRE-XXXX-XXXX-XXXX-XXXX` saat pembayaran dikonfirmasi
  - Kirim email otomatis ke klien berisi license key + signed download link (berlaku 7 hari)
  - Fitur kirim ulang email lisensi
  - Fitur cabut (revoke) lisensi
  - Download endpoint terproteksi signed URL: `GET /download/app/{token}`
- **API License Verify** (`POST /api/license/verify`): Endpoint untuk verifikasi license key dari installer klien. Rate-limited 30 req/menit. Mendukung pendaftaran domain otomatis saat aktivasi pertama.
- Template email HTML responsif untuk pengiriman lisensi.
- Halaman fallback download manual untuk kasus GitHub Release tidak tersedia.
- Tabel database `pembelian_lisensi` dengan kolom: nama_klien, email_klien, domain, license_key, status, payment_status, download_token, expires_at.
- Penambahan validasi lisensi pada proses Update Assets (Publish Livewire Assets) di Dashboard Admin.
- Integrasi pengecekan lisensi ke server `https://saas-presensi.lutfifuadi.my.id/api/license/verify`.
- Pesan error informatif di UI jika lisensi tidak valid atau belum dikonfigurasi.
