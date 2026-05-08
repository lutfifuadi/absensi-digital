# Laporan Progress Sistem Absensi Klien

Tanggal: 04 Mei 2026

---

## 1. Ringkasan Eksekutif

**absensi-klien** adalah sistem manajemen presensi sekolah digital yang komprehensif, dibangun untuk sekolah-sekolah di Indonesia. Aplikasi ini menangani pencatatan kehadiran untuk siswa, guru, dan staff tata usaha dengan fitur pemindaian kode QR untuk check-in/check-out.

---

## 2. Arsitektur Sistem

### 2.1 Struktur Proyek
Sistem menggunakan arsitektur **Laravel MVC** dengan tooling modern:

```
D:\Project\absensi-klien\
├── app/                          # Kode aplikasi utama
│   ├── Http/Controllers/         # Controller (Admin, API, Auth, Pages)
│   ├── Models/                   # Model Eloquent (27 model)
│   ├── Services/                 # Logika bisnis
│   ├── Traits/                   # Reusable traits (HasTenant)
│   ├── Middleware/               # Custom middleware (8 middleware)
│   ├── Exports/                  # Export Excel
│   ├── Imports/                  # Import Excel
│   ├── Observers/                # Model observers
│   └── Helpers/                  # Fungsi helper
├── resources/                    # Frontend assets
│   ├── views/                    # Blade templates
│   │   ├── admin/               # Admin panel (33 direktori)
│   │   ├── layouts/             # Template layout
│   │   ├── portal-ortu/         # Portal orang tua
│   │   └── public/              # Halaman publik
│   ├── js/                      # JavaScript
│   ├── css/                     # Stylesheets
│   └── assets/                  # Vendor assets
├── routes/                      # Definisi rute
│   ├── web.php                  # Web routes (531 baris)
│   ├── api.php                  # API routes (58 baris)
│   └── console.php              # Artisan commands
├── database/                    # File database
│   ├── migrations/              # 43 file migrasi
│   ├── seeders/                 # Database seeders
│   └── factories/               # Model factories
├── config/                      # File konfigurasi (15 file)
├── public/                      # File publik
├── storage/                     # Penyimpanan aplikasi
└── tests/                       # File testing
```

---

## 3. Teknologi yang Digunakan

### 3.1 Backend (PHP)
| Teknologi | Versi | Keterangan |
|-----------|-------|------------|
| Laravel | 12.x | PHP Framework |
| PHP | 8.2+ | Bahasa pemrograman |
| Laravel Jetstream | 5.5 | Application scaffolding (Livewire) |
| Livewire | 3.6.4 | Full-stack framework untuk UI dinamis |
| Laravel Sanctum | 4.0 | API token authentication |
| Laravel Fortify | - | Authentication backend |
| Maatwebsite Excel | 3.1 | Import/export Excel |
| endroid/qr-code | 6.0 | Generasi QR code |
| barryvdh/laravel-dompdf | 3.1 | Generasi PDF |
| pixinvent/vuexy-laravel-bootstrap-jetstream | 3.0 | Admin template |

### 3.2 Frontend (JavaScript/CSS)
| Teknologi | Versi | Keterangan |
|-----------|-------|------------|
| Vite | 6.3.5 | Build tool dan dev server |
| Bootstrap | 5.3.5 | CSS framework |
| Vuexy Template | 3.0 | Admin dashboard template |
| jQuery | 3.7.1 | JavaScript library |
| DataTables | 2.1.8 | Advanced tables |
| FullCalendar | 6.1.17 | Calendar functionality |
| ApexCharts | 4.2.0 | Charts dan analytics |
| SweetAlert2 | 11.14.5 | Alert dialogs |
| Select2, Flatpickr, Tagify | - | UI enhancement |
| Axios | 1.9.0 | HTTP client |
| FontAwesome | 6.7.2 | Icon library |

### 3.3 Development Tools
- PHPUnit 11.5.3 (Testing)
- Laravel Pint (Code style)
- ESLint 9.16.0 (JS linting)
- Prettier 3.5.3 (Formatter)
- Stylelint 16.19.1 (CSS linting)
- Docker/Laravel Sail (Containerization)

---

## 4. Role Pengguna (8 Role)

1. **super_admin** - Administrator sistem dengan akses penuh
2. **admin_sekolah** - Administrator sekolah
3. **operator** - Operator sekolah untuk operasional harian
4. **guru** - Portal guru
5. **wali_kelas** - Portal wali kelas
6. **staff_tu** - Portal staff tata usaha
7. **siswa** - Portal siswa
8. **orang_tua** - Portal orang tua untuk memantau anak

---

## 5. Fitur Utama

