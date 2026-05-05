# CHANGELOG

Semua perubahan signifikan pada project ini didokumentasikan di file ini.  
Format mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/)  
Versioning mengikuti [Semantic Versioning](https://semver.org/lang/id/)

---

## [Unreleased]

### Added
- **Sinkronisasi Otomatis PMBM → Absensi**: Ketika sistem PMBM mengirimkan data siswa yang memenuhi syarat (`status=lulus` DAN `daftar_ulang_selesai=true`), sistem absensi secara otomatis menambahkan siswa tersebut sebagai siswa aktif (`status=aktif`) siap digunakan untuk pencatatan kehadiran.
- **Filter syarat PMBM** di `SyncMasterData` command: hanya siswa dengan `status=lulus` + `daftar_ulang_selesai=true` yang diproses; siswa lain dilewati dan dihitung sebagai `lewati`.
- **Webhook PMBM** (`POST /api/v1/pmbm/presensi`) kini menerima field tambahan: `daftar_ulang_selesai`, `wawancara_selesai`, `nomor_pendaftaran`, `nomor_hp_ayah`, `nomor_hp_ibu`, `nomor_hp_wali`.
- **Data `no_hp_ortu`** kini disinkronisasi dari field `phone_ayah`/`phone_ibu` saat pull sync dari PMBM.

### Fixed
- **Perbaiki error duplikasi email** saat sinkronisasi siswa dari PMBM: gunakan SAVEPOINT MariaDB di `SyncService::syncUser` agar retry SELECT bisa berjalan setelah INSERT gagal dalam transaksi yang sama.
- **Perbaiki pemetaan status PMBM**: status `lulus` yang sebelumnya salah dipetakan ke `alumni` kini dipetakan ke `aktif` sesuai konteks PMBM (lulus = diterima sebagai siswa baru).

## [0.9.1] - 2026-04-16 — Mobile UI Optimization (Admin Dashboards)

### Changed
- **Mobile Optimizations**: Major overhaul for Super Admin, Admin Sekolah, Operator, and Wali Kelas dashboards on smartphone viewports.
- **Dynamic Grids**: Optimized stat and action grids to use 2-column layouts on mobile for better space utilization.
- **Responsive Tables**: Implemented progressive column hiding in dashboard tables to prevent overflow on small screens.
- **Visual Scaling**: Refined font sizes, icon dimensions, and card padding specifically for mobile devices.
- **Hero Header Spacing**: Adjusted hero section margins and overlapping logic for a cleaner smartphone experience.

## [0.9.0] - 2026-04-16 — Portal Orang Tua (Parent Portal)

### Added
- **Portal Orang Tua (v0.9.0)**: Dedicated portal for parents to monitor their children's activities.
- **Child Relation**: Added `children()` relationship to `User` model for multi-child support.
- **Parent Dashboard**: Real-time attendance summary for all linked children, including monthly statistics and Early Warning System (Alpha &ge; 3).
- **Child Detailed Profile**: View personal information, academic status, and quick actions for each child.
- **Attendance History**: Monthly view of children's attendance logs with status and method details.
- **Parent-Initiated Permissions**: Parents can now submit permission requests (Sakit/Izin) for their children directly from the portal, including file attachments.
- **PortalOrangTuaController**: New controller to manage parent-specific views and data logic.
- **Automated Dashboard Redirection**: Integrated parent portal into the global dashboard redirection system.

## [0.7.0] - 2026-04-12 — Audit Trail & Visual Attendance Calendar
 
### Added
 
- **Activity Log (Audit Trail)**: Full system logging for CRUD operations, logins, and scans. Includes a visual dashboard for logs with data diff comparison (Old vs New data).
- **Monthly Visual Calendar**: Interactive monthly calendar view (`/admin/kalender-absensi`) showing daily attendance distribution with color-coded percentages.
- **Detailed Audit View**: Modal popup to compare JSON data changes during updates, ensuring full transparency of system modifications.
- **Activity Tracking Integration**: Automatically recording scans from main dashboard and status changes in Izin/Sakit modules.
- **Improved Sidebar**: Added new direct links to "Kalender Absensi" and "Activity Log" for authorized roles.
 
---

## [0.6.0] - 2026-04-12 — Major Dashboard Redesign & Premium Experience

### Added

- **Premium Landing Page (Homepage)**: Redesign total halaman depan (`/`) dengan desain *high-end glassmorphism*, hero section dengan ilustrasi 3D, statistik sekolah, dan navigasi cepat ke fitur utama.
- **Major Dashboard Redesign (Super Admin)**: Split-layout desktop-first design, premium gradient hero banner, live clock, and real-time AJAX statistics.
- **Integrated QR Scanner**: Scanner card directly on the dashboard with `jsQR` and AJAX processing (no-reload experience).
- **Live Attendance Monitor**: Fullscreen monitoring page (`/admin/live-monitor`) designed for TV/Projectors with dark mode and 15s auto-refresh.
- **Class Statistics & Comparison**: Analytics page (`/admin/statistik-kelas`) for ranking classes based on attendance percentage and identifying top students.
- **Early Warning Alpha System**: Alert system to identify students with consistent absenteeism (Alpha &ge; 3).
- **Detailed Student Profile**: Deep-dive profile view (`/admin/siswa/{id}/profil`) with attendance history, stats donut charts, and monthly trends.
- **Absensi Cepat (Bulk Input)**: Fast attendance entry for entire classes with "Mark All Present" utility and keyboard shortcuts (1-5).
- **AJAX Core Updates**: Main dashboard components (Stats, Donut Chart, Leaderboards) now refresh automatically after every scan via AJAX polling.

### Changed

- **Navigation Overhaul**: Reorganized sidebar menu with new sub-items for Dashboard and Attendance modules.
- `DashboardController` expanded with advanced data aggregation for live monitoring and statistics.

---

## [0.5.0] - 2026-04-12 — Responsiveness & Dashboard Enhancement

### Added

- **Donut chart distribusi status** di dashboard Super Admin & Admin Sekolah — menampilkan proporsi Hadir/Sakit/Izin/Alpha/Terlambat absensi siswa hari ini.
- **Multi-series bar chart** di dashboard — 4 seri (Hadir, Sakit, Izin, Alpha) per hari selama 7 hari terakhir, menggantikan single-series chart.
- **Stat cards clickable** di dashboard Super Admin — setiap card (Siswa, Guru, Staff, Kelas, Absen, Izin) kini menjadi link langsung ke halaman terkait.
- **Tanggal hari** di hero card dashboard — menampilkan hari dan tanggal lokal.

### Changed

- **10 halaman index di-refactor** untuk mobile-first responsive design:
  - Headers menggunakan `d-flex flex-column flex-md-row` agar stacking pada layar kecil.
  - Kolom opsional (NIP, NIS, NISN, Email, Metode, Keterangan) di-hidden progresif via `d-none d-md-table-cell` / `d-lg-table-cell` / `d-xl-table-cell`.
  - Tombol aksi diganti dari teks ("Ubah", "Hapus") ke icon-only (`btn-icon btn-sm`) dengan tooltip.
  - Status (aktif/nonaktif, hadir/sakit/izin/alpha/terlambat) kini menggunakan color-coded badges (`bg-label-success`, `bg-label-danger`, dst).
  - Metode absensi ditampilkan sebagai badge (`QR` = primary, `MANUAL` = secondary).
  - Kelas ditampilkan sebagai badge `bg-label-info`.
  - Empty state ditambahkan ikon visual.
  - Alert success → `alert-dismissible` dengan close button.
  - Tabel menggunakan `table-hover` + `table-light` thead + `p-0` card body (full-width).
- **Halaman `izin-sakit/index`** — filter dipindah ke card terpisah; approve/reject menggunakan ikon ✔/✘; periode ditampilkan inline pada mobile.
- **Halaman `laporan/index`** — filter form responsive stacking; 2 kolom pertama pivot table menggunakan `position-sticky` agar tetap terlihat saat scroll horizontal.
- **Dashboard Admin Sekolah** — di-upgrade ke pattern yang sama dengan Super Admin (donut + multi-series bar chart).

## [0.4.0] - 2026-04-12 (Sesi Lanjutan — Fitur Completion)

### Added

- **Notification Bell di Navbar** — dropdown notifikasi real-time di navbar untuk semua role. Menampilkan notifikasi izin/sakit yang belum dibaca. Tombol "×" per item dan "Tandai Semua Dibaca" via AJAX (`POST /admin/notifications/mark-read`).
- **Validasi Duplikat Absensi** — `store()` pada `AbsensiSiswaController`, `AbsensiGuruController`, `AbsensiStaffController` kini menolak input absensi jika sudah ada catatan untuk entitas yang sama pada tanggal yang sama.
- **Export Rekap Bulanan Guru** — `GET /admin/laporan/export-excel-guru` → download `.xlsx` rekap absensi guru per bulan (`RekapBulananGuruExport`).
- **Export Rekap Bulanan Staff TU** — `GET /admin/laporan/export-excel-staff` → download `.xlsx` rekap absensi staff per bulan (`RekapBulananStaffExport`).
- **Tombol Export Guru & Staff** di halaman `admin/laporan` untuk memudahkan akses.
- **AutoMarkAlphaCommand** (`php artisan absensi:auto-alpha [--tanggal=Y-m-d]`) — menandai semua siswa/guru/staff aktif yang tidak memiliki catatan absensi pada tanggal target sebagai "alpha". Dijadwalkan otomatis setiap hari jam 08:00 via Laravel Scheduler.
- **Cetak Kartu QR Siswa per Kelas** — `GET /admin/siswa/cetak-qr?kelas_id=X` → generate & download PDF kartu QR (DomPDF, A4 portrait, 3 kolom). Tombol "Cetak Kartu QR" ditambahkan pada halaman daftar siswa.

### Changed

- `markRead()` di `IzinSakitController` kini mendukung parameter `all=1` untuk menandai semua notifikasi sekaligus, dan mengembalikan JSON `{success: true}` yang konsisten.
- `routes/console.php` ditambahkan jadwal `Schedule::command(AutoMarkAlphaCommand::class)->dailyAt('08:00')`.

---

## [0.3.0] - 2026-04-11 (HARI 2–3) — Fitur Lanjutan

### Added

- `barryvdh/laravel-dompdf ^3.1` untuk export PDF laporan.
- Tabel notifikasi Laravel (`notifications`) via `php artisan notifications:table`.
- `IzinDiajukanNotification` — notifikasi database ke admin saat izin diajukan.
- `IzinDisetujuiNotification` — notifikasi database saat status izin berubah.
- `IzinSakitController` full rewrite — approval workflow (approve/reject), file upload lampiran, `updateAbsensiFromIzin()`.
- View `izin-sakit/index.blade.php` — filter, tombol approve/reject, preview lampiran, pagination.
- View `izin-sakit/form.blade.php` — `enctype="multipart/form-data"`, file input lampiran (maks 100KB).
- `PengaturanController` + view `pengaturan/index.blade.php`.
- `UserController` + views `users/index`, `users/form` — CRUD user (super_admin only).
- Migration `jadwal_pelajaran`, model `JadwalPelajaran`, controller + views CRUD.
- `LaporanController` rewrite — pivot table absensi, rekap harian, individual siswa, export PDF.
- View `laporan/index.blade.php` — pivot table kode warna (H/S/I/A/T), summary cards, export Excel+PDF.
- Views `laporan/rekap-harian`, `laporan/individual-siswa`, `laporan/rekap-pdf`.
- `DemoDataSeeder.php` — seed 1 bulan absensi weekday.
- Dashboard dengan ApexCharts bar chart kehadiran siswa 7 hari terakhir.
- 6 dashboard terpisah per role di `resources/views/dashboards/`.

### Fixed

- Syntax error `IzinSakitController.php` (kode duplikat orphan).

---

## [0.2.0] - 2026-04-11 (HARI 2) — Absensi Core & QR Scanner

### Added

- Migration & Model: `absensi_siswa`, `absensi_guru`, `absensi_staff`, `izin_sakit`.
- CRUD Absensi Siswa, Guru, Staff TU.
- Halaman Scan QR Code (jsQR.js / Html5QrcodeScanner).
- Logic scan QR → cari entitas → catat absensi otomatis.
- Docker PHP 8.3 environment.

---

## [0.1.0] - 2026-04-11 (HARI 1) — Foundation & Master Data

### Added

- Setup custom role ACL dengan field `users.role`.
- Seeder 6 role default (super_admin, admin_sekolah, guru, wali_kelas, staff_tu, siswa).
- Integrasi layout Vuexy (sidebar, navbar, breadcrumb).
- Route group per role dengan `RoleMiddleware`.
- Migration: `tahun_akademik`, `kelas`, `siswa`, `guru`, `staff_tata_usaha`, `pengaturan`.
- CRUD Tahun Akademik (validasi: hanya 1 yang aktif).
- CRUD Kelas (select wali kelas dari daftar guru).
- CRUD Siswa (foto upload, validasi NIS unik, QR code di edit page).
- CRUD Guru (foto upload, validasi NIP unik, QR code di edit page).
- CRUD Staff TU.
- Import Excel siswa via Maatwebsite Laravel-Excel.
- Generate QR Code via `endroid/qr-code`.
- Dashboard admin (skeleton + widget data hari ini).

### Security

- Middleware `role` di semua route sensitif.
