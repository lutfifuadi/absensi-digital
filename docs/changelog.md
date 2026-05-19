# Changelog

## [v1.3.0] - 2026-05-19
### Added
- **Fitur Naik Kelas Massal**: Promosikan seluruh siswa secara batch ke tahun ajaran baru dengan pemetaan otomatis X→XI, XI→XII, XII→alumni.
- Halaman `/admin/siswa/naik-kelas-massal` dengan step-by-step flow (pilih TA → preview → eksekusi → hasil)
- Preview kenaikan kelas sebelum eksekusi (jumlah siswa, mapping kelas tujuan, peringatan alumni)
- Logika auto-map tingkat berdasarkan jurusan yang sama di tahun ajaran tujuan
- Status alumni otomatis untuk siswa kelas XII
- Tabel `riwayat_kenaikan_kelas` untuk histori kenaikan kelas per siswa
- Model `RiwayatKenaikanKelas` dengan relasi ke siswa, kelas, dan tahun akademik
- Method `SiswaService::naikKelasMassal()` dan `SiswaService::previewNaikKelasMassal()`
- RBAC: super_admin, admin_sekolah, operator
- Sidebar navigasi admin & operator untuk akses cepat
- Dokumentasi API di `docs/api/naik-kelas-massal.md`

## [v1.2.0] - 2026-05-18
### Added
- **Fitur Chat AI dengan Google Gemini**: Asisten AI berbasis Google Gemini (`gemini-3-flash-preview`) untuk membantu user mengelola data dengan percakapan natural language.
- Halaman Asisten AI di `/admin/ai-chat` dengan Livewire component realtime
- `GeminiService` dengan round-robin (max 3 retry), function calling, dan 9 tool functions
- Tool functions: get/update siswa, get/update guru, get/update kelas, cari siswa, cari guru, statistik data
- Riwayat chat otomatis tersimpan di tabel `chat_logs` per user
- RBAC: hanya super_admin, admin_sekolah, dan operator yang bisa akses
- Rate limiting 30 request/menit pada endpoint send
- Dokumentasi API di `docs/api/ai-chat.md`
- Konfigurasi: `GEMINI_API_KEY` dan `GEMINI_MODEL` di `.env`

## [v1.1.0] - 2026-05-18
### Added
- Fitur pull data siswa dari Google Sheets
- Halaman setting Google Sheets di `/admin/pengaturan/google-sheets`
- Google Sheets API integration service (`GoogleSheetsService`)
- Background job sync (`GoogleSheetsSyncJob`) dengan queue `syncs`
- Test connection, mapping kolom, dan trigger sync manual
- Encryption untuk service account credentials

## [1.0.10] - 2026-05-09
### Added
- **Fitur Import & Export Guru**: Implementasi lengkap sistem import data guru dari file Excel/CSV dan export data guru ke format Excel (.xlsx).
- **Akun User Otomatis**: Sistem secara cerdas membuatkan akun pengguna (`User`) secara otomatis bagi setiap guru yang diimport, lengkap dengan password default berbasis NIP.
- **UI Guru yang Diperbarui**:
  - Tombol operasional Import & Export yang premium pada Hero Header.
  - Modal upload file dengan panduan format kolom dan fitur download sampel format.
  - Fitur pencarian instan pada tabel daftar guru untuk mempermudah manajemen data dalam jumlah besar.
- **Validasi Data Cerdas**: Memastikan integritas data NIP dan kelengkapan profil guru terjaga selama proses pemindahan data massal.

## [1.0.9] - 2026-05-09
### Added
- **Dual Camera QR Scanner**: Implementasi fitur perpindahan kamera (front/back) pada semua modul pemindaian QR di aplikasi.
- **Switch Camera UI**: Penambahan tombol interaktif "Ganti Kamera" dengan ikon animasi yang memudahkan pengguna beralih antara kamera depan dan belakang secara real-time.
- **Refactor QR Logic**: Pembaruan logika pemindaian menggunakan native MediaDevices API dikombinasikan dengan Html5Qrcode untuk kontrol kamera yang lebih presisi dan responsif.
- **Kompatibilitas Multi-Halaman**: Fitur ini telah diimplementasikan pada:
  - Halaman Scan Absensi Siswa
  - Halaman Scan Absensi Guru
  - Halaman Scan Kegiatan
  - Halaman Scan Publik

## [1.0.8] - 2026-05-09
### Added
- **Fungsi Export Siswa**: Menambahkan fitur untuk mengunduh data siswa ke dalam format **Excel (.xlsx)** dan **CSV (.csv)** langsung dari halaman daftar siswa.
- **Filter Pintar pada Export**: Sistem export secara otomatis menyesuaikan data yang diunduh dengan filter pencarian dan periode Tahun Ajaran yang sedang aktif di layar pengguna.
- **Antarmuka Premium**: Penambahan tombol dropdown Export yang elegan dengan desain glassmorphism, memberikan pengalaman pengguna yang lebih premium dan fungsional.

### Fixed
- Optimasi struktur export menggunakan library `maatwebsite/excel` untuk memastikan performa yang cepat dan penggunaan memori yang efisien saat menangani data dalam jumlah besar.

## [1.0.6] - 2026-05-09