### 5.1 Manajemen Presensi
- **Presensi Siswa** - Pemindaian QR code dengan jam masuk/pulang
- **Presensi Guru** - Sistem serupa untuk guru
- **Presensi Staff** - Untuk staff tata usaha
- **Presensi Kegiatan** - Pelacakan kehadiran berbasis acara
- **Presensi Cepat** - Input presensi massal
- **Live Monitor** - Tampilan presensi real-time untuk TV/projector
- **Live Board** - Papan status kehadiran publik

### 5.2 Manajemen Akademik
- **Manajemen Siswa** - CRUD dengan generasi QR code, pindah kelas, kenaikan kelas
- **Manajemen Guru** - Termasuk penugasan wali kelas
- **Manajemen Kelas** - Dengan jam presensi kustom per kelas
- **Manajemen Tahun Akademik** - Dukungan multi-tahun
- **Jadwal Pelajaran** - Jadwal mata pelajaran guru

### 5.3 Sistem QR Code
- Generasi QR code individual untuk siswa, guru, dan staff
- Cetak QR code massal (kartu PDF)
- Halaman pemindaian QR publik (tanpa login)
- Sistem trust device untuk pemindaian aman

### 5.4 Laporan & Analitik
- **Rekap Harian** - Laporan per kelas dan per siswa
- **Export Excel** - Laporan presensi bulanan (siswa, guru, staff)
- **Export PDF** - Laporan presensi yang bisa dicetak
- **Dashboard Analitik** - Visualisasi chart dan statistik
- **Statistik Kelas** - Peringkat kelas berdasarkan kehadiran
- **Kalender Presensi** - Visualisasi kehadiran bulanan
- **Sistem Peringatan Dini** - Peringatan untuk siswa dengan >= 3 ketidakhadiran

### 5.5 Gamifikasi
- **Attendance Streaks** - Pelacakan kehadiran berturut-turut
- **Sistem Badge** - Badge pencapaian untuk siswa
- **Leaderboards** - Peringkat peserta dengan kehadiran tertinggi
- **Pesan Motivasi** - Sapaan dinamis berdasarkan streak

### 5.6 Komunikasi
- **Integrasi WhatsApp** - Notifikasi otomatis via WA Gateway
- **Notifikasi Orang Tua** - Peringatan kehadiran ke orang tua
- **Ringkasan Mingguan** - Laporan mingguan otomatis ke kepala sekolah
- **Manajemen Izin/Sakit** - Permohonan izin dengan alur persetujuan

### 5.7 Fitur Lainnya
- **Manajemen Hari Libur** - Kalender hari libur sekolah dan nasional
- **Activity Log** - Audit trail lengkap
- **User Impersonation** - Admin bisa login sebagai user lain
- **Dukungan PWA** - Progressive Web App
- **Template ID Card** - Desain kartu siswa yang dapat disesuaikan
- **Template Notifikasi** - Pesan yang dapat dikonfigurasi
- **Keamanan Device** - Manajemen device tepercaya untuk pemindaian QR
- **Integrasi API** - Sinkronisasi dengan sistem eksternal (PMBM, Master DB)
- **Multi-School Support (Tenancy)** - HasTenant trait untuk isolasi sekolah
- **Installer Wizard** - Proses instalasi berbasis web 4 langkah

---

## 6. Struktur Database

### 6.1 Tabel Utama (43 migrasi)

#### Tabel Core
- **schools** - Dukungan multi-tenancy untuk multiple sekolah
- **users** - User autentikasi dengan field role dan roles (JSON array)
- **siswa** - Siswa (nis, nisn, nama_lengkap, jenis_kelamin, qr_code, dll)
- **guru** - Guru (nip, nama_lengkap, mata_pelajaran, jabatan, qr_code)
- **staff_tata_usaha** - Staff administrasi
- **kelas** - Kelas (nama, tingkat, jurusan, wali_kelas_id, is_aktif_absensi)
- **tahun_akademik** - Tahun akademik (nama, semester, tanggal_mulai/selesai, is_aktif)

#### Tabel Presensi
- **absensi_siswa** - Presensi siswa (siswa_id, kelas_id, tanggal, jam_masuk/pulang, status, metode)
- **absensi_guru** - Presensi guru
- **absensi_staff** - Presensi staff
- **absensi_kegiatan** - Presensi kegiatan/acara
- **izin_sakit** - Permohonan izin (tipe: siswa/guru/staff, status: pending/approved)

#### Tabel Tambahan
- **kegiatan** - Kegiatan/acara
- **jadwal_pelajaran** - Jadwal mata pelajaran
- **pengaturan** - Key-value settings storage
- **holidays** - Kalender hari libur
- **notification_templates** - Template pesan yang dapat dikonfigurasi
- **id_card_templates** - Desain kartu ID
- **activity_logs** - Audit trail
- **authorized_devices** - Device tepercaya untuk pemindaian QR
- **roles** - Definisi role
- **attendance_analytics** - Data analitik
- **badges** - Badge gamifikasi
- **offline_queues** - Antrian presensi offline
- **activity_attendance** - Partisipasi kegiatan
- **reminder_settings** - Konfigurasi pengingat otomatis
- **permission_proofs** - Dokumentasi izin

