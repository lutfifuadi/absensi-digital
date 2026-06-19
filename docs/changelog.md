# Changelog

> Semua perubahan penting pada aplikasi Absensi Digital akan dicatat di sini.

---

## [v1.7.0] - 2026-06-19

### Added
- **Offline scan absensi ekskul** — IndexedDB + Background Sync untuk halaman `/scan-ekskul`
  - Saat internet mati, data scan otomatis tersimpan di IndexedDB browser
  - Service Worker otomatis mengirim data saat koneksi pulih
- **Offline scan absensi datang/pulang** — halaman Guru Piket `/scan-qr/scan`
  - IndexedDB + Background Sync + banner offline/online
  - Backend sync handle logika datang (INSERT) + pulang (UPDATE) + Guru
- **Banner indikator** offline (merah) dan online (hijau) di semua halaman scan
- **Toast indikator** "Disimpan Offline" saat scan di mode offline
- **Registrasi Service Worker** di halaman scan datang/pulang

### Changed
- `InnovationController@syncOfflineEvents`: case `absensi` diupgrade — validasi QR, deteksi datang/pulang, handle Guru
- `InnovationController@syncOfflineEvents`: case `absensi_ekskul` baru — validasi NIS, token QR, membership, duplikasi
- PWA Enhancement: meta tags, manifest link di halaman scan datang/pulang

### Fixed
- `.gitignore` — exclude vendor.zip (file terlalu besar untuk GitHub)

---

## [v1.6.4.1] - 2026-06-19

### Changed
- PWA Settings: tambah validasi input, error handling, dan fallback icon URL
- Dashboard orang tua: redesign total dengan premium cards, dark mode, animasi
- PWA manifest: tambah start_url, display, orientation requirements

### Fixed
- Kompatibilitas migration DROP FOREIGN KEY untuk MariaDB (query INFORMATION_SCHEMA)
- Scope variable `$today, $month, $year` di method `staffTuData()`

---

## [v1.6.4] - 2026-06-18

### Fixed
- Dualisme activity attendance dan optimize kegiatan management dashboard
- Hapus controller duplikat, merge logic absensi kegiatan

---

## [v1.6.3] - 2026-06-18

### Fixed
- Resolve mobile card download failure
- Dynamize landing page stats

---

## [v1.6.2] - 2026-06-18

### Added
- Louder beep notification for graduation (pelepasan) scan — meningkatkan feedback di lapangan

---

## [v1.6.1] - 2026-06-17

### Added
- Implementasi lengkap modul ekstrakurikuler
- QR scanner untuk ekskul (jsQR + token HMAC)
- Filter absensi kegiatan per tingkat
- Opsi kegiatan tanpa batas waktu & tanggal fleksibel
- Rebuild kegiatan scanner, toggle is_wajib, pagination absensi
- AI Chat dengan model fix, live typing, markdown rendering, floating chat

### Fixed
- Fatal errors, API integration issues, storage permissions
- Optimasi caching settings dan database indexing untuk high-traffic QR scanning