### 6.2 Koneksi Database
- **MySQL 8.0** (utama, via Docker)
- **SQLite** (untuk testing)
- **MariaDB, PostgreSQL, SQL Server** (alternatif)

---

## 7. API Endpoints

### 7.1 Web Routes (routes/web.php - 531 baris)

#### Rute Publik
- `/` - Beranda
- `/scan-qr/*` - Pemindaian QR publik (5 rute dengan middleware device trust)
- `/live-board` - Papan kehadiran live publik
- `/tentang-kami`, `/panduan-pengguna`, dll. - Halaman informasi publik
- `/auth/login-basic`, `/auth/register-basic` - Halaman autentikasi
- `/install/*` - Installer web 4 langkah

#### Rute Terautentikasi (dengan middleware berbasis role)

**Portal Siswa** (`/siswa/*` dengan `role:siswa`):
- `/dashboard`, `/profile`, `/download-kartu`, `/izin-sakit/*`, `/absensi-mandiri`

**Portal Guru** (`/guru/*` dengan `role:guru`):
- `/dashboard`, `/absensi`, `/absensi/scan`, `/izin-sakit/*`

**Portal Wali Kelas** (`/wali-kelas/*` dengan `role:wali_kelas`):
- `/dashboard`, `/siswa/*`, `/absensi-siswa`, `/rekap-harian`

**Portal Orang Tua** (`/ortu/*` dengan `role:orang_tua`):
- `/dashboard`, `/anak/{id}/*`, `/izin-sakit/*`

**Rute Admin** (`/admin/*` dengan berbagai middleware role):
- Dashboard, Live Monitor, Statistik Kelas, Kalender Absensi
- Manajemen: Schools, Tahun Akademik, Kelas, Siswa, Guru, Wali Kelas, Staff TU
- Presensi: Siswa, Guru, Staff, Kegiatan
- Izin/Sakit, Laporan, Kegiatan, Scan QR, Jadwal
- Users, Role, Pengaturan, WA Gateway, PWA, Devices
- Analytics, Gamifikasi, Reminder Settings, API Integration
- Notification Templates, ID Card Templates, Impersonation
- Activity Log, Holidays

### 7.2 API Routes (routes/api.php - 58 baris)

#### Sync API (v1/sync) - Dilindungi Sanctum
- `/v1/sync/siswa` - Sinkronisasi data siswa
- `/v1/sync/guru` - Sinkronisasi data guru
- `/v1/sync/staff` - Sinkronisasi data staff
- `/v1/sync/kelas` - Sinkronisasi data kelas
- `/v1/sync/tahun-akademik` - Sinkronisasi data tahun akademik

#### PMBM Webhook (v1/pmbm) - Dilindungi API key
- `/v1/pmbm/presensi` - Menerima webhook presensi

#### Innovation API (v1/innovation)
- Notification Templates, Analytics, Badges, Leaderboard
- Offline Queue, Reminder Settings, Activity Attendance, Device Offline Mode

#### Holiday API (v1/holidays)
- CRUD lengkap untuk hari libur + endpoint pengecekan tanggal

---

## 8. Struktur Frontend

### 8.1 Layouts (resources/views/layouts/)
- `commonMaster.blade.php` - Layout admin utama
- `layoutMaster.blade.php` - Layout master alternatif
- `horizontalLayout.blade.php` - Layout menu horizontal
- `contentNavbarLayout.blade.php` - Konten dengan navbar
- `blankLayout.blade.php` - Layout minimal
- `layoutFront.blade.php` - Layout untuk publik

### 8.2 Direktori Views (33 direktori view admin)
Termasuk folder khusus untuk: absensi-guru, absensi-siswa, absensi-staff, siswa, guru, kelas, tahun-akademik, laporan, izin-sakit, kegiatan, scan-qr, analytics, gamifikasi, devices, schools, users, role, pengaturan, pwa, wa-gateway, notification-templates, id-card-templates, jadwal, holidays, wali-kelas, staff-tata-usaha, activity-log, api, reminder, statistik-kelas, kalender-absensi, live-monitor, master-data

### 8.3 Frontend Assets (resources/)
- `js/app.js` - Entry point JavaScript utama
- `js/bootstrap.js` - Inisialisasi Bootstrap
- `js/laravel-user-management.js` - JavaScript CRUD User
- `css/app.css` - Stylesheet utama
- `css/das-theme.css` - Tema kustom
- `assets/` - Library vendor, font, dan JS/CSS kustom

### 8.4 Konfigurasi Build (vite.config.js)
- Memproses `resources/assets/vendor/` libraries
- Menangani `resources/assets/js/*.js` script spesifik per halaman
- Mengkompilasi `resources/assets/vendor/scss/` stylesheets
- Termasuk plugin `icons` kustom untuk integrasi Iconify
- Mendukung alias `@` untuk direktori resources

---

## 9. Konfigurasi Sistem

### 9.1 Konfigurasi Laravel (config/ - 15 file)
- **app.php** - Nama aplikasi, timezone (Asia/Jakarta), locale, encryption
- **auth.php** - Authentication guards dan providers
- **cache.php** - Konfigurasi cache
- **custom.php** - Kustomisasi template Vuexy (layout, theme, skins, RTL)
- **database.php** - Koneksi database (MySQL, SQLite, MariaDB, PostgreSQL, SQL Server)
- **filesystems.php** - File storage disks
- **fortify.php** - Fitur autentikasi Fortify
- **jetstream.php** - Konfigurasi Jetstream (Livewire stack, features)
- **logging.php** - Log channels
- **mail.php** - Mail driver settings
- **queue.php** - Queue connections
- **sanctum.php** - API authentication
- **services.php** - Layanan pihak ketiga
- **session.php** - Konfigurasi session
- **variables.php** - Variabel aplikasi kustom

### 9.2 Environment Configuration (.env.example)
- **Application**: APP_NAME, APP_ENV, APP_DEBUG, APP_URL
- **Localization**: APP_LOCALE (en), APP_FALLBACK_LOCALE, APP_FAKER_LOCALE
- **Database**: DB_CONNECTION (mysql), DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- **Session**: SESSION_DRIVER (database), SESSION_LIFETIME
- **Cache**: CACHE_STORE (database)
- **Queue**: QUEUE_CONNECTION (database)
- **Redis**: REDIS_HOST, REDIS_PORT, REDIS_PASSWORD
- **Mail**: MAIL_MAILER (log), MAIL_HOST, MAIL_PORT
- **AWS S3**: AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_BUCKET
- **Vite**: VITE_APP_NAME
- **Master DB API**: MASTER_DB_API_URL, MASTER_DB_API_KEY

### 9.3 Docker Configuration (docker-compose.yml)
- **laravel.test** - Container PHP 8.3 dengan Sail
- **mysql** - Container database MySQL 8.0
- Ports: 80 (HTTP), 5173 (Vite dev server), 3306 (MySQL)
- Volumes: Persistent MySQL data, application code mount

---

## 10. Implementasi Multi-Tenancy

Sistem mengimplementasikan **multi-school tenancy** menggunakan:
- **HasTenant trait** (`app/Traits/HasTenant.php`) - Diterapkan ke semua model utama
- **TenantScope** - Global scope untuk memfilter data berdasarkan school_id
- **school_id column** - Ditambahkan ke semua tabel terkait (migrasi ke-43)
- **School model** - Mewakili setiap sekolah dengan relasi ke semua entitas
- **TenantMiddleware** - Mengatur konteks sekolah saat ini dari session/subdomain

---

## 11. Key Services

### 11.1 WhatsAppService (`app/Services/WhatsAppService.php`)
- Integrasi dengan WA Gateway eksternal (wa.lutfifuadi.my.id)
- Mengirim pesan teks dan media
- Validasi nomor WhatsApp dengan caching 24 jam
- Mendukung validasi nomor sebelum mengirim

### 11.2 SyncService (`app/Services/SyncService.php`)
- Menangani sinkronisasi dengan sistem eksternal
- Memproses data siswa, guru, staff, kelas, dan tahun akademik

### 11.3 SiswaService (`app/Services/SiswaService.php`)
- Logika bisnis untuk manajemen siswa
- Menangani transfer kelas dan kenaikan kelas

---

## 12. Kesimpulan

**absensi-klien** adalah sistem manajemen presensi sekolah yang **produksi-ready** dan kaya fitur, dibangun dengan ekosistem Laravel modern. Sistem ini mendukung:

- **3 jenis presensi** (siswa, guru, staff)
- **8 role pengguna** dengan izin granular
- **Presensi berbasis QR code** (check-in/check-out)
- **Multi-school tenancy**
- **Laporan komprehensif** (Excel, PDF)
- **Gamifikasi & analitik**
- **Notifikasi WhatsApp**
- **Kemampuan PWA**
- **Sinkronisasi API** dengan sistem eksternal
- **Keamanan device** untuk stasiun pemindaian QR

Codebase terstruktur dengan baik, mengikuti best practices Laravel, dan menggunakan **template admin Vuexy** untuk UI yang polished. Sistem dirancang untuk sekolah Indonesia dengan fitur lokal seperti NIS/NISN, tahun akademik, dan struktur kelas (tingkat/jurusan).

---

*Laporan dibuat secara otomatis pada 04 Mei 2026*
